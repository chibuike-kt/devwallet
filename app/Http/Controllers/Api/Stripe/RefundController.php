<?php

namespace App\Http\Controllers\Api\Stripe;

use App\Http\Controllers\Controller;
use App\Models\PaystackRefund;
use App\Models\PaystackTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RefundController extends Controller
{
  /**
   * POST /api/stripe/v1/refunds
   */
  public function store(Request $request): JsonResponse
  {
    $request->validate([
      'payment_intent' => ['required', 'string'],
      'amount'         => ['nullable', 'integer'],
      'reason'         => ['nullable', 'string'],
    ]);

    $tx = PaystackTransaction::where('project_id', $request->_api_project->id)
      ->where('reference', $request->payment_intent)
      ->first();

    if (!$tx) {
      return response()->json([
        'error' => [
          'code'    => 'resource_missing',
          'message' => 'No such payment_intent.',
          'type'    => 'invalid_request_error',
        ],
      ], 404);
    }

    $refund = PaystackRefund::create([
      'project_id'              => $request->_api_project->id,
      'paystack_transaction_id' => $tx->id,
      'amount'                  => $request->amount ?? $tx->amount,
      'currency'                => $tx->currency,
      'merchant_note'           => $request->reason,
      'status'                  => 'processed',
      'processed_at'            => now(),
    ]);

    return response()->json($this->formatRefund($refund, $tx->reference));
  }

  /**
   * GET /api/stripe/v1/refunds/{id}
   */
  public function show(Request $request, string $id): JsonResponse
  {
    $refund = PaystackRefund::where('project_id', $request->_api_project->id)
      ->where('reference', $id)
      ->with('transaction')
      ->first();

    if (!$refund) {
      return response()->json([
        'error' => ['code' => 'resource_missing', 'message' => 'No such refund.'],
      ], 404);
    }

    return response()->json(
      $this->formatRefund($refund, $refund->transaction?->reference)
    );
  }

  /**
   * GET /api/stripe/v1/refunds
   */
  public function index(Request $request): JsonResponse
  {
    $refunds = PaystackRefund::where('project_id', $request->_api_project->id)
      ->with('transaction')
      ->latest()
      ->paginate(20);

    return response()->json([
      'object'   => 'list',
      'data'     => $refunds->map(fn($r) => $this->formatRefund($r, $r->transaction?->reference)),
      'has_more' => $refunds->hasMorePages(),
    ]);
  }

  private function formatRefund(PaystackRefund $refund, ?string $paymentIntentId): array
  {
    return [
      'id'             => $refund->reference,
      'object'         => 'refund',
      'amount'         => $refund->amount,
      'currency'       => strtolower($refund->currency),
      'payment_intent' => $paymentIntentId,
      'reason'         => $refund->merchant_note,
      'status'         => $refund->status === 'processed' ? 'succeeded' : $refund->status,
      'created'        => $refund->created_at->timestamp,
    ];
  }
}
