<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\Http;

class WebhookService
{
  /**
   * Build and store a webhook event payload from a transaction.
   * Called after any significant transaction state change.
   */
  public function createEvent(Transaction $transaction): WebhookEvent
  {
    $eventType = $this->resolveEventType($transaction);

    $payload = [
      'event'     => $eventType,
      'timestamp' => now()->toIso8601String(),
      'data'      => [
        'id'              => $transaction->id,
        'reference'       => $transaction->reference,
        'type'            => $transaction->type->value,
        'status'          => $transaction->status->value,
        'amount'          => $transaction->amount,
        'currency'        => $transaction->currency,
        'narration'       => $transaction->narration,
        'balance_before'  => $transaction->balance_before,
        'balance_after'   => $transaction->balance_after,
        'wallet'          => [
          'id'        => $transaction->wallet->id,
          'name'      => $transaction->wallet->name,
          'reference' => $transaction->wallet->reference,
          'currency'  => $transaction->wallet->currency,
        ],
        'provider'        => $transaction->provider,
        'failure_reason'  => $transaction->failure_reason,
        'completed_at'    => $transaction->completed_at?->toIso8601String(),
        'created_at'      => $transaction->created_at->toIso8601String(),
      ],
    ];

    return WebhookEvent::create([
      'project_id'     => $transaction->project_id,
      'transaction_id' => $transaction->id,
      'event_type'     => $eventType,
      'payload'        => $payload,
    ]);
  }

  /**
   * Deliver a webhook event to all active endpoints in the project.
   * Simulates HTTP delivery — uses real HTTP if URL is reachable,
   * otherwise simulates a response.
   */
  public function deliverEvent(WebhookEvent $event, bool $simulateFailure = false): void
  {
    $endpoints = WebhookEndpoint::where('project_id', $event->project_id)
      ->where('status', 'active')
      ->get();

    foreach ($endpoints as $endpoint) {
      $this->attemptDelivery($event, $endpoint, 1, $simulateFailure);
    }
  }

  /**
   * Retry a previously failed delivery.
   */
  public function retryDelivery(WebhookDelivery $delivery): WebhookDelivery
  {
    $nextAttempt = $delivery->attempt_number + 1;

    return $this->attemptDelivery(
      $delivery->event,
      $delivery->endpoint,
      $nextAttempt,
      false
    );
  }

  /**
   * Simulate a duplicate event delivery (same payload, new delivery record).
   */
  public function simulateDuplicate(WebhookEvent $event): void
  {
    $endpoints = WebhookEndpoint::where('project_id', $event->project_id)
      ->where('status', 'active')
      ->get();

    foreach ($endpoints as $endpoint) {
      $this->attemptDelivery($event, $endpoint, 99, false);
    }
  }

  // ─── Internal ─────────────────────────────────────────────────────────────

  private function attemptDelivery(
    WebhookEvent    $event,
    WebhookEndpoint $endpoint,
    int             $attemptNumber,
    bool            $simulateFailure
  ): WebhookDelivery {
    $startTime = microtime(true);
    $payload   = json_encode($event->payload);
    $signature = $this->sign($payload, $endpoint->secret);

    // Create delivery record immediately as pending
    $delivery = WebhookDelivery::create([
      'webhook_event_id'    => $event->id,
      'webhook_endpoint_id' => $endpoint->id,
      'status'              => 'pending',
      'attempt_number'      => $attemptNumber,
      'attempted_at'        => now(),
    ]);

    if ($simulateFailure) {
      $duration = rand(200, 800);
      $delivery->update([
        'status'         => 'failed',
        'http_status'    => 503,
        'response_body'  => json_encode(['error' => 'Service Unavailable']),
        'duration_ms'    => $duration,
        'failure_reason' => 'Simulated delivery failure: endpoint returned 503.',
      ]);

      return $delivery->fresh();
    }

    // Attempt real HTTP delivery if URL looks real
    // For localhost/test URLs, simulate a successful response
    if ($this->isSimulatedUrl($endpoint->url)) {
      $duration = rand(80, 400);
      $delivery->update([
        'status'        => 'success',
        'http_status'   => 200,
        'response_body' => json_encode(['received' => true, 'simulated' => true]),
        'duration_ms'   => $duration,
      ]);

      return $delivery->fresh();
    }

    // Real HTTP attempt
    try {
      $response = Http::timeout(10)
        ->withHeaders([
          'Content-Type'           => 'application/json',
          'X-DevWallet-Signature'  => 'sha256=' . $signature,
          'X-DevWallet-Event'      => $event->event_type,
          'X-DevWallet-Delivery'   => (string) $delivery->id,
        ])
        ->post($endpoint->url, $event->payload);

      $duration = (int) ((microtime(true) - $startTime) * 1000);

      $delivery->update([
        'status'       => $response->successful() ? 'success' : 'failed',
        'http_status'  => $response->status(),
        'response_body' => substr($response->body(), 0, 1000),
        'duration_ms'  => $duration,
        'failure_reason' => $response->successful()
          ? null
          : "HTTP {$response->status()} received from endpoint.",
      ]);
    } catch (\Exception $e) {
      $duration = (int) ((microtime(true) - $startTime) * 1000);

      $delivery->update([
        'status'         => 'failed',
        'http_status'    => null,
        'response_body'  => null,
        'duration_ms'    => $duration,
        'failure_reason' => 'Connection error: ' . $e->getMessage(),
      ]);
    }

    return $delivery->fresh();
  }

  private function sign(string $payload, string $secret): string
  {
    return hash_hmac('sha256', $payload, $secret);
  }

  private function isSimulatedUrl(string $url): bool
  {
    return str_contains($url, 'localhost')
      || str_contains($url, '127.0.0.1')
      || str_contains($url, 'example.com')
      || str_contains($url, 'devwallet.dev')
      || str_contains($url, 'webhook.site') === false && str_contains($url, 'test');
  }

  private function resolveEventType(Transaction $transaction): string
  {
    return match (true) {
      $transaction->type->value === 'wallet_funding' && $transaction->status->value === 'success'
      => 'wallet.funded',
      $transaction->type->value === 'wallet_debit' && $transaction->status->value === 'success'
      => 'wallet.debited',
      $transaction->type->value === 'wallet_debit' && $transaction->status->value === 'failed'
      => 'wallet.debit.failed',
      $transaction->type->value === 'wallet_transfer' && $transaction->status->value === 'success'
      => 'transfer.success',
      $transaction->type->value === 'wallet_transfer' && $transaction->status->value === 'failed'
      => 'transfer.failed',
      $transaction->type->value === 'bank_transfer' && $transaction->status->value === 'pending'
      => 'transfer.pending',
      $transaction->type->value === 'bank_transfer' && $transaction->status->value === 'success'
      => 'transfer.success',
      $transaction->type->value === 'bank_transfer' && $transaction->status->value === 'failed'
      => 'transfer.failed',
      $transaction->type->value === 'reversal'
      => 'transaction.reversed',
      default => 'transaction.' . $transaction->status->value,
    };
  }
}
