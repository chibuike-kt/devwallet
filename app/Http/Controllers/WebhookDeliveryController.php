<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\WebhookDelivery;
use App\Services\WebhookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WebhookDeliveryController extends Controller
{
    public function __construct(protected WebhookService $webhooks) {}

    public function retry(Request $request, Project $project, WebhookDelivery $delivery): RedirectResponse
    {
        $this->authorize('view', $project);

        $newDelivery = $this->webhooks->retryDelivery($delivery);

        $status = $newDelivery->isSuccess() ? 'Retry succeeded.' : 'Retry failed again.';

        activity()
            ->causedBy($request->user())
            ->performedOn($project)
            ->log("Retried webhook delivery #{$delivery->id} — {$status}");

        return redirect()
            ->route('projects.webhooks.index', $project)
            ->with('success', "Retry attempt completed. {$status}");
    }
}
