<?php

namespace App\Http\Controllers\Api;

use App\Models\WebhookEndpoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookEndpointController extends ApiController
{
  /**
   * GET /api/v1/webhooks
   * List registered webhook endpoints for the project.
   */
  public function index(Request $request): JsonResponse
  {
    $project   = $request->_api_project;
    $endpoints = $project->webhookEndpoints()->latest()->get();

    return $this->success(
      $endpoints->map(fn(WebhookEndpoint $e) => $this->formatEndpoint($e))
    );
  }

  /**
   * POST /api/v1/webhooks
   * Register a new webhook endpoint.
   */
  public function store(Request $request): JsonResponse
  {
    $validated = $request->validate([
      'url'         => ['required', 'url', 'max:500'],
      'description' => ['nullable', 'string', 'max:200'],
      'events'      => ['nullable', 'array'],
      'events.*'    => ['string'],
    ]);

    $project  = $request->_api_project;

    $endpoint = $project->webhookEndpoints()->create([
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
   * DELETE /api/v1/webhooks/{id}
   * Remove a webhook endpoint.
   */
  public function destroy(Request $request, int $id): JsonResponse
  {
    $project  = $request->_api_project;

    $endpoint = $project->webhookEndpoints()->find($id);

    if (!$endpoint) {
      return $this->notFound("Webhook endpoint #{$id} not found.");
    }

    $endpoint->delete();

    return $this->success(null, 'Webhook endpoint removed.');
  }

  // ─── Formatter ────────────────────────────────────────────────────────────

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
}
