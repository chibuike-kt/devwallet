<?php

namespace App\Http\Controllers\Api\Paystack;

use App\Http\Controllers\Controller;
use App\Models\PaystackRefund;
use App\Models\PaystackTransaction;
use App\Services\PaystackResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefundController extends Controller
{
  public function __construct(protected PaystackResponseService $response) {}

  /**
   * POST /api/paystack/refund
   */
  public function store(Request $request): JsonResponse
  {
    $request->validate([
      'transaction'   => ['required', 'string'],
      'amount'        => ['nullable', 'integer', 'min:100'],
      'currency'      => ['nullable', 'string'],
      'customer_note' => ['nullable', 'string'],
      'merchant_note' => ['nullable', 'string'],
    ]);

    $tx = PaystackTransaction::where('project_id', $request->_api_project->id)
      ->where('reference', $request->transaction)
      ->first();

    if (!$tx) {
      return $this->response->errorResponse('Transaction not found', 404);
    }

    if (!$tx->isSuccess()) {
      return $this->response->errorResponse(
        'Transaction has not been paid, so it cannot be refunded',
        400
      );
    }

    $refundAmount = $request->amount ?? $tx->amount;

    if ($refundAmount > $tx->amount) {
      return $this->response->errorResponse(
        'Refund amount cannot exceed transaction amount',
        400
      );
    }

    $refund = PaystackRefund::create([
      'project_id'              => $request->_api_project->id,
      'paystack_transaction_id' => $tx->id,
      'amount'                  => $refundAmount,
      'currency'                => $request->currency ?? $tx->currency,
      'customer_note'           => $request->customer_note,
      'merchant_note'           => $request->merchant_note,
      'status'                  => 'processed',
      'processed_at'            => now(),
    ]);

    return response()->json($this->response->refundResponse($refund));
  }

  /**
   * GET /api/paystack/refund/{reference}
   */
  public function show(Request $request, string $reference): JsonResponse
  {
    $refund = PaystackRefund::where('project_id', $request->_api_project->id)
      ->where('reference', $reference)
      ->first();

    if (!$refund) {
      return $this->response->errorResponse('Refund not found', 404);
    }

    return response()->json([
      'status'  => true,
      'message' => 'Refund retrieved',
      'data'    => $this->response->refundData($refund),
    ]);
  }

  /**
   * GET /api/paystack/refund
   */
  public function index(Request $request): JsonResponse
  {
    $refunds = PaystackRefund::where('project_id', $request->_api_project->id)
      ->latest()
      ->paginate(50);

    return response()->json($this->response->refundListResponse($refunds));
  }
}
