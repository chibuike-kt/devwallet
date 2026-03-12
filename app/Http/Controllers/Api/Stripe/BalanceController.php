<?php

namespace App\Http\Controllers\Api\Stripe;

use App\Http\Controllers\Controller;
use App\Models\PaystackRefund;
use App\Models\PaystackTransaction;
use App\Models\PaystackTransfer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
  /**
   * GET /api/stripe/v1/balance
   */
  public function show(Request $request): JsonResponse
  {
    $projectId = $request->_api_project->id;

    $totalIn = PaystackTransaction::where('project_id', $projectId)
      ->where('status', 'success')->sum('amount');

    $totalOut = PaystackTransfer::where('project_id', $projectId)
      ->where('status', 'success')->sum('amount');

    $totalRefunded = PaystackRefund::where('project_id', $projectId)
      ->where('status', 'processed')->sum('amount');

    $available = max(0, $totalIn - $totalOut - $totalRefunded);

    return response()->json([
      'object'    => 'balance',
      'available' => [[
        'amount'   => $available,
        'currency' => 'usd',
        'source_types' => ['card' => $available],
      ]],
      'pending' => [[
        'amount'   => 0,
        'currency' => 'usd',
        'source_types' => ['card' => 0],
      ]],
      'livemode' => false,
    ]);
  }
}
