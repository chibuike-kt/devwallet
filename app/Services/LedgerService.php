<?php

namespace App\Services;

use App\Enums\LedgerDirection;
use App\Models\LedgerEntry;
use App\Models\Transaction;
use App\Models\Wallet;

class LedgerService
{
  /**
   * Write a credit ledger entry for a completed transaction.
   * Called after the wallet balance has been updated.
   */
  public function recordCredit(
    Wallet $wallet,
    Transaction $transaction,
    string $narration = ''
  ): LedgerEntry {
    return LedgerEntry::create([
      'wallet_id'       => $wallet->id,
      'transaction_id'  => $transaction->id,
      'direction'       => LedgerDirection::Credit,
      'amount'          => $transaction->amount,
      'currency'        => $wallet->currency,
      'running_balance' => $wallet->fresh()->balance,
      'narration'       => $narration ?: $transaction->narration,
    ]);
  }

  /**
   * Write a debit ledger entry for a completed transaction.
   * Called after the wallet balance has been updated.
   */
  public function recordDebit(
    Wallet $wallet,
    Transaction $transaction,
    string $narration = ''
  ): LedgerEntry {
    return LedgerEntry::create([
      'wallet_id'       => $wallet->id,
      'transaction_id'  => $transaction->id,
      'direction'       => LedgerDirection::Debit,
      'amount'          => $transaction->amount,
      'currency'        => $wallet->currency,
      'running_balance' => $wallet->fresh()->balance,
      'narration'       => $narration ?: $transaction->narration,
    ]);
  }

  /**
   * Verify ledger integrity: sum of credits minus sum of debits
   * should equal the wallet's current balance.
   * Useful for debugging and audit scenarios.
   */
  public function verifyBalance(Wallet $wallet): array
  {
    $credits = LedgerEntry::where('wallet_id', $wallet->id)
      ->where('direction', LedgerDirection::Credit->value)
      ->sum('amount');

    $debits = LedgerEntry::where('wallet_id', $wallet->id)
      ->where('direction', LedgerDirection::Debit->value)
      ->sum('amount');

    $computed = $credits - $debits;
    $actual   = $wallet->balance;

    return [
      'credits'     => $credits,
      'debits'      => $debits,
      'computed'    => $computed,
      'actual'      => $actual,
      'is_balanced' => $computed === $actual,
    ];
  }
}
