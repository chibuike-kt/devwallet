<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ScenarioController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => view('welcome'))->name('welcome');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::resource('projects', ProjectController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy']);

    Route::resource('projects.wallets', WalletController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy']);

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

    Route::get(
        'projects/{project}/wallets/{wallet}/ledger',
        [LedgerController::class, 'index']
    )
        ->name('projects.wallets.ledger');

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
});

require __DIR__ . '/auth.php';
