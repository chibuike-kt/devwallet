<?php

use App\Http\Controllers\Api\Paystack\AuthorizationController;
use App\Http\Controllers\Api\Paystack\BalanceController;
use App\Http\Controllers\Api\Paystack\CustomerController;
use App\Http\Controllers\Api\Paystack\RefundController;
use App\Http\Controllers\Api\Paystack\TransactionController;
use App\Http\Controllers\Api\Paystack\TransferController;
use Illuminate\Support\Facades\Route;

  // ── Health ────────────────────────────────────────────────────────────────
  Route::get('ping', fn() => response()->json([
    'status'  => true,
    'message' => 'DevWallet Paystack Sandbox is running.',
  ]));

  // ── Paystack-compatible API ───────────────────────────────────────────────
  Route::prefix('paystack')
    ->middleware('auth.apikey')
    ->group(function () {

      // Transactions
      Route::post('transaction/initialize',        [TransactionController::class, 'initialize']);
      Route::get('transaction/verify/{reference}', [TransactionController::class, 'verify']);
      Route::get('transaction/{id}',               [TransactionController::class, 'show']);
      Route::get('transaction',                    [TransactionController::class, 'index']);

      // Refunds
      Route::post('refund',                        [RefundController::class, 'store']);
      Route::get('refund/{reference}',             [RefundController::class, 'show']);
      Route::get('refund',                         [RefundController::class, 'index']);

      // Transfers
      Route::post('transfer',                      [TransferController::class, 'store']);
      Route::get('transfer/verify/{reference}',    [TransferController::class, 'verify']);
      Route::get('transfer/{reference}',           [TransferController::class, 'show']);
      Route::get('transfer',                       [TransferController::class, 'index']);

      // Balance
      Route::get('balance',                        [BalanceController::class, 'show']);
      Route::get('balance/ledger',                 [BalanceController::class, 'ledger']);

      // Customers
      Route::post('customer',                      [CustomerController::class, 'store']);
      Route::get('customer/{email_or_code}',       [CustomerController::class, 'show']);
      Route::get('customer',                       [CustomerController::class, 'index']);
    });
