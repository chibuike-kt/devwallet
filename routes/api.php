<?php

use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WebhookEndpointController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth.apikey')->group(function () {

  // ── Wallets ───────────────────────────────────────────────────────────────
  Route::get('wallets',            [WalletController::class, 'index']);
  Route::post('wallets/fund',      [WalletController::class, 'fund']);
  Route::post('wallets/debit',     [WalletController::class, 'debit']);
  Route::get('wallets/{reference}', [WalletController::class, 'show']);
  Route::post('wallets/transfer',  [WalletController::class, 'transfer']);

  // ── Transactions ──────────────────────────────────────────────────────────
  Route::get('transactions',               [TransactionController::class, 'index']);
  Route::get('transactions/{reference}',   [TransactionController::class, 'show']);

  // ── Webhooks ──────────────────────────────────────────────────────────────
  Route::get('webhooks',     [WebhookEndpointController::class, 'index']);
  Route::post('webhooks',    [WebhookEndpointController::class, 'store']);
  Route::delete('webhooks/{id}', [WebhookEndpointController::class, 'destroy']);

  // ── Health check ──────────────────────────────────────────────────────────
  Route::get('ping', fn() => response()->json([
    'status'    => 'ok',
    'message'   => 'DevWallet API is running.',
    'timestamp' => now()->toIso8601String(),
  ]));
});
