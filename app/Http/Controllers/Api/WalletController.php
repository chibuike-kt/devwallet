<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
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
   */
  public function index(Request $request): JsonResponse
  {
    $wallets = $request->_api_project
      ->wallets()
      ->latest()
      ->get()
      ->map(fn(Wallet $w) => $this->formatWallet($w));

    return $this->success($wallets);
  }

  /**
   * POST /api/v1/wallets
   */
  public function store(Request $request): JsonResponse
  {
    $validated = $request->validate([
      'name'     => ['required', 'string', 'max:100'],
      'currency' => ['required', 'in:NGN,USD,KES,GHS'],
    ]);

    $wallet = $request->_api_project->wallets()->create([
      'name'     => $validated['name'],
      'currency' => $validated['currency'],
      'status'   => 'active',
    ]);

    return $this->created($this->formatWallet($wallet), 'Wallet created.');
  }

  /**
   * GET /api/v1/wallets/{reference}
   */
  public function show(Request $request, string $reference): JsonResponse
  {
    $wallet = $this->resolveWallet($request, $reference);
    if (!$wallet) return $this->notFound("Wallet '{$reference}' not found.");

    return $this->success($this->formatWallet($wallet));
  }

  /**
   * POST /api/v1/wallets/{reference}/fund
   */
  public function fund(Request $request, string $reference): JsonResponse
  {
    $wallet = $this->resolveWallet($request, $reference);
    if (!$wallet) return $this->notFound("Wallet '{$reference}' not found.");

    $validated = $request->validate([
      'amount'          => ['required', 'numeric', 'min:1'],
      'narration'       => ['nullable', 'string', 'max:255'],
      'idempotency_key' => ['nullable', 'string', 'max:100'],
    ]);

    try {
      $tx = $this->transactions->fund(
        wallet: $wallet,
        amount: $this->toMinorUnits($validated['amount']),
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
   * POST /api/v1/wallets/{reference}/debit
   */
  public function debit(Request $request, string $reference): JsonResponse
  {
    $wallet = $this->resolveWallet($request, $reference);
    if (!$wallet) return $this->notFound("Wallet '{$reference}' not found.");

    $validated = $request->validate([
      'amount'          => ['required', 'numeric', 'min:1'],
      'narration'       => ['nullable', 'string', 'max:255'],
      'idempotency_key' => ['nullable', 'string', 'max:100'],
    ]);

    try {
      $tx = $this->transactions->debit(
        wallet: $wallet,
        amount: $this->toMinorUnits($validated['amount']),
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

    $from = $this->resolveWallet($request, $validated['from_wallet_reference']);
    $to   = $this->resolveWallet($request, $validated['to_wallet_reference']);

    if (!$from) return $this->notFound("Source wallet '{$validated['from_wallet_reference']}' not found.");
    if (!$to)   return $this->notFound("Destination wallet '{$validated['to_wallet_reference']}' not found.");

    try {
      $tx = $this->transactions->transfer(
        from: $from,
        to: $to,
        amount: $this->toMinorUnits($validated['amount']),
        narration: $validated['narration'] ?? 'API transfer',
        idempotencyKey: $validated['idempotency_key'] ?? null,
      );

      return $this->created([
        'transaction' => $this->formatTransaction($tx),
        'from_wallet' => $this->formatWallet($from->fresh()),
        'to_wallet'   => $this->formatWallet($to->fresh()),
      ], 'Transfer completed successfully.');
    } catch (\RuntimeException $e) {
      return $this->error($e->getMessage());
    }
  }

  /**
   * POST /api/v1/wallets/{reference}/freeze
   */
  public function freeze(Request $request, string $reference): JsonResponse
  {
    $wallet = $this->resolveWallet($request, $reference);
    if (!$wallet) return $this->notFound("Wallet '{$reference}' not found.");

    if ($wallet->isFrozen()) {
      return $this->error('Wallet is already frozen.');
    }

    $this->wallets->freeze($wallet);

    return $this->success($this->formatWallet($wallet->fresh()), 'Wallet frozen.');
  }

  /**
   * POST /api/v1/wallets/{reference}/unfreeze
   */
  public function unfreeze(Request $request, string $reference): JsonResponse
  {
    $wallet = $this->resolveWallet($request, $reference);
    if (!$wallet) return $this->notFound("Wallet '{$reference}' not found.");

    if (!$wallet->isFrozen()) {
      return $this->error('Wallet is not frozen.');
    }

    $this->wallets->unfreeze($wallet);

    return $this->success($this->formatWallet($wallet->fresh()), 'Wallet unfrozen.');
  }

  /**
   * GET /api/v1/wallets/{reference}/ledger
   */
  public function ledger(Request $request, string $reference): JsonResponse
  {
    $wallet = $this->resolveWallet($request, $reference);
    if (!$wallet) return $this->notFound("Wallet '{$reference}' not found.");

    $entries = $wallet->ledgerEntries()
      ->with('transaction')
      ->latest()
      ->paginate(50);

    return $this->success([
      'wallet'  => $this->formatWallet($wallet),
      'entries' => $entries->map(fn($entry) => [
        'id'              => $entry->id,
        'direction'       => $entry->direction->value,
        'amount'          => $entry->amount,
        'currency'        => $entry->currency,
        'running_balance' => $entry->running_balance,
        'narration'       => $entry->narration,
        'transaction_reference' => $entry->transaction?->reference,
        'created_at'      => $entry->created_at->toIso8601String(),
      ]),
      'pagination' => [
        'total'        => $entries->total(),
        'per_page'     => $entries->perPage(),
        'current_page' => $entries->currentPage(),
        'last_page'    => $entries->lastPage(),
      ],
    ]);
  }

  /**
   * GET /api/v1/wallets/{reference}/transactions
   */
  public function transactions(Request $request, string $reference): JsonResponse
  {
    $wallet = $this->resolveWallet($request, $reference);
    if (!$wallet) return $this->notFound("Wallet '{$reference}' not found.");

    $transactions = $wallet->transactions()
      ->latest()
      ->paginate(20);

    return $this->success([
      'wallet'       => $this->formatWallet($wallet),
      'transactions' => $transactions->map(
        fn($tx) => $this->formatTransaction($tx)
      ),
      'pagination' => [
        'total'        => $transactions->total(),
        'per_page'     => $transactions->perPage(),
        'current_page' => $transactions->currentPage(),
        'last_page'    => $transactions->lastPage(),
      ],
    ]);
  }

  // ─── Private helpers ──────────────────────────────────────────────────────

  private function resolveWallet(Request $request, string $reference): ?Wallet
  {
    return $request->_api_project
      ->wallets()
      ->where('reference', $reference)
      ->first();
  }

  private function toMinorUnits(float $amount): int
  {
    return (int) round($amount * 100);
  }

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
