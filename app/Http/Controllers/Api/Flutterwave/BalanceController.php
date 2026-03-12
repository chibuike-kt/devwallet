<?php

namespace App\Http\Controllers\Api\Flutterwave;

use App\Http\Controllers\Controller;
use App\Models\PaystackTransaction;
use App\Models\PaystackTransfer;
use App\Models\PaystackRefund;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
  /**
   * GET /api/flutterwave/v3/balances/{currency}
   */
  public function show(Request $request, string $currency): JsonResponse
  {
    $balance = $this->calculateBalance($request->_api_project->id, strtoupper($currency));

    return response()->json([
      'status'  => 'success',
      'message' => 'Wallet balance fetched',
      'data'    => [
        'currency'          => strtoupper($currency),
        'available_balance' => $balance / 100,
        'ledger_balance'    => $balance / 100,
      ],
    ]);
  }

  /**
   * GET /api/flutterwave/v3/balances
   */
  public function index(Request $request): JsonResponse
  {
    $currencies = ['NGN', 'USD', 'KES', 'GHS'];
    $projectId  = $request->_api_project->id;

    $balances = collect($currencies)->map(fn($currency) => [
      'currency'          => $currency,
      'available_balance' => $this->calculateBalance($projectId, $currency) / 100,
      'ledger_balance'    => $this->calculateBalance($projectId, $currency) / 100,
    ]);

    return response()->json([
      'status'  => 'success',
      'message' => 'Wallet balances fetched',
      'data'    => $balances,
    ]);
  }

  private function calculateBalance(int $projectId, string $currency): int
  {
    $in = PaystackTransaction::where('project_id', $projectId)
      ->where('status', 'success')
      ->where('currency', $currency)
      ->sum('amount');

    $out = PaystackTransfer::where('project_id', $projectId)
      ->where('status', 'success')
      ->where('currency', $currency)
      ->sum('amount');

    $refunded = PaystackRefund::where('project_id', $projectId)
      ->where('status', 'processed')
      ->where('currency', $currency)
      ->sum('amount');

    return max(0, $in - $out - $refunded);
  }
}
