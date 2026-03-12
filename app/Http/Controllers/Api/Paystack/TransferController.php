<?php

namespace App\Http\Controllers\Api\Paystack;

use App\Http\Controllers\Controller;
use App\Models\PaystackTransfer;
use App\Services\PaystackResponseService;
use App\Services\PaystackWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransferController extends Controller
{
  public function __construct(
    protected PaystackResponseService $response,
    protected PaystackWebhookService  $webhooks,
  ) {}

  /**
   * POST /api/paystack/transfer
   */
  public function store(Request $request): JsonResponse
  {
    $request->validate([
      'amount'                    => ['required', 'integer', 'min:100'],
      'recipient'                 => ['required', 'array'],
      'recipient.name'            => ['required', 'string'],
      'recipient.account_number'  => ['required', 'string'],
      'recipient.bank_code'       => ['required', 'string'],
      'recipient.bank_name'       => ['nullable', 'string'],
      'narration'                 => ['nullable', 'string'],
      'currency'                  => ['nullable', 'string'],
      'reference'                 => ['nullable', 'string'],
    ]);

    $transfer = PaystackTransfer::create([
      'project_id'              => $request->_api_project->id,
      'amount'                  => $request->amount,
      'currency'                => $request->currency ?? 'NGN',
      'narration'               => $request->narration,
      'reference'               => $request->reference,
      'recipient_name'          => $request->recipient['name'],
      'recipient_account_number' => $request->recipient['account_number'],
      'recipient_bank_code'     => $request->recipient['bank_code'],
      'recipient_bank_name'     => $request->recipient['bank_name'] ?? 'Test Bank',
      'status'                  => 'pending',
    ]);

    // Simulate async transfer completion
    // In real Paystack, this happens asynchronously and fires a webhook
    $project      = $request->_api_project;
    $delayMs      = $project->transferDelayMs();
    $shouldFail   = $project->shouldSimulateFail();

    dispatch(function () use ($transfer, $delayMs, $shouldFail) {
      if ($delayMs > 0) {
        usleep($delayMs * 1000);
      }

      $newStatus = $shouldFail ? 'failed' : 'success';

      $transfer->update([
        'status'       => $newStatus,
        'completed_at' => $shouldFail ? null : now(),
      ]);

      $webhook = app(PaystackWebhookService::class);

      if ($shouldFail) {
        $webhook->fireTransferFailed($transfer->fresh());
      } else {
        $webhook->fireTransferSuccess($transfer->fresh());
      }
    })->afterResponse();

    return response()->json($this->response->transferResponse($transfer));
  }

  /**
   * GET /api/paystack/transfer/verify/{reference}
   */
  public function verify(Request $request, string $reference): JsonResponse
  {
    $transfer = PaystackTransfer::where('project_id', $request->_api_project->id)
      ->where('reference', $reference)
      ->first();

    if (!$transfer) {
      return $this->response->errorResponse('Transfer not found', 404);
    }

    return response()->json($this->response->transferVerifyResponse($transfer));
  }

  /**
   * GET /api/paystack/transfer/{reference}
   */
  public function show(Request $request, string $reference): JsonResponse
  {
    $transfer = PaystackTransfer::where('project_id', $request->_api_project->id)
      ->where('reference', $reference)
      ->first();

    if (!$transfer) {
      return $this->response->errorResponse('Transfer not found', 404);
    }

    return response()->json($this->response->transferVerifyResponse($transfer));
  }

  /**
   * GET /api/paystack/transfer
   */
  public function index(Request $request): JsonResponse
  {
    $transfers = PaystackTransfer::where('project_id', $request->_api_project->id)
      ->latest()
      ->paginate((int)($request->perPage ?? 50));

    return response()->json($this->response->transferListResponse($transfers));
  }
}
