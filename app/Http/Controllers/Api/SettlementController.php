<?php

namespace App\Http\Controllers\Api;

use App\Models\SettlementBatch;
use App\Models\Wallet;
use App\Services\SettlementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettlementController extends ApiController
{
  public function __construct(protected SettlementService $settlement) {}

  /**
   * GET /api/v1/settlements
   */
  public function index(Request $request): JsonResponse
  {
    $batches = SettlementBatch::where('project_id', $request->_api_project->id)
      ->with('wallet')
      ->latest()
      ->paginate(20);

    return $this->success([
      'settlements' => $batches->map(fn($b) => $this->formatBatch($b)),
      'pagination'  => [
        'total'        => $batches->total(),
        'per_page'     => $batches->perPage(),
        'current_page' => $batches->currentPage(),
        'last_page'    => $batches->lastPage(),
      ],
    ]);
  }

  /**
   * POST /api/v1/settlements
   */
  public function run(Request $request): JsonResponse
  {
    $validated = $request->validate([
      'wallet_reference' => ['required', 'string'],
      'notes'            => ['nullable', 'string', 'max:300'],
    ]);

    $wallet = $request->_api_project
      ->wallets()
      ->where('reference', $validated['wallet_reference'])
      ->first();

    if (!$wallet) {
      return $this->notFound("Wallet '{$validated['wallet_reference']}' not found.");
    }

    try {
      $batch = $this->settlement->run(
        $wallet,
        $request->_api_project,
        $validated['notes'] ?? null
      );

      return $this->created(
        $this->formatBatch($batch->load('transactions')),
        "Settlement {$batch->reference} completed."
      );
    } catch (\RuntimeException $e) {
      return $this->error($e->getMessage());
    }
  }

  /**
   * GET /api/v1/settlements/{reference}
   */
  public function show(Request $request, string $reference): JsonResponse
  {
    $batch = SettlementBatch::where('project_id', $request->_api_project->id)
      ->where('reference', $reference)
      ->with(['wallet', 'transactions.wallet'])
      ->first();

    if (!$batch) return $this->notFound("Settlement '{$reference}' not found.");

    return $this->success($this->formatBatch($batch, detailed: true));
  }

  // ─── Formatter ────────────────────────────────────────────────────────────

  private function formatBatch(SettlementBatch $batch, bool $detailed = false): array
  {
    $base = [
      'reference'         => $batch->reference,
      'status'            => $batch->status,
      'total_amount'      => $batch->total_amount,
      'currency'          => $batch->currency,
      'transaction_count' => $batch->transaction_count,
      'notes'             => $batch->notes,
      'wallet'            => $batch->wallet ? [
        'reference' => $batch->wallet->reference,
        'name'      => $batch->wallet->name,
      ] : null,
      'settled_at'  => $batch->settled_at?->toIso8601String(),
      'created_at'  => $batch->created_at->toIso8601String(),
    ];

    if ($detailed) {
      $base['transactions'] = $batch->transactions
        ->where('type', '!=', 'settlement')
        ->map(fn($tx) => [
          'reference' => $tx->reference,
          'type'      => $tx->type->value,
          'amount'    => $tx->amount,
          'currency'  => $tx->currency,
          'narration' => $tx->narration,
          'created_at' => $tx->created_at->toIso8601String(),
        ]);
    }

    return $base;
  }
}
