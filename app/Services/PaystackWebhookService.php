<?php

namespace App\Services;

use App\Models\PaystackTransaction;
use App\Models\PaystackTransfer;
use App\Models\Project;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Http;

class PaystackWebhookService
{
  public function __construct(
    protected PaystackResponseService $response
  ) {}

  public function fireChargeSuccess(PaystackTransaction $tx): void
  {
    $payload = $this->response->chargeSuccessPayload($tx);
    $this->dispatch($tx->project, $payload);
  }

  public function fireTransferSuccess(PaystackTransfer $transfer): void
  {
    $payload = $this->response->transferSuccessPayload($transfer);
    $this->dispatch($transfer->project, $payload);
  }

  public function fireTransferFailed(PaystackTransfer $transfer): void
  {
    $payload = $this->response->transferFailedPayload($transfer);
    $this->dispatch($transfer->project, $payload);
  }

  private function dispatch(Project $project, array $payload): void
  {
    $endpoints = WebhookEndpoint::where('project_id', $project->id)
      ->where('status', 'active')
      ->get();

    $body      = json_encode($payload);

    foreach ($endpoints as $endpoint) {
      $signature = hash_hmac('sha512', $body, $endpoint->secret);

      // Create delivery record
      $delivery = WebhookDelivery::create([
        'webhook_event_id'    => $this->storeEvent($project, $payload),
        'webhook_endpoint_id' => $endpoint->id,
        'status'              => 'pending',
        'attempt_number'      => 1,
        'attempted_at'        => now(),
      ]);

      $start = microtime(true);

      try {
        if ($this->isSimulatedUrl($endpoint->url)) {
          $duration = rand(80, 300);
          $delivery->update([
            'status'       => 'success',
            'http_status'  => 200,
            'response_body' => json_encode(['received' => true]),
            'duration_ms'  => $duration,
          ]);
          continue;
        }

        $res = Http::timeout(10)
          ->withHeaders([
            'Content-Type'        => 'application/json',
            'x-paystack-signature' => $signature,
          ])
          ->post($endpoint->url, $payload);

        $duration = (int)((microtime(true) - $start) * 1000);

        $delivery->update([
          'status'        => $res->successful() ? 'success' : 'failed',
          'http_status'   => $res->status(),
          'response_body' => substr($res->body(), 0, 1000),
          'duration_ms'   => $duration,
          'failure_reason' => $res->successful()
            ? null
            : "HTTP {$res->status()} from endpoint.",
        ]);
      } catch (\Exception $e) {
        $delivery->update([
          'status'         => 'failed',
          'duration_ms'    => (int)((microtime(true) - $start) * 1000),
          'failure_reason' => $e->getMessage(),
        ]);
      }
    }
  }

  private function storeEvent(Project $project, array $payload): int
  {
    $event = \App\Models\WebhookEvent::create([
      'project_id' => $project->id,
      'event_type' => $payload['event'],
      'payload'    => $payload,
    ]);

    return $event->id;
  }

  private function isSimulatedUrl(string $url): bool
  {
    return str_contains($url, 'localhost')
      || str_contains($url, '127.0.0.1')
      || str_contains($url, 'example.com');
  }
}
