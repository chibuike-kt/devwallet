<?php

namespace App\Services;

use App\Enums\LedgerDirection;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\LedgerEntry;
use App\Models\Project;
use App\Models\SettlementBatch;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SettlementService
{
  /**
   * Run a settlement batch for a wallet.
   *
   * Finds all unsettled successful debit-type transactions,
   * groups them into a batch, debits the settlement amount
   * from the wallet, and marks transactions as settled.
   */
  public function run(
    Wallet  $wallet,
    Project $project,
    ?string $notes = null
  ): SettlementBatch {
    // Find eligible transactions:
    // - Successful wallet debits and transfers not yet in a settlement batch
    $eligible = Transaction::where('wallet_id', $wallet->id)
      ->whereIn('type', [
        TransactionType::WalletDebit->value,
        TransactionType::BankTransfer->value,
      ])
      ->where('status', TransactionStatus::Success->value)
      ->whereNull('settlement_batch_id')
      ->get();

    if ($eligible->isEmpty()) {
      throw new \RuntimeException(
        'No eligible transactions found for settlement. '
          . 'Only successful debit transactions that have not been settled qualify.'
      );
    }

    $totalAmount = $eligible->sum('amount');

    if ($wallet->available_balance < $totalAmount) {
      throw new \RuntimeException(
        "Insufficient wallet balance for settlement. "
          . "Required: {$wallet->formatAmount($totalAmount)}, "
          . "Available: {$wallet->formattedAvailableBalance()}"
      );
    }

    return DB::transaction(function () use (
      $wallet,
      $project,
      $eligible,
      $totalAmount,
      $notes
    ) {
      $wallet = Wallet::lockForUpdate()->findOrFail($wallet->id);

      // Create the batch record
      $batch = SettlementBatch::create([
        'project_id'        => $project->id,
        'wallet_id'         => $wallet->id,
        'status'            => 'processing',
        'total_amount'      => $totalAmount,
        'currency'          => $wallet->currency,
        'transaction_count' => $eligible->count(),
        'notes'             => $notes,
      ]);

      // Tag all eligible transactions with this batch
      Transaction::whereIn('id', $eligible->pluck('id'))
        ->update(['settlement_batch_id' => $batch->id]);

      // Create the settlement transaction
      $settlementTx = Transaction::create([
        'wallet_id'      => $wallet->id,
        'project_id'     => $project->id,
        'type'           => TransactionType::Settlement,
        'status'         => TransactionStatus::Success,
        'amount'         => $totalAmount,
        'currency'       => $wallet->currency,
        'balance_before' => $wallet->balance,
        'balance_after'  => $wallet->balance - $totalAmount,
        'narration'      => "Settlement batch {$batch->reference} — {$eligible->count()} transactions",
        'provider'       => 'simulation',
        'completed_at'   => now(),
        'settlement_batch_id' => $batch->id,
      ]);

      // Debit the wallet
      $wallet->decrement('balance', $totalAmount);
      $wallet->decrement('available_balance', $totalAmount);
      $wallet->decrement('ledger_balance', $totalAmount);

      // Write ledger entry
      LedgerEntry::create([
        'wallet_id'      => $wallet->id,
        'transaction_id' => $settlementTx->id,
        'direction'      => LedgerDirection::Debit,
        'amount'         => $totalAmount,
        'currency'       => $wallet->currency,
        'running_balance' => $wallet->fresh()->balance,
        'narration'      => "Settlement: {$batch->reference}",
      ]);

      // Mark batch completed
      $batch->update([
        'status'      => 'completed',
        'settled_at'  => now(),
      ]);

      return $batch->fresh();
    });
  }

  /**
   * Get unsettled transaction count and total for a wallet.
   * Used to preview what a settlement run would include.
   */
  public function preview(Wallet $wallet): array
  {
    $eligible = Transaction::where('wallet_id', $wallet->id)
      ->whereIn('type', [
        TransactionType::WalletDebit->value,
        TransactionType::BankTransfer->value,
      ])
      ->where('status', TransactionStatus::Success->value)
      ->whereNull('settlement_batch_id')
      ->get();

    return [
      'count'  => $eligible->count(),
      'total'  => $eligible->sum('amount'),
      'items'  => $eligible,
    ];
  }
}
