<?php

namespace App\Http\Controllers\Api\Paystack;

use App\Http\Controllers\Controller;
use App\Models\PaystackCustomer;
use App\Models\PaystackTransaction;
use App\Services\PaystackResponseService;
use App\Services\PaystackWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
  public function __construct(
    protected PaystackResponseService $response,
    protected PaystackWebhookService  $webhooks,
  ) {}

  /**
   * POST /api/paystack/transaction/initialize
   */
  public function initialize(Request $request): JsonResponse
  {
    $request->validate([
      'email'        => ['required', 'email'],
      'amount'       => ['required', 'integer', 'min:100'],
      'currency'     => ['nullable', 'in:NGN,USD,GHS,KES'],
      'reference'    => ['nullable', 'string'],
      'callback_url' => ['nullable', 'url'],
      'metadata'     => ['nullable', 'array'],
      'channels'     => ['nullable', 'array'],
    ]);

    $project = $request->_api_project;

    // Find or create customer
    $customer = PaystackCustomer::firstOrCreate(
      ['project_id' => $project->id, 'email' => $request->email],
      ['customer_code' => 'CUS_' . strtolower(Str::random(12))]
    );

    $tx = PaystackTransaction::create([
      'project_id'          => $project->id,
      'paystack_customer_id' => $customer->id,
      'reference'           => $request->reference ?? strtolower(Str::random(16)),
      'status'              => 'initialized',
      'amount'              => $request->amount,
      'currency'            => $request->currency ?? 'NGN',
      'channel'             => 'card',
      'callback_url'        => $request->callback_url,
      'metadata'            => $request->metadata,
    ]);

    return response()->json(
      $this->response->initializeResponse($tx, $request->getSchemeAndHttpHost())
    );
  }

  /**
   * GET /api/paystack/transaction/verify/{reference}
   */
  public function verify(Request $request, string $reference): JsonResponse
  {
    $tx = PaystackTransaction::where('project_id', $request->_api_project->id)
      ->where('reference', $reference)
      ->with('customer')
      ->first();

    if (!$tx) {
      return $this->response->errorResponse(
        'Transaction reference not found',
        404
      );
    }

    // Auto-complete initialized transactions on verify
    // This mirrors real Paystack behaviour where verify confirms payment
    if ($tx->status === 'initialized') {
      $shouldFail = $tx->force_fail;

      $tx->update([
        'status'             => $shouldFail ? 'failed' : 'success',
        'gateway_response'   => $shouldFail ? 'Declined' : 'Approved',
        'authorization_code' => 'AUTH_' . strtolower(Str::random(8)),
        'card_type'          => 'visa',
        'last4'              => (string) rand(1000, 9999),
        'exp_month'          => '12',
        'exp_year'           => '2030',
        'bank'               => 'TEST BANK',
        'paid_at'            => $shouldFail ? null : now(),
      ]);

      $tx->refresh()->load('customer');

      // Fire charge.success webhook
      if (!$shouldFail) {
        $this->webhooks->fireChargeSuccess($tx);
      }
    }

    return response()->json($this->response->verifyResponse($tx));
  }

  /**
   * GET /api/paystack/transaction/{id}
   */
  public function show(Request $request, int $id): JsonResponse
  {
    $tx = PaystackTransaction::where('project_id', $request->_api_project->id)
      ->with('customer')
      ->find($id);

    if (!$tx) {
      return $this->response->errorResponse('Transaction not found', 404);
    }

    return response()->json($this->response->transactionResponse($tx));
  }

  /**
   * GET /api/paystack/transaction
   */
  public function index(Request $request): JsonResponse
  {
    $transactions = PaystackTransaction::where('project_id', $request->_api_project->id)
      ->with('customer')
      ->latest()
      ->paginate((int)($request->perPage ?? 50));

    return response()->json(
      $this->response->transactionListResponse($transactions)
    );
  }
}
