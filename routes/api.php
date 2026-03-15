<?php

use App\Http\Controllers\Api\Paystack\AuthorizationController;
use App\Http\Controllers\Api\Paystack\BalanceController;
use App\Http\Controllers\Api\Paystack\CustomerController;
use App\Http\Controllers\Api\Paystack\RefundController;
use App\Http\Controllers\Api\Paystack\TransactionController;
use App\Http\Controllers\Api\Paystack\TransferController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Flutterwave\PaymentController as FlwPaymentController;
use App\Http\Controllers\Api\Flutterwave\TransactionController as FlwTransactionController;
use App\Http\Controllers\Api\Flutterwave\TransferController as FlwTransferController;
use App\Http\Controllers\Api\Flutterwave\BalanceController as FlwBalanceController;
use App\Http\Controllers\Api\Stripe\PaymentIntentController;
use App\Http\Controllers\Api\Stripe\RefundController as StripeRefundController;
use App\Http\Controllers\Api\Stripe\TransferController as StripeTransferController;
use App\Http\Controllers\Api\Stripe\BalanceController as StripeBalanceController;

  // ── Health ────────────────────────────────────────────────────────────────
  Route::get('ping', fn() => response()->json([
    'status'  => true,
    'message' => 'DevWallet Sandbox is running.',
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

// ── Flutterwave-compatible API ───────────────────────────────────────────────
Route::prefix('flutterwave/v3')
  ->middleware('auth.apikey')
  ->group(function () {

    Route::post('payments',                          [FlwPaymentController::class, 'store']);
    Route::get('transactions/{id}/verify',           [FlwTransactionController::class, 'verify']);
    Route::get('transactions',                       [FlwTransactionController::class, 'index']);
    Route::post('transfers',                         [FlwTransferController::class, 'store']);
    Route::get('transfers/{id}',                     [FlwTransferController::class, 'show']);
    Route::get('transfers',                          [FlwTransferController::class, 'index']);
    Route::get('balances/{currency}',                [FlwBalanceController::class, 'show']);
    Route::get('balances',                           [FlwBalanceController::class, 'index']);
  });

// ── Stripe-compatible API ───────────────────────────────────────────────
Route::prefix('stripe/v1')
  ->middleware('auth.apikey')
  ->group(function () {

    Route::post('payment_intents',                   [PaymentIntentController::class, 'store']);
    Route::get('payment_intents/{id}',               [PaymentIntentController::class, 'show']);
    Route::post('payment_intents/{id}/confirm',      [PaymentIntentController::class, 'confirm']);
    Route::post('payment_intents/{id}/cancel',       [PaymentIntentController::class, 'cancel']);
    Route::get('payment_intents',                    [PaymentIntentController::class, 'index']);
    Route::post('refunds',                           [StripeRefundController::class, 'store']);
    Route::get('refunds/{id}',                       [StripeRefundController::class, 'show']);
    Route::get('refunds',                            [StripeRefundController::class, 'index']);
    Route::post('transfers',                         [StripeTransferController::class, 'store']);
    Route::get('transfers/{id}',                     [StripeTransferController::class, 'show']);
    Route::get('transfers',                          [StripeTransferController::class, 'index']);
    Route::get('balance',                            [StripeBalanceController::class, 'show']);
  });

// Checkout page — no auth required
Route::get('paystack/checkout/{reference}', [
  App\Http\Controllers\Api\Paystack\CheckoutController::class,
  'show'
])->name('paystack.checkout');

Route::post('paystack/checkout/{reference}', [
  App\Http\Controllers\Api\Paystack\CheckoutController::class,
  'pay'
])->name('paystack.checkout.pay');

//Flutterwave checkout
Route::get('flutterwave/v3/checkout/{reference}', [
  App\Http\Controllers\Api\Flutterwave\CheckoutController::class, 'show'
]);

Route::post('flutterwave/v3/checkout/{reference}', [
  App\Http\Controllers\Api\Flutterwave\CheckoutController::class,
  'pay'
]);

// Stripe checkout
Route::get('stripe/v1/checkout/{reference}',  [
  App\Http\Controllers\Api\Stripe\CheckoutController::class,
  'show'
]);
Route::post('stripe/v1/checkout/{reference}', [
  App\Http\Controllers\Api\Stripe\CheckoutController::class,
  'pay'
]);
