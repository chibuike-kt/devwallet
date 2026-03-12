<?php

namespace App\Http\Controllers\Api\Stripe;

use App\Http\Controllers\Controller;
use App\Models\PaystackTransfer;
use App\Services\PaystackWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransferController extends Controller
{
  public function __construct(protected PaystackWebhookService $webhooks) {}

  /**
   * POST /api/stripe/v1/transfers
   */
  public function store(Request $request): JsonResponse
  {
    $request->validate([
      'amount'      => ['required', 'integer', 'min:1'],
      'currency'    => ['required', 'string'],
      'destination' => ['required', 'string'],
      'description' => ['nullable', 'string'],
    ]);

    $transfer = PaystackTransfer::create([
      'project_id'               => $request->_api_project->id,
      'amount'                   => $request->amount,
      'currency'                 => strtoupper($request->currency),
      'narration'                => $request->description,
      'recipient_name'           => 'Stripe Destination',
      'recipient_account_number' => $request->destination,
      'recipient_bank_code'      => 'STRIPE',
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

    return response()->json($this->formatTransfer($transfer));
  }

  /**
   * GET /api/stripe/v1/transfers/{id}
   */
  public function show(Request $request, string $id): JsonResponse
  {
    $transfer = PaystackTransfer::where('project_id', $request->_api_project->id)
      ->where(fn($q) => $q->where('id', $id)->orWhere('transfer_code', $id))
      ->first();

    if (!$transfer) {
      return response()->json([
        'error' => ['code' => 'resource_missing', 'message' => 'No such transfer.'],
      ], 404);
    }

    return response()->json($this->formatTransfer($transfer));
  }

  /**
   * GET /api/stripe/v1/transfers
   */
  public function index(Request $request): JsonResponse
  {
    $transfers = PaystackTransfer::where('project_id', $request->_api_project->id)
      ->latest()->paginate(20);

    return response()->json([
      'object'   => 'list',
      'data'     => $transfers->map(fn($t) => $this->formatTransfer($t)),
      'has_more' => $transfers->hasMorePages(),
    ]);
  }

  private function formatTransfer(PaystackTransfer $transfer): array
  {
    return [
      'id'          => $transfer->transfer_code,
      'object'      => 'transfer',
      'amount'      => $transfer->amount,
      'currency'    => strtolower($transfer->currency),
      'destination' => $transfer->recipient_account_number,
      'description' => $transfer->narration,
      'livemode'    => false,
      'created'     => $transfer->created_at->timestamp,
      'metadata'    => [],
    ];
  }
}
