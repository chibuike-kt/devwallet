<?php

namespace App\Http\Controllers\Api\Stripe;

use App\Http\Controllers\Controller;
use App\Models\PaystackCustomer;
use App\Models\PaystackTransaction;
use App\Services\PaystackWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentIntentController extends Controller
{
  public function __construct(
    protected PaystackWebhookService $webhooks
  ) {}

  /**
   * POST /api/stripe/v1/payment_intents
   */
  public function store(Request $request): JsonResponse
  {
    $request->validate([
      'amount'                  => ['required', 'integer', 'min:1'],
      'currency'                => ['required', 'string'],
      'payment_method_types'    => ['nullable', 'array'],
      'receipt_email'           => ['nullable', 'email'],
      'description'             => ['nullable', 'string'],
      'metadata'                => ['nullable', 'array'],
    ]);

    $project  = $request->_api_project;
    $email    = $request->receipt_email;

    $customer = null;
    if ($email) {
      $customer = PaystackCustomer::firstOrCreate(
        ['project_id' => $project->id, 'email' => $email],
        ['customer_code' => 'CUS_' . strtolower(Str::random(12))]
      );
    }

    $tx = PaystackTransaction::create([
      'project_id'           => $project->id,
      'paystack_customer_id' => $customer?->id,
      'reference'            => 'pi_' . strtolower(Str::random(24)),
      'status'               => 'initialized',
      'amount'               => $request->amount,
      'currency'             => strtoupper($request->currency),
      'channel'              => 'card',
      'metadata'             => $request->metadata,
    ]);

    return response()->json($this->formatIntent($tx, 'requires_payment_method'));
  }

  /**
   * GET /api/stripe/v1/payment_intents/{id}
   */
  public function show(Request $request, string $id): JsonResponse
  {
    $tx = $this->resolveIntent($request, $id);
    if (!$tx) return $this->notFound();

    return response()->json($this->formatIntent($tx));
  }

  /**
   * POST /api/stripe/v1/payment_intents/{id}/confirm
   */
  public function confirm(Request $request, string $id): JsonResponse
  {
    $tx = $this->resolveIntent($request, $id);
    if (!$tx) return $this->notFound();

    if ($tx->status !== 'initialized') {
      return response()->json(
        $this->formatIntent($tx)
      );
    }

    $shouldFail = $tx->force_fail || $request->_api_project->shouldSimulateFail();

    $tx->update([
      'status'             => $shouldFail ? 'failed' : 'success',
      'gateway_response'   => $shouldFail ? 'Declined' : 'Approved',
      'authorization_code' => 'AUTH_' . strtolower(Str::random(8)),
      'card_type'          => 'visa',
      'last4'              => (string) rand(1000, 9999),
      'exp_month'          => '12',
      'exp_year'           => '2030',
      'bank'               => 'TEST BANK',
      'paid_at'            => $shouldFail ? null : now(),
    ]);

    $tx->refresh();

    if (!$shouldFail) {
      $this->webhooks->fireChargeSuccess($tx);
    }

    return response()->json($this->formatIntent($tx));
  }

  /**
   * POST /api/stripe/v1/payment_intents/{id}/cancel
   */
  public function cancel(Request $request, string $id): JsonResponse
  {
    $tx = $this->resolveIntent($request, $id);
    if (!$tx) return $this->notFound();

    $tx->update(['status' => 'abandoned']);

    return response()->json($this->formatIntent($tx, 'canceled'));
  }

  /**
   * GET /api/stripe/v1/payment_intents
   */
  public function index(Request $request): JsonResponse
  {
    $intents = PaystackTransaction::where('project_id', $request->_api_project->id)
      ->latest()
      ->paginate(20);

    return response()->json([
      'object'   => 'list',
      'data'     => $intents->map(fn($tx) => $this->formatIntent($tx)),
      'has_more' => $intents->hasMorePages(),
      'url'      => '/v1/payment_intents',
    ]);
  }

  // ─── Helpers ──────────────────────────────────────────────────────────────

  private function resolveIntent(Request $request, string $id): ?PaystackTransaction
  {
    return PaystackTransaction::where('project_id', $request->_api_project->id)
      ->where('reference', $id)
      ->first();
  }

  private function notFound(): JsonResponse
  {
    return response()->json([
      'error' => [
        'code'    => 'resource_missing',
        'message' => 'No such payment_intent.',
        'type'    => 'invalid_request_error',
      ],
    ], 404);
  }

  private function formatIntent(PaystackTransaction $tx, ?string $overrideStatus = null): array
  {
    $stripeStatus = $overrideStatus ?? match ($tx->status) {
      'success'     => 'succeeded',
      'failed'      => 'requires_payment_method',
      'abandoned'   => 'canceled',
      default       => 'requires_confirmation',
    };

    return [
      'id'                    => $tx->reference,
      'object'                => 'payment_intent',
      'amount'                => $tx->amount,
      'amount_capturable'     => 0,
      'amount_received'       => $tx->status === 'success' ? $tx->amount : 0,
      'currency'              => strtolower($tx->currency),
      'status'                => $stripeStatus,
      'client_secret'         => $tx->reference . '_secret_' . Str::random(16),
      'created'               => $tx->created_at->timestamp,
      'description'           => $tx->narration,
      'livemode'              => false,
      'metadata'              => $tx->metadata ?? [],
      'payment_method_types'  => ['card'],
      'receipt_email'         => $tx->customer?->email,
      'charges'               => [
        'object'   => 'list',
        'data'     => $tx->status === 'success' ? [[
          'id'                => 'ch_' . strtolower(Str::random(24)),
          'object'            => 'charge',
          'amount'            => $tx->amount,
          'currency'          => strtolower($tx->currency),
          'paid'              => true,
          'status'            => 'succeeded',
          'payment_method_details' => [
            'card' => [
              'brand'    => $tx->card_type ?? 'visa',
              'last4'    => $tx->last4    ?? '4242',
              'exp_month' => (int)($tx->exp_month ?? 12),
              'exp_year' => (int)($tx->exp_year  ?? 2030),
            ],
            'type' => 'card',
          ],
        ]] : [],
        'has_more'  => false,
        'total_count' => $tx->status === 'success' ? 1 : 0,
      ],
    ];
  }
}
