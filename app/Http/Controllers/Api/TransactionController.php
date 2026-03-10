<?php

namespace App\Http\Controllers\Api;

use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends ApiController
{
  /**
   * GET /api/v1/transactions
   * List transactions for the project with optional filters.
   */
  public function index(Request $request): JsonResponse
  {
    $project = $request->_api_project;

    $query = Transaction::where('project_id', $project->id)
      ->with('wallet')
      ->latest();

    // Optional filters
    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    if ($request->filled('type')) {
      $query->where('type', $request->type);
    }

    if ($request->filled('wallet_reference')) {
      $query->whereHas('wallet', function ($q) use ($request) {
        $q->where('reference', $request->wallet_reference);
      });
    }

    $perPage      = min((int) ($request->per_page ?? 20), 100);
    $transactions = $query->paginate($perPage);

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
   * Get a single transaction by reference.
   */
  public function show(Request $request, string $reference): JsonResponse
  {
    $project = $request->_api_project;

    $tx = Transaction::where('project_id', $project->id)
      ->where('reference', $reference)
      ->with('wallet')
      ->first();

    if (!$tx) {
      return $this->notFound("Transaction '{$reference}' not found.");
    }

    return $this->success($this->formatTransaction($tx, detailed: true));
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
      $base['idempotency_key']       = $tx->idempotency_key;
      $base['provider_reference']    = $tx->provider_reference;
      $base['related_wallet_id']     = $tx->related_wallet_id;
      $base['settlement_batch_id']   = $tx->settlement_batch_id;
    }

    return $base;
  }
}
