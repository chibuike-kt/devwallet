<?php

namespace App\Http\Controllers\Api\Paystack;

use App\Http\Controllers\Controller;
use App\Models\PaystackTransaction;
use App\Services\PaystackResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
  public function __construct(protected PaystackResponseService $response) {}

  /**
   * GET /api/paystack/balance
   * Returns sum of all successful transactions minus refunds and transfers.
   */
  public function show(Request $request): JsonResponse
  {
    $project = $request->_api_project;

    $totalIn = PaystackTransaction::where('project_id', $project->id)
      ->where('status', 'success')
      ->sum('amount');

    $totalRefunded = \App\Models\PaystackRefund::where('project_id', $project->id)
      ->where('status', 'processed')
      ->sum('amount');

    $totalTransferred = \App\Models\PaystackTransfer::where('project_id', $project->id)
      ->where('status', 'success')
      ->sum('amount');

    $balance = max(0, $totalIn - $totalRefunded - $totalTransferred);

    return response()->json($this->response->balanceResponse($balance));
  }

  /**
   * GET /api/paystack/balance/ledger
   */
  public function ledger(Request $request): JsonResponse
  {
    $project = $request->_api_project;

    $transactions = PaystackTransaction::where('project_id', $project->id)
      ->where('status', 'success')
      ->latest()
      ->take(20)
      ->get();

    $entries = $transactions->map(fn($tx) => [
      'id'          => $tx->id,
      'domain'      => 'test',
      'amount'      => $tx->amount,
      'currency'    => $tx->currency,
      'type'        => 'credit',
      'status'      => 'success',
      'settled_by'  => null,
      'created_at'  => $tx->created_at->toIso8601String(),
    ]);

    return response()->json([
      'status'  => true,
      'message' => 'Balance ledger retrieved',
      'data'    => $entries,
    ]);
  }
}
