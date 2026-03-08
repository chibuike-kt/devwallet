<?php

namespace App\Services;

use App\Models\Wallet;

class WalletService
{
  public function __construct(protected TransactionService $transactions) {}

  public function freeze(Wallet $wallet): Wallet
  {
    if ($wallet->isClosed()) {
      throw new \RuntimeException('Cannot freeze a closed wallet.');
    }

    $wallet->update(['status' => 'frozen']);
    return $wallet->fresh();
  }

  public function unfreeze(Wallet $wallet): Wallet
  {
    if (! $wallet->isFrozen()) {
      throw new \RuntimeException('Wallet is not frozen.');
    }

    $wallet->update(['status' => 'active']);
    return $wallet->fresh();
  }

  public function close(Wallet $wallet): Wallet
  {
    $wallet->update(['status' => 'closed']);
    return $wallet->fresh();
  }
}
