<?php

namespace App\Http\Controllers\Api;

use App\Models\Wallet;
use App\Services\TransactionService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends ApiController
{
  public function __construct(
    protected TransactionService $transactions,
    protected WalletService      $wallets,
  ) {}

  /**
   * GET /api/v1/wallets
   * List all wallets in the authenticated project.
   */
  public function index(Request $request): JsonResponse
  {
    $project = $request->_api_project;

    $wallets = $project->wallets()
      ->latest()
      ->get()
      ->map(fn(Wallet $w) => $this->formatWallet($w));

    return $this->success($wallets);
  }

  /**
   * GET /api/v1/wallets/{reference}
   * Get a single wallet by its reference.
   */
  public function show(Request $request, string $reference): JsonResponse
  {
    $project = $request->_api_project;

    $wallet = $project->wallets()
      ->where('reference', $reference)
      ->first();

    if (!$wallet) {
      return $this->notFound("Wallet '{$reference}' not found in this project.");
    }

    return $this->success($this->formatWallet($wallet));
  }

  /**
   * POST /api/v1/wallets/fund
   * Credit a wallet.
   */
  public function fund(Request $request): JsonResponse
  {
    $validated = $request->validate([
      'wallet_reference' => ['required', 'string'],
      'amount'           => ['required', 'numeric', 'min:1'],
      'narration'        => ['nullable', 'string', 'max:255'],
      'idempotency_key'  => ['nullable', 'string', 'max:100'],
    ]);

    $project = $request->_api_project;

    $wallet = $project->wallets()
      ->where('reference', $validated['wallet_reference'])
      ->first();

    if (!$wallet) {
      return $this->notFound("Wallet '{$validated['wallet_reference']}' not found.");
    }

    try {
      $amountInMinorUnits = (int) round($validated['amount'] * 100);

      $tx = $this->transactions->fund(
        wallet: $wallet,
        amount: $amountInMinorUnits,
        narration: $validated['narration'] ?? 'API funding',
        provider: 'api',
        idempotencyKey: $validated['idempotency_key'] ?? null,
      );

      return $this->created([
        'transaction' => $this->formatTransaction($tx),
        'wallet'      => $this->formatWallet($wallet->fresh()),
      ], 'Wallet funded successfully.');
    } catch (\RuntimeException $e) {
      return $this->error($e->getMessage());
    }
  }

  /**
   * POST /api/v1/wallets/debit
   * Debit a wallet.
   */
  public function debit(Request $request): JsonResponse
  {
    $validated = $request->validate([
      'wallet_reference' => ['required', 'string'],
      'amount'           => ['required', 'numeric', 'min:1'],
      'narration'        => ['nullable', 'string', 'max:255'],
      'idempotency_key'  => ['nullable', 'string', 'max:100'],
    ]);

    $project = $request->_api_project;

    $wallet = $project->wallets()
      ->where('reference', $validated['wallet_reference'])
      ->first();

    if (!$wallet) {
      return $this->notFound("Wallet '{$validated['wallet_reference']}' not found.");
    }

    try {
      $amountInMinorUnits = (int) round($validated['amount'] * 100);

      $tx = $this->transactions->debit(
        wallet: $wallet,
        amount: $amountInMinorUnits,
        narration: $validated['narration'] ?? 'API debit',
        provider: 'api',
        idempotencyKey: $validated['idempotency_key'] ?? null,
      );

      return $this->created([
        'transaction' => $this->formatTransaction($tx),
        'wallet'      => $this->formatWallet($wallet->fresh()),
      ], 'Wallet debited successfully.');
    } catch (\RuntimeException $e) {
      return $this->error($e->getMessage());
    }
  }

  /**
   * POST /api/v1/wallets/transfer
   * Transfer between two wallets in the same project.
   */
  public function transfer(Request $request): JsonResponse
  {
    $validated = $request->validate([
      'from_wallet_reference' => ['required', 'string'],
      'to_wallet_reference'   => ['required', 'string', 'different:from_wallet_reference'],
      'amount'                => ['required', 'numeric', 'min:1'],
      'narration'             => ['nullable', 'string', 'max:255'],
      'idempotency_key'       => ['nullable', 'string', 'max:100'],
    ]);

    $project = $request->_api_project;

    $from = $project->wallets()
      ->where('reference', $validated['from_wallet_reference'])
      ->first();

    $to = $project->wallets()
      ->where('reference', $validated['to_wallet_reference'])
      ->first();

    if (!$from) {
      return $this->notFound("Source wallet '{$validated['from_wallet_reference']}' not found.");
    }

    if (!$to) {
      return $this->notFound("Destination wallet '{$validated['to_wallet_reference']}' not found.");
    }

    try {
      $amountInMinorUnits = (int) round($validated['amount'] * 100);

      $tx = $this->transactions->transfer(
        from: $from,
        to: $to,
        amount: $amountInMinorUnits,
        narration: $validated['narration'] ?? 'API transfer',
        idempotencyKey: $validated['idempotency_key'] ?? null,
      );

      return $this->created([
        'transaction'   => $this->formatTransaction($tx),
        'from_wallet'   => $this->formatWallet($from->fresh()),
        'to_wallet'     => $this->formatWallet($to->fresh()),
      ], 'Transfer completed successfully.');
    } catch (\RuntimeException $e) {
      return $this->error($e->getMessage());
    }
  }

  // ─── Formatters ───────────────────────────────────────────────────────────

  private function formatWallet(Wallet $wallet): array
  {
    return [
      'reference'         => $wallet->reference,
      'name'              => $wallet->name,
      'currency'          => $wallet->currency,
      'status'            => $wallet->status,
      'balance'           => $wallet->balance,
      'available_balance' => $wallet->available_balance,
      'ledger_balance'    => $wallet->ledger_balance,
      'balance_formatted' => $wallet->formattedBalance(),
      'created_at'        => $wallet->created_at->toIso8601String(),
    ];
  }

  private function formatTransaction(\App\Models\Transaction $tx): array
  {
    return [
      'reference'      => $tx->reference,
      'type'           => $tx->type->value,
      'status'         => $tx->status->value,
      'amount'         => $tx->amount,
      'currency'       => $tx->currency,
      'narration'      => $tx->narration,
      'balance_before' => $tx->balance_before,
      'balance_after'  => $tx->balance_after,
      'failure_reason' => $tx->failure_reason,
      'completed_at'   => $tx->completed_at?->toIso8601String(),
      'created_at'     => $tx->created_at->toIso8601String(),
    ];
  }
}
