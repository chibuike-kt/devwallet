<?php

use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\SettlementController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth.apikey')->group(function () {

  // ── Health ────────────────────────────────────────────────────────────────
  Route::get('ping', fn() => response()->json([
    'status'    => 'ok',
    'message'   => 'DevWallet API is running.',
    'timestamp' => now()->toIso8601String(),
  ]));

  // ── Project ───────────────────────────────────────────────────────────────
  Route::get('project', [ProjectController::class, 'show']);

  // ── Wallets ───────────────────────────────────────────────────────────────
  Route::get('wallets',                        [WalletController::class, 'index']);
  Route::post('wallets',                       [WalletController::class, 'store']);
  Route::get('wallets/{reference}',            [WalletController::class, 'show']);
  Route::post('wallets/{reference}/fund',      [WalletController::class, 'fund']);
  Route::post('wallets/{reference}/debit',     [WalletController::class, 'debit']);
  Route::post('wallets/{reference}/freeze',    [WalletController::class, 'freeze']);
  Route::post('wallets/{reference}/unfreeze',  [WalletController::class, 'unfreeze']);
  Route::get('wallets/{reference}/ledger',     [WalletController::class, 'ledger']);
  Route::get('wallets/{reference}/transactions', [WalletController::class, 'transactions']);
  Route::post('wallets/transfer',              [WalletController::class, 'transfer']);

  // ── Transactions ──────────────────────────────────────────────────────────
  Route::get('transactions',                   [TransactionController::class, 'index']);
  Route::get('transactions/{reference}',       [TransactionController::class, 'show']);
  Route::post('transactions/{reference}/reverse', [TransactionController::class, 'reverse']);

  // ── Webhooks ──────────────────────────────────────────────────────────────
  Route::get('webhooks/endpoints',             [WebhookController::class, 'indexEndpoints']);
  Route::post('webhooks/endpoints',            [WebhookController::class, 'storeEndpoint']);
  Route::delete('webhooks/endpoints/{id}',     [WebhookController::class, 'destroyEndpoint']);
  Route::get('webhooks/events',                [WebhookController::class, 'indexEvents']);
  Route::get('webhooks/events/{id}',           [WebhookController::class, 'showEvent']);
  Route::post('webhooks/deliveries/{id}/retry', [WebhookController::class, 'retryDelivery']);

  // ── Settlements ───────────────────────────────────────────────────────────
  Route::get('settlements',                    [SettlementController::class, 'index']);
  Route::post('settlements',                   [SettlementController::class, 'run']);
  Route::get('settlements/{reference}',        [SettlementController::class, 'show']);
});
