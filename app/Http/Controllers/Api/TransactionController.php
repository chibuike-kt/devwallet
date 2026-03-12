<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends ApiController
{
  public function __construct(protected TransactionService $transactions) {}

  /**
   * GET /api/v1/transactions
   */
  public function index(Request $request): JsonResponse
  {
    $query = Transaction::where('project_id', $request->_api_project->id)
      ->with('wallet')
      ->latest();

    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    if ($request->filled('type')) {
      $query->where('type', $request->type);
    }

    if ($request->filled('wallet_reference')) {
      $query->whereHas(
        'wallet',
        fn($q) =>
        $q->where('reference', $request->wallet_reference)
      );
    }

    if ($request->filled('date_from')) {
      $query->whereDate('created_at', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
      $query->whereDate('created_at', '<=', $request->date_to);
    }

    $transactions = $query->paginate(min((int)($request->per_page ?? 20), 100));

    return $this->success([
      'transactions' => $transactions->map(
        fn(Transaction $tx) => $this->formatTransaction($tx)
      ),
      'pagination' => [
        'total'        => $transactions->total(),
        'per_page'     => $transactions->perPage(),
        'current_page' => $transactions->currentPage(),
        'last_page'    => $transactions->lastPage(),
      ],
    ]);
  }

  /**
   * GET /api/v1/transactions/{reference}
   */
  public function show(Request $request, string $reference): JsonResponse
  {
    $tx = Transaction::where('project_id', $request->_api_project->id)
      ->where('reference', $reference)
      ->with(['wallet', 'settlementBatch'])
      ->first();

    if (!$tx) return $this->notFound("Transaction '{$reference}' not found.");

    return $this->success($this->formatTransaction($tx, detailed: true));
  }

  /**
   * POST /api/v1/transactions/{reference}/reverse
   */
  public function reverse(Request $request, string $reference): JsonResponse
  {
    $tx = Transaction::where('project_id', $request->_api_project->id)
      ->where('reference', $reference)
      ->with('wallet')
      ->first();

    if (!$tx) return $this->notFound("Transaction '{$reference}' not found.");

    $request->validate([
      'reason' => ['nullable', 'string', 'max:255'],
    ]);

    try {
      $reversal = $this->transactions->reverse(
        $tx,
        $request->reason ?? 'API reversal'
      );

      return $this->created([
        'reversal'     => $this->formatTransaction($reversal),
        'original'     => $this->formatTransaction($tx->fresh()),
        'wallet'       => [
          'reference'         => $tx->wallet->reference,
          'balance'           => $tx->wallet->fresh()->balance,
          'balance_formatted' => $tx->wallet->fresh()->formattedBalance(),
        ],
      ], 'Transaction reversed successfully.');
    } catch (\RuntimeException $e) {
      return $this->error($e->getMessage());
    }
  }

  // ─── Formatter ────────────────────────────────────────────────────────────

  private function formatTransaction(Transaction $tx, bool $detailed = false): array
  {
    $base = [
      'reference'      => $tx->reference,
      'type'           => $tx->type->value,
      'type_label'     => $tx->type->label(),
      'status'         => $tx->status->value,
      'status_label'   => $tx->status->label(),
      'amount'         => $tx->amount,
      'currency'       => $tx->currency,
      'narration'      => $tx->narration,
      'balance_before' => $tx->balance_before,
      'balance_after'  => $tx->balance_after,
      'failure_reason' => $tx->failure_reason,
      'provider'       => $tx->provider,
      'completed_at'   => $tx->completed_at?->toIso8601String(),
      'created_at'     => $tx->created_at->toIso8601String(),
      'wallet'         => $tx->wallet ? [
        'reference' => $tx->wallet->reference,
        'name'      => $tx->wallet->name,
        'currency'  => $tx->wallet->currency,
      ] : null,
    ];

    if ($detailed) {
      $base['idempotency_key']     = $tx->idempotency_key;
      $base['provider_reference']  = $tx->provider_reference;
      $base['settlement_batch']    = $tx->settlementBatch
        ? ['reference' => $tx->settlementBatch->reference]
        : null;
    }

    return $base;
  }
}
