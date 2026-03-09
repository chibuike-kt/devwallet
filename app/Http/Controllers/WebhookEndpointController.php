<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWebhookEndpointRequest;
use App\Models\Project;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use App\Services\WebhookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebhookEndpointController extends Controller
{
    public function __construct(protected WebhookService $webhooks) {}

    public function index(Request $request, Project $project): View
    {
        $this->authorize('view', $project);

        $endpoints = $project->webhookEndpoints()->latest()->get();

        $recentEvents = WebhookEvent::where('project_id', $project->id)
            ->with(['transaction', 'deliveries'])
            ->latest()
            ->take(10)
            ->get();

        return view('webhooks.index', compact('project', 'endpoints', 'recentEvents'));
    }

    public function create(Request $request, Project $project): View
    {
        $this->authorize('view', $project);

        return view('webhooks.create', compact('project'));
    }

    public function store(StoreWebhookEndpointRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('view', $project);

        $endpoint = $project->webhookEndpoints()->create([
            'url'         => $request->url,
            'description' => $request->description,
            'events'      => $request->events ?? [],
        ]);

        activity()
            ->causedBy($request->user())
            ->performedOn($project)
            ->log("Created webhook endpoint: {$endpoint->url}");

        return redirect()
            ->route('projects.webhooks.index', $project)
            ->with('success', 'Webhook endpoint registered successfully.');
    }

    public function show(Request $request, Project $project, WebhookEndpoint $webhook): View
    {
        $this->authorize('view', $project);

        $deliveries = $webhook->deliveries()
            ->with('event')
            ->latest()
            ->paginate(20);

        return view('webhooks.show', compact('project', 'webhook', 'deliveries'));
    }

    public function destroy(Request $request, Project $project, WebhookEndpoint $webhook): RedirectResponse
    {
        $this->authorize('view', $project);

        $webhook->delete();

        return redirect()
            ->route('projects.webhooks.index', $project)
            ->with('success', 'Webhook endpoint removed.');
    }

    /**
     * Manually dispatch a webhook event from a transaction.
     */
    public function dispatch(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('view', $project);

        $request->validate([
            'transaction_id'   => ['required', 'exists:transactions,id'],
            'simulate_failure' => ['nullable', 'boolean'],
        ]);

        $transaction = \App\Models\Transaction::where('project_id', $project->id)
            ->findOrFail($request->transaction_id);

        $transaction->load('wallet');

        $event = $this->webhooks->createEvent($transaction);
        $this->webhooks->deliverEvent($event, (bool) $request->simulate_failure);

        activity()
            ->causedBy($request->user())
            ->performedOn($project)
            ->log("Dispatched webhook event: {$event->event_type} for {$transaction->reference}");

        return redirect()
            ->route('projects.webhooks.index', $project)
            ->with('success', "Webhook event \"{$event->event_type}\" dispatched to all active endpoints.");
    }

    /**
     * Simulate a duplicate webhook delivery.
     */
    public function duplicate(Request $request, Project $project, WebhookEvent $event): RedirectResponse
    {
        $this->authorize('view', $project);

        $this->webhooks->simulateDuplicate($event);

        return redirect()
            ->route('projects.webhooks.index', $project)
            ->with('success', "Duplicate webhook event dispatched for \"{$event->event_type}\".");
    }
}
