<?php

namespace App\Services;

use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class WalletService
{
  /**
   * Credit a wallet by a given amount (in minor units).
   *
   * This is the ONLY place credit operations should happen.
   * Phase 3 will add ledger entry creation here.
   */
  public function credit(Wallet $wallet, int $amount, string $narration = ''): Wallet
  {
    if ($amount <= 0) {
      throw new \InvalidArgumentException('Credit amount must be a positive integer.');
    }

    if ($wallet->isClosed()) {
      throw new \RuntimeException('Cannot credit a closed wallet.');
    }

    DB::transaction(function () use ($wallet, $amount) {
      // Lock the row to prevent race conditions
      $wallet = Wallet::lockForUpdate()->findOrFail($wallet->id);

      $wallet->increment('balance', $amount);
      $wallet->increment('available_balance', $amount);
      $wallet->increment('ledger_balance', $amount);
    });

    return $wallet->fresh();
  }

  /**
   * Debit a wallet by a given amount (in minor units).
   *
   * Validates sufficient balance before proceeding.
   */
  public function debit(Wallet $wallet, int $amount, string $narration = ''): Wallet
  {
    if ($amount <= 0) {
      throw new \InvalidArgumentException('Debit amount must be a positive integer.');
    }

    if (! $wallet->canTransact()) {
      throw new \RuntimeException('Wallet is not active. Current status: ' . $wallet->status);
    }

    DB::transaction(function () use ($wallet, $amount) {
      $wallet = Wallet::lockForUpdate()->findOrFail($wallet->id);

      if ($wallet->available_balance < $amount) {
        throw new \RuntimeException(
          'Insufficient balance. Available: ' . $wallet->available_balance . ', Requested: ' . $amount
        );
      }

      $wallet->decrement('balance', $amount);
      $wallet->decrement('available_balance', $amount);
      $wallet->decrement('ledger_balance', $amount);
    });

    return $wallet->fresh();
  }

  /**
   * Freeze a wallet — blocks all transactions.
   */
  public function freeze(Wallet $wallet): Wallet
  {
    if ($wallet->isClosed()) {
      throw new \RuntimeException('Cannot freeze a closed wallet.');
    }

    $wallet->update(['status' => 'frozen']);

    return $wallet->fresh();
  }

  /**
   * Unfreeze a wallet back to active.
   */
  public function unfreeze(Wallet $wallet): Wallet
  {
    if (! $wallet->isFrozen()) {
      throw new \RuntimeException('Wallet is not frozen.');
    }

    $wallet->update(['status' => 'active']);

    return $wallet->fresh();
  }

  /**
   * Close a wallet permanently.
   */
  public function close(Wallet $wallet): Wallet
  {
    $wallet->update(['status' => 'closed']);

    return $wallet->fresh();
  }
}
