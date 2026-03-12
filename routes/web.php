<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ScenarioController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WebhookDeliveryController;
use App\Http\Controllers\WebhookEndpointController;
use App\Http\Controllers\AuditLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettlementController;
use App\Http\Controllers\ApiKeyController;
use App\Http\Controllers\TransactionExportController;

Route::get('/', fn() => view('welcome'))->name('welcome');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
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
        'projects/{project}/transactions/export',
        TransactionExportController::class
    )
        ->name('projects.transactions.export');

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

    Route::get('/audit', [AuditLogController::class, 'index'])
        ->name('audit.index');

    // Settlements
    Route::get(
        'projects/{project}/settlements',
        [SettlementController::class, 'index']
    )
        ->name('projects.settlements.index');

    Route::post(
        'projects/{project}/settlements/run',
        [SettlementController::class, 'run']
    )
        ->name('projects.settlements.run');

    Route::get(
        'projects/{project}/settlements/{batch}',
        [SettlementController::class, 'show']
    )
        ->name('projects.settlements.show');

    // API Keys
    Route::get(
        'projects/{project}/api-keys',
        [ApiKeyController::class, 'index']
    )
        ->name('projects.api-keys.index');

    Route::post(
        'projects/{project}/api-keys',
        [ApiKeyController::class, 'store']
    )
        ->name('projects.api-keys.store');

    Route::post(
        'projects/{project}/api-keys/{apiKey}/revoke',
        [ApiKeyController::class, 'revoke']
    )
        ->name('projects.api-keys.revoke');

    // ── Paystack-style UI pages ───────────────────────────────────────────────
    Route::get(
        'projects/{project}/overview',
        [App\Http\Controllers\Paystack\DashboardController::class, 'index']
    )
        ->name('projects.paystack.overview');

    Route::get(
        'projects/{project}/paystack/transactions',
        [App\Http\Controllers\Paystack\TransactionUiController::class, 'index']
    )
        ->name('projects.paystack.transactions');

    Route::get(
        'projects/{project}/paystack/transactions/{reference}',
        [App\Http\Controllers\Paystack\TransactionUiController::class, 'show']
    )
        ->name('projects.paystack.transactions.show');

    Route::get(
        'projects/{project}/paystack/transfers',
        [App\Http\Controllers\Paystack\TransferUiController::class, 'index']
    )
        ->name('projects.paystack.transfers');

    Route::get(
        'projects/{project}/paystack/transfers/{reference}',
        [App\Http\Controllers\Paystack\TransferUiController::class, 'show']
    )
        ->name('projects.paystack.transfers.show');

    Route::get(
        'projects/{project}/paystack/customers',
        [App\Http\Controllers\Paystack\CustomerUiController::class, 'index']
    )
        ->name('projects.paystack.customers');

    Route::get(
        'projects/{project}/paystack/customers/{code}',
        [App\Http\Controllers\Paystack\CustomerUiController::class, 'show']
    )
        ->name('projects.paystack.customers.show');


    // ── Simulation ───────────────────────────────────────────────────────────────
    Route::get(
        'projects/{project}/simulation',
        [App\Http\Controllers\SimulationController::class, 'index']
    )
        ->name('projects.simulation.index');

    Route::post(
        'projects/{project}/simulation/settings',
        [App\Http\Controllers\SimulationController::class, 'updateSettings']
    )
        ->name('projects.simulation.settings');

    Route::post(
        'projects/{project}/simulation/reset',
        [App\Http\Controllers\SimulationController::class, 'reset']
    )
        ->name('projects.simulation.reset');

    Route::post(
        'projects/{project}/simulation/webhook',
        [App\Http\Controllers\SimulationController::class, 'fireWebhook']
    )
        ->name('projects.simulation.webhook');

    Route::post(
        'projects/{project}/switch',
        [App\Http\Controllers\ProjectController::class, 'switch']
    )
        ->name('projects.switch');

    Route::get('/docs', [App\Http\Controllers\DocsController::class, 'index'])
        ->name('docs');

});

require __DIR__ . '/auth.php';
