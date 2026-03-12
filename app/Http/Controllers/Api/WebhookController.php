<?php

namespace App\Http\Controllers\Api;

use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends ApiController
{
  public function __construct(protected WebhookService $webhooks) {}

  /**
   * GET /api/v1/webhooks/endpoints
   */
  public function indexEndpoints(Request $request): JsonResponse
  {
    $endpoints = $request->_api_project
      ->webhookEndpoints()
      ->latest()
      ->get()
      ->map(fn(WebhookEndpoint $e) => $this->formatEndpoint($e));

    return $this->success($endpoints);
  }

  /**
   * POST /api/v1/webhooks/endpoints
   */
  public function storeEndpoint(Request $request): JsonResponse
  {
    $validated = $request->validate([
      'url'         => ['required', 'url', 'max:500'],
      'description' => ['nullable', 'string', 'max:200'],
      'events'      => ['nullable', 'array'],
      'events.*'    => ['string'],
    ]);

    $endpoint = $request->_api_project->webhookEndpoints()->create([
      'url'         => $validated['url'],
      'description' => $validated['description'] ?? null,
      'events'      => $validated['events'] ?? [],
      'status'      => 'active',
    ]);

    return $this->created(
      $this->formatEndpoint($endpoint),
      'Webhook endpoint registered.'
    );
  }

  /**
   * DELETE /api/v1/webhooks/endpoints/{id}
   */
  public function destroyEndpoint(Request $request, int $id): JsonResponse
  {
    $endpoint = $request->_api_project
      ->webhookEndpoints()
      ->find($id);

    if (!$endpoint) return $this->notFound("Endpoint #{$id} not found.");

    $endpoint->delete();

    return $this->success(null, 'Webhook endpoint removed.');
  }

  /**
   * GET /api/v1/webhooks/events
   */
  public function indexEvents(Request $request): JsonResponse
  {
    $events = WebhookEvent::where('project_id', $request->_api_project->id)
      ->with('deliveries')
      ->latest()
      ->paginate(20);

    return $this->success([
      'events' => $events->map(fn(WebhookEvent $e) => $this->formatEvent($e)),
      'pagination' => [
        'total'        => $events->total(),
        'per_page'     => $events->perPage(),
        'current_page' => $events->currentPage(),
        'last_page'    => $events->lastPage(),
      ],
    ]);
  }

  /**
   * GET /api/v1/webhooks/events/{id}
   */
  public function showEvent(Request $request, int $id): JsonResponse
  {
    $event = WebhookEvent::where('project_id', $request->_api_project->id)
      ->with(['deliveries.endpoint', 'transaction'])
      ->find($id);

    if (!$event) return $this->notFound("Webhook event #{$id} not found.");

    return $this->success($this->formatEvent($event, detailed: true));
  }

  /**
   * POST /api/v1/webhooks/deliveries/{id}/retry
   */
  public function retryDelivery(Request $request, int $id): JsonResponse
  {
    $delivery = WebhookDelivery::whereHas('event', function ($q) use ($request) {
      $q->where('project_id', $request->_api_project->id);
    })->find($id);

    if (!$delivery) return $this->notFound("Delivery #{$id} not found.");

    if ($delivery->isSuccess()) {
      return $this->error('Delivery already succeeded. Only failed deliveries can be retried.');
    }

    $newDelivery = $this->webhooks->retryDelivery($delivery);

    return $this->success([
      'delivery' => [
        'id'             => $newDelivery->id,
        'status'         => $newDelivery->status,
        'http_status'    => $newDelivery->http_status,
        'attempt_number' => $newDelivery->attempt_number,
        'duration_ms'    => $newDelivery->duration_ms,
        'attempted_at'   => $newDelivery->attempted_at?->toIso8601String(),
      ],
    ], $newDelivery->isSuccess() ? 'Retry succeeded.' : 'Retry failed again.');
  }

  // ─── Formatters ───────────────────────────────────────────────────────────

  private function formatEndpoint(WebhookEndpoint $endpoint): array
  {
    return [
      'id'          => $endpoint->id,
      'url'         => $endpoint->url,
      'description' => $endpoint->description,
      'events'      => $endpoint->events ?? [],
      'status'      => $endpoint->status,
      'created_at'  => $endpoint->created_at->toIso8601String(),
    ];
  }

  private function formatEvent(WebhookEvent $event, bool $detailed = false): array
  {
    $base = [
      'id'                   => $event->id,
      'event_type'           => $event->event_type,
      'transaction_reference' => $event->transaction?->reference,
      'delivery_count'       => $event->deliveries->count(),
      'success_count'        => $event->deliveries->where('status', 'success')->count(),
      'failed_count'         => $event->deliveries->where('status', 'failed')->count(),
      'created_at'           => $event->created_at->toIso8601String(),
    ];

    if ($detailed) {
      $base['payload']    = $event->payload;
      $base['deliveries'] = $event->deliveries->map(fn($d) => [
        'id'             => $d->id,
        'endpoint_url'   => $d->endpoint?->url,
        'status'         => $d->status,
        'http_status'    => $d->http_status,
        'duration_ms'    => $d->duration_ms,
        'attempt_number' => $d->attempt_number,
        'failure_reason' => $d->failure_reason,
        'attempted_at'   => $d->attempted_at?->toIso8601String(),
      ]);
    }

    return $base;
  }
}
