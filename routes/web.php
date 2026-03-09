<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ScenarioController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WebhookDeliveryController;
use App\Http\Controllers\WebhookEndpointController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'))->name('welcome');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Projects
    Route::resource('projects', ProjectController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy']);

    // ── Wallet show page only — explicit routes come before resource ──
    Route::get(
        'projects/{project}/wallets/create',
        [WalletController::class, 'create']
    )
        ->name('projects.wallets.create');

    Route::post(
        'projects/{project}/wallets',
        [WalletController::class, 'store']
    )
        ->name('projects.wallets.store');

    Route::get(
        'projects/{project}/wallets',
        [WalletController::class, 'index']
    )
        ->name('projects.wallets.index');

    Route::get(
        'projects/{project}/wallets/{wallet}',
        [WalletController::class, 'show']
    )
        ->name('projects.wallets.show');

    Route::delete(
        'projects/{project}/wallets/{wallet}',
        [WalletController::class, 'destroy']
    )
        ->name('projects.wallets.destroy');

    // Wallet ledger
    Route::get(
        'projects/{project}/wallets/{wallet}/ledger',
        [LedgerController::class, 'index']
    )
        ->name('projects.wallets.ledger');

    // Transactions
    Route::get(
        'projects/{project}/transactions',
        [TransactionController::class, 'index']
    )
        ->name('projects.transactions.index');

    Route::get(
        'projects/{project}/transactions/{transaction}',
        [TransactionController::class, 'show']
    )
        ->name('projects.transactions.show');

    // Scenarios
    Route::get(
        'projects/{project}/scenarios',
        [ScenarioController::class, 'index']
    )
        ->name('projects.scenarios.index');

    Route::post(
        'projects/{project}/scenarios/run',
        [ScenarioController::class, 'run']
    )
        ->name('projects.scenarios.run');

    // Webhooks — all explicit, no resource() to avoid conflicts
    Route::get(
        'projects/{project}/webhooks',
        [WebhookEndpointController::class, 'index']
    )
        ->name('projects.webhooks.index');

    Route::get(
        'projects/{project}/webhooks/create',
        [WebhookEndpointController::class, 'create']
    )
        ->name('projects.webhooks.create');

    Route::post(
        'projects/{project}/webhooks',
        [WebhookEndpointController::class, 'store']
    )
        ->name('projects.webhooks.store');

    Route::post(
        'projects/{project}/webhooks/dispatch',
        [WebhookEndpointController::class, 'dispatch']
    )
        ->name('projects.webhooks.dispatch');

    Route::post(
        'projects/{project}/webhooks/events/{event}/duplicate',
        [WebhookEndpointController::class, 'duplicate']
    )
        ->name('projects.webhooks.duplicate');

    Route::get(
        'projects/{project}/webhooks/{webhook}',
        [WebhookEndpointController::class, 'show']
    )
        ->name('projects.webhooks.show');

    Route::delete(
        'projects/{project}/webhooks/{webhook}',
        [WebhookEndpointController::class, 'destroy']
    )
        ->name('projects.webhooks.destroy');

    Route::post(
        'projects/{project}/webhook-deliveries/{delivery}/retry',
        [WebhookDeliveryController::class, 'retry']
    )
        ->name('projects.webhook-deliveries.retry');
});

require __DIR__ . '/auth.php';
