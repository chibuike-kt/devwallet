<?php

namespace App\Http\Controllers\Api\Flutterwave;

use App\Http\Controllers\Controller;
use App\Models\PaystackTransfer;
use App\Services\PaystackWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransferController extends Controller
{
  public function __construct(
    protected PaystackWebhookService $webhooks
  ) {}

  /**
   * POST /api/flutterwave/v3/transfers
   */
  public function store(Request $request): JsonResponse
  {
    $request->validate([
      'amount'          => ['required', 'numeric', 'min:1'],
      'currency'        => ['nullable', 'string'],
      'beneficiary_name' => ['required', 'string'],
      'account_number'  => ['required', 'string'],
      'bank_code'       => ['required', 'string'],
      'narration'       => ['nullable', 'string'],
      'reference'       => ['nullable', 'string'],
    ]);

    $transfer = PaystackTransfer::create([
      'project_id'               => $request->_api_project->id,
      'amount'                   => (int)round($request->amount * 100),
      'currency'                 => $request->currency ?? 'NGN',
      'narration'                => $request->narration,
      'reference'                => $request->reference,
      'recipient_name'           => $request->beneficiary_name,
      'recipient_account_number' => $request->account_number,
      'recipient_bank_code'      => $request->bank_code,
      'status'                   => 'pending',
    ]);

    $project      = $request->_api_project;
    $delayMs      = $project->transferDelayMs();
    $shouldFail   = $project->shouldSimulateFail();

    dispatch(function () use ($transfer, $delayMs, $shouldFail) {
      if ($delayMs > 0) {
        usleep($delayMs * 1000);
      }

      $newStatus = $shouldFail ? 'failed' : 'success';

      $transfer->update([
        'status'       => $newStatus,
        'completed_at' => $shouldFail ? null : now(),
      ]);

      $webhook = app(PaystackWebhookService::class);

      if ($shouldFail) {
        $webhook->fireTransferFailed($transfer->fresh());
      } else {
        $webhook->fireTransferSuccess($transfer->fresh());
      }
    })->afterResponse();

    return response()->json([
      'status'  => 'success',
      'message' => 'Transfer Queued Successfully',
      'data'    => $this->formatTransfer($transfer),
    ]);
  }

  /**
   * GET /api/flutterwave/v3/transfers/{id}
   */
  public function show(Request $request, string $id): JsonResponse
  {
    $transfer = PaystackTransfer::where('project_id', $request->_api_project->id)
      ->where(fn($q) => $q->where('id', $id)->orWhere('reference', $id))
      ->first();

    if (!$transfer) {
      return response()->json([
        'status'  => 'error',
        'message' => 'Transfer not found',
      ], 404);
    }

    return response()->json([
      'status'  => 'success',
      'message' => 'Transfer fetched',
      'data'    => $this->formatTransfer($transfer),
    ]);
  }

  /**
   * GET /api/flutterwave/v3/transfers
   */
  public function index(Request $request): JsonResponse
  {
    $transfers = PaystackTransfer::where('project_id', $request->_api_project->id)
      ->latest()
      ->paginate(20);

    return response()->json([
      'status'  => 'success',
      'message' => 'Transfers fetched',
      'data'    => $transfers->map(fn($t) => $this->formatTransfer($t)),
    ]);
  }

  private function formatTransfer(PaystackTransfer $transfer): array
  {
    return [
      'id'               => $transfer->id,
      'account_number'   => $transfer->recipient_account_number,
      'bank_code'        => $transfer->recipient_bank_code,
      'full_name'        => $transfer->recipient_name,
      'created_at'       => $transfer->created_at->toIso8601String(),
      'currency'         => $transfer->currency,
      'debit_currency'   => $transfer->currency,
      'amount'           => $transfer->amount / 100,
      'fee'              => 10.75,
      'status'           => $transfer->status,
      'reference'        => $transfer->reference,
      'meta'             => null,
      'narration'        => $transfer->narration,
      'complete_message' => $transfer->status === 'success' ? 'Successful' : 'Pending',
      'requires_approval' => 0,
      'is_approved'      => 1,
      'bank_name'        => $transfer->recipient_bank_name ?? 'ACCESS BANK NIGERIA',
    ];
  }
}
