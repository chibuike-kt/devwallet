<?php

namespace App\Services;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class TransactionService
{
  public function __construct(protected LedgerService $ledger) {}

  /**
   * Fund a wallet (credit). Creates a transaction + ledger entry atomically.
   *
   * @throws \RuntimeException on frozen/closed wallet
   */
  public function fund(
    Wallet $wallet,
    int    $amount,
    string $narration       = 'Wallet funding',
    string $provider        = 'simulation',
    ?string $idempotencyKey = null
  ): Transaction {
    // Idempotency check: return existing if key already used
    if ($idempotencyKey) {
      $existing = Transaction::where('idempotency_key', $idempotencyKey)->first();
      if ($existing) {
        return $existing;
      }
    }

    if (! $wallet->canTransact()) {
      return $this->recordFailedTransaction(
        wallet: $wallet,
        amount: $amount,
        type: TransactionType::WalletFunding,
        narration: $narration,
        failureReason: "Wallet is {$wallet->status}. Cannot fund.",
        idempotencyKey: $idempotencyKey
      );
    }

    return DB::transaction(function () use ($wallet, $amount, $narration, $provider, $idempotencyKey) {
      // Lock wallet row
      $wallet = Wallet::lockForUpdate()->findOrFail($wallet->id);

      $balanceBefore = $wallet->balance;

      // Create the transaction record first (pending)
      $transaction = Transaction::create([
        'wallet_id'       => $wallet->id,
        'project_id'      => $wallet->project_id,
        'type'            => TransactionType::WalletFunding,
        'status'          => TransactionStatus::Pending,
        'amount'          => $amount,
        'currency'        => $wallet->currency,
        'balance_before'  => $balanceBefore,
        'balance_after'   => $balanceBefore + $amount,
        'narration'       => $narration,
        'provider'        => $provider,
        'idempotency_key' => $idempotencyKey,
        'completed_at'    => null,
      ]);

      // Apply the balance change
      $wallet->increment('balance', $amount);
      $wallet->increment('available_balance', $amount);
      $wallet->increment('ledger_balance', $amount);

      // Mark transaction success
      $transaction->update([
        'status'       => TransactionStatus::Success,
        'completed_at' => now(),
      ]);

      // Write ledger entry
      $this->ledger->recordCredit($wallet, $transaction, $narration);

      return $transaction->fresh();
    });
  }

  /**
   * Debit a wallet. Creates a transaction + ledger entry atomically.
   * Produces a failed transaction record on insufficient balance.
   */
  public function debit(
    Wallet $wallet,
    int    $amount,
    string $narration       = 'Wallet debit',
    string $provider        = 'simulation',
    ?string $idempotencyKey = null
  ): Transaction {
    if ($idempotencyKey) {
      $existing = Transaction::where('idempotency_key', $idempotencyKey)->first();
      if ($existing) {
        return $existing;
      }
    }

    if (! $wallet->canTransact()) {
      return $this->recordFailedTransaction(
        wallet: $wallet,
        amount: $amount,
        type: TransactionType::WalletDebit,
        narration: $narration,
        failureReason: "Wallet is {$wallet->status}. Cannot debit.",
        idempotencyKey: $idempotencyKey
      );
    }

    // Pre-flight balance check (outside DB lock for fast failure)
    if ($wallet->available_balance < $amount) {
      return $this->recordFailedTransaction(
        wallet: $wallet,
        amount: $amount,
        type: TransactionType::WalletDebit,
        narration: $narration,
        failureReason: "Insufficient balance. Available: {$wallet->available_balance}, Requested: {$amount}.",
        idempotencyKey: $idempotencyKey
      );
    }

    return DB::transaction(function () use ($wallet, $amount, $narration, $provider, $idempotencyKey) {
      $wallet = Wallet::lockForUpdate()->findOrFail($wallet->id);

      // Re-check inside lock to prevent race conditions
      if ($wallet->available_balance < $amount) {
        throw new \RuntimeException(
          "Insufficient balance after lock. Available: {$wallet->available_balance}"
        );
      }

      $balanceBefore = $wallet->balance;

      $transaction = Transaction::create([
        'wallet_id'       => $wallet->id,
        'project_id'      => $wallet->project_id,
        'type'            => TransactionType::WalletDebit,
        'status'          => TransactionStatus::Pending,
        'amount'          => $amount,
        'currency'        => $wallet->currency,
        'balance_before'  => $balanceBefore,
        'balance_after'   => $balanceBefore - $amount,
        'narration'       => $narration,
        'provider'        => $provider,
        'idempotency_key' => $idempotencyKey,
        'completed_at'    => null,
      ]);

      $wallet->decrement('balance', $amount);
      $wallet->decrement('available_balance', $amount);
      $wallet->decrement('ledger_balance', $amount);

      $transaction->update([
        'status'       => TransactionStatus::Success,
        'completed_at' => now(),
      ]);

      $this->ledger->recordDebit($wallet, $transaction, $narration);

      return $transaction->fresh();
    });
  }

  /**
   * Transfer between two wallets in the same project.
   * Creates two transactions (debit + credit) and two ledger entries.
   */
  public function transfer(
    Wallet $from,
    Wallet $to,
    int    $amount,
    string $narration       = 'Wallet transfer',
    ?string $idempotencyKey = null
  ): Transaction {
    if ($idempotencyKey) {
      $existing = Transaction::where('idempotency_key', $idempotencyKey)->first();
      if ($existing) {
        return $existing;
      }
    }

    if (! $from->canTransact()) {
      return $this->recordFailedTransaction(
        wallet: $from,
        amount: $amount,
        type: TransactionType::WalletTransfer,
        narration: $narration,
        failureReason: "Source wallet is {$from->status}.",
        idempotencyKey: $idempotencyKey
      );
    }

    if ($from->available_balance < $amount) {
      return $this->recordFailedTransaction(
        wallet: $from,
        amount: $amount,
        type: TransactionType::WalletTransfer,
        narration: $narration,
        failureReason: "Insufficient balance for transfer. Available: {$from->available_balance}",
        idempotencyKey: $idempotencyKey
      );
    }

    return DB::transaction(function () use ($from, $to, $amount, $narration, $idempotencyKey) {
      $from = Wallet::lockForUpdate()->findOrFail($from->id);
      $to   = Wallet::lockForUpdate()->findOrFail($to->id);

      if ($from->available_balance < $amount) {
        throw new \RuntimeException('Insufficient balance after lock.');
      }

      // Debit the source
      $debitTx = Transaction::create([
        'wallet_id'         => $from->id,
        'project_id'        => $from->project_id,
        'related_wallet_id' => $to->id,
        'type'              => TransactionType::WalletTransfer,
        'status'            => TransactionStatus::Pending,
        'amount'            => $amount,
        'currency'          => $from->currency,
        'balance_before'    => $from->balance,
        'balance_after'     => $from->balance - $amount,
        'narration'         => "Transfer to {$to->name}: {$narration}",
        'idempotency_key'   => $idempotencyKey,
      ]);

      $from->decrement('balance', $amount);
      $from->decrement('available_balance', $amount);
      $from->decrement('ledger_balance', $amount);

      $debitTx->update(['status' => TransactionStatus::Success, 'completed_at' => now()]);
      $this->ledger->recordDebit($from, $debitTx);

      // Credit the destination
      $creditTx = Transaction::create([
        'wallet_id'         => $to->id,
        'project_id'        => $to->project_id,
        'related_wallet_id' => $from->id,
        'type'              => TransactionType::WalletTransfer,
        'status'            => TransactionStatus::Pending,
        'amount'            => $amount,
        'currency'          => $to->currency,
        'balance_before'    => $to->balance,
        'balance_after'     => $to->balance + $amount,
        'narration'         => "Transfer from {$from->name}: {$narration}",
      ]);

      $to->increment('balance', $amount);
      $to->increment('available_balance', $amount);
      $to->increment('ledger_balance', $amount);

      $creditTx->update(['status' => TransactionStatus::Success, 'completed_at' => now()]);
      $this->ledger->recordCredit($to, $creditTx);

      return $debitTx->fresh();
    });
  }

  /**
   * Reverse a successful transaction.
   * Restores the wallet balance and writes offsetting ledger entries.
   */
  public function reverse(Transaction $transaction, string $reason = 'Reversal'): Transaction
  {
    if (! $transaction->status->canTransitionTo(TransactionStatus::Reversed)) {
      throw new \RuntimeException(
        "Cannot reverse a transaction with status: {$transaction->status->value}"
      );
    }

    return DB::transaction(function () use ($transaction, $reason) {
      $wallet = Wallet::lockForUpdate()->findOrFail($transaction->wallet_id);

      // Re-credit if original was a debit, re-debit if original was a credit
      $isDebitType = in_array($transaction->type, [
        TransactionType::WalletDebit,
        TransactionType::WalletTransfer,
        TransactionType::BankTransfer,
        TransactionType::Settlement,
      ]);

      if ($isDebitType) {
        $wallet->increment('balance', $transaction->amount);
        $wallet->increment('available_balance', $transaction->amount);
        $wallet->increment('ledger_balance', $transaction->amount);
      } else {
        if ($wallet->available_balance < $transaction->amount) {
          throw new \RuntimeException('Insufficient balance to reverse this credit.');
        }
        $wallet->decrement('balance', $transaction->amount);
        $wallet->decrement('available_balance', $transaction->amount);
        $wallet->decrement('ledger_balance', $transaction->amount);
      }

      // Create the reversal transaction
      $reversalTx = Transaction::create([
        'wallet_id'      => $wallet->id,
        'project_id'     => $transaction->project_id,
        'type'           => TransactionType::Reversal,
        'status'         => TransactionStatus::Success,
        'amount'         => $transaction->amount,
        'currency'       => $transaction->currency,
        'balance_before' => $isDebitType
          ? $wallet->balance - $transaction->amount
          : $wallet->balance + $transaction->amount,
        'balance_after'  => $wallet->fresh()->balance,
        'narration'      => "Reversal of {$transaction->reference}: {$reason}",
        'completed_at'   => now(),
      ]);

      // Write offsetting ledger entry
      if ($isDebitType) {
        $this->ledger->recordCredit($wallet, $reversalTx, $reversalTx->narration);
      } else {
        $this->ledger->recordDebit($wallet, $reversalTx, $reversalTx->narration);
      }

      // Mark original as reversed
      $transaction->update(['status' => TransactionStatus::Reversed]);

      return $reversalTx->fresh();
    });
  }

  /**
   * Internal helper: record a clean failed transaction without touching balances.
   */
  private function recordFailedTransaction(
    Wallet          $wallet,
    int             $amount,
    TransactionType $type,
    string          $narration,
    string          $failureReason,
    ?string         $idempotencyKey = null
  ): Transaction {
    return Transaction::create([
      'wallet_id'       => $wallet->id,
      'project_id'      => $wallet->project_id,
      'type'            => $type,
      'status'          => TransactionStatus::Failed,
      'amount'          => $amount,
      'currency'        => $wallet->currency,
      'balance_before'  => $wallet->balance,
      'balance_after'   => $wallet->balance,
      'narration'       => $narration,
      'failure_reason'  => $failureReason,
      'idempotency_key' => $idempotencyKey,
      'completed_at'    => now(),
    ]);
  }
}
