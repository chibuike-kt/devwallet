<?php

namespace App\Http\Controllers\Api\Flutterwave;

use App\Http\Controllers\Controller;
use App\Models\PaystackTransaction;
use App\Services\PaystackWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
  public function __construct(
    protected PaystackWebhookService $webhooks
  ) {}

  /**
   * GET /api/flutterwave/v3/transactions/{id}/verify
   */
  public function verify(Request $request, string $id): JsonResponse
  {
    $tx = PaystackTransaction::where('project_id', $request->_api_project->id)
      ->where(fn($q) => $q->where('id', $id)->orWhere('reference', $id))
      ->with('customer')
      ->first();

    if (!$tx) {
      return response()->json([
        'status'  => 'error',
        'message' => 'Transaction not found',
      ], 404);
    }

    // Auto-complete on verify
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

      if (!$shouldFail) {
        $this->webhooks->fireChargeSuccess($tx);
      }
    }

    return response()->json([
      'status'  => 'success',
      'message' => 'Transaction fetched successfully',
      'data'    => $this->formatTransaction($tx),
    ]);
  }

  /**
   * GET /api/flutterwave/v3/transactions
   */
  public function index(Request $request): JsonResponse
  {
    $transactions = PaystackTransaction::where('project_id', $request->_api_project->id)
      ->with('customer')
      ->latest()
      ->paginate(20);

    return response()->json([
      'status'  => 'success',
      'message' => 'Transactions fetched',
      'data'    => $transactions->map(fn($tx) => $this->formatTransaction($tx)),
      'meta'    => [
        'page_info' => [
          'total'       => $transactions->total(),
          'current_page' => $transactions->currentPage(),
          'total_pages' => $transactions->lastPage(),
        ],
      ],
    ]);
  }

  private function formatTransaction(PaystackTransaction $tx): array
  {
    return [
      'id'               => $tx->id,
      'tx_ref'           => $tx->reference,
      'flw_ref'          => 'FLW-' . strtoupper(substr($tx->reference, 0, 10)),
      'device_fingerprint' => 'N/A',
      'amount'           => $tx->amount / 100,
      'currency'         => $tx->currency,
      'charged_amount'   => $tx->amount / 100,
      'app_fee'          => round($tx->amount * 0.014 / 100, 2),
      'merchant_fee'     => 0,
      'processor_response' => $tx->gateway_response ?? 'Approved',
      'auth_model'       => 'PIN',
      'ip'               => '::ffff:10.63.7.110',
      'narration'        => 'DevWallet Sandbox',
      'status'           => $tx->status === 'success' ? 'successful' : $tx->status,
      'payment_type'     => 'card',
      'created_at'       => $tx->created_at->toIso8601String(),
      'account_id'       => $tx->project_id,
      'card'             => $tx->authorization_code ? [
        'first_6digits' => '408408',
        'last_4digits'  => $tx->last4 ?? '4081',
        'issuer'        => 'GUARANTY TRUST BANK',
        'country'       => 'NG',
        'type'          => strtoupper($tx->card_type ?? 'VISA'),
        'expiry'        => ($tx->exp_month ?? '09') . '/' . ($tx->exp_year ?? '2030'),
      ] : null,
      'customer'         => $tx->customer ? [
        'id'          => $tx->customer->id,
        'name'        => $tx->customer->fullName(),
        'phone_number' => $tx->customer->phone,
        'email'       => $tx->customer->email,
        'created_at'  => $tx->customer->created_at->toIso8601String(),
      ] : null,
    ];
  }
}
