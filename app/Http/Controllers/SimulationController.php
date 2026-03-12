<?php

namespace App\Http\Controllers;

use App\Models\PaystackTransaction;
use App\Models\PaystackTransfer;
use App\Models\Project;
use App\Models\WebhookEndpoint;
use App\Services\PaystackWebhookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SimulationController extends Controller
{
    public function __construct(
        protected PaystackWebhookService $webhooks
    ) {}

    /**
     * GET /projects/{project}/simulation
     */
    public function index(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $recentTransactions = PaystackTransaction::where('project_id', $project->id)
            ->where('status', 'success')
            ->with('customer')
            ->latest()
            ->take(10)
            ->get();

        $recentTransfers = PaystackTransfer::where('project_id', $project->id)
            ->latest()
            ->take(10)
            ->get();

        $endpoints = WebhookEndpoint::where('project_id', $project->id)
            ->where('status', 'active')
            ->get();

        return view('simulation.index', compact(
            'project',
            'recentTransactions',
            'recentTransfers',
            'endpoints',
        ));
    }

    /**
     * POST /projects/{project}/simulation/settings
     * Update failure rate, force fail toggle, transfer delay.
     */
    public function updateSettings(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'sim_failure_rate'    => ['required', 'integer', 'min:0', 'max:100'],
            'sim_force_next_fail' => ['boolean'],
            'sim_transfer_delay'  => ['required', 'in:instant,slow,timeout'],
        ]);

        $project->update([
            'sim_failure_rate'    => $validated['sim_failure_rate'],
            'sim_force_next_fail' => $request->boolean('sim_force_next_fail'),
            'sim_transfer_delay'  => $validated['sim_transfer_delay'],
        ]);

        activity()
            ->causedBy($request->user())
            ->performedOn($project)
            ->log("Updated simulation settings: failure rate {$project->sim_failure_rate}%, " .
                "transfer delay {$project->sim_transfer_delay}");

        return back()->with('success', 'Simulation settings updated.');
    }

    /**
     * POST /projects/{project}/simulation/reset
     * Reset all simulation settings to defaults.
     */
    public function reset(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $project->update([
            'sim_failure_rate'    => 0,
            'sim_force_next_fail' => false,
            'sim_transfer_delay'  => 'instant',
        ]);

        return back()->with('success', 'Simulation reset to defaults.');
    }

    /**
     * POST /projects/{project}/simulation/webhook
     * Manually fire a webhook event using a real recent transaction or transfer.
     */
    public function fireWebhook(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'event_type'     => ['required', 'in:charge.success,transfer.success,transfer.failed'],
            'transaction_id' => ['nullable', 'exists:paystack_transactions,id'],
            'transfer_id'    => ['nullable', 'exists:paystack_transfers,id'],
        ]);

        $endpoints = WebhookEndpoint::where('project_id', $project->id)
            ->where('status', 'active')
            ->count();

        if ($endpoints === 0) {
            return back()->with('error', 'No active webhook endpoints. Add one first.');
        }

        match ($validated['event_type']) {
            'charge.success' => $this->fireChargeSuccess($project, $validated),
            'transfer.success' => $this->fireTransferEvent($project, $validated, 'success'),
            'transfer.failed'  => $this->fireTransferEvent($project, $validated, 'failed'),
        };

        return back()->with('success', "Webhook {$validated['event_type']} fired successfully.");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function fireChargeSuccess(Project $project, array $data): void
    {
        $tx = isset($data['transaction_id'])
            ? PaystackTransaction::find($data['transaction_id'])
            : PaystackTransaction::where('project_id', $project->id)
            ->where('status', 'success')
            ->latest()
            ->first();

        if (!$tx) return;

        $this->webhooks->fireChargeSuccess($tx);
    }

    private function fireTransferEvent(Project $project, array $data, string $outcome): void
    {
        $transfer = isset($data['transfer_id'])
            ? PaystackTransfer::find($data['transfer_id'])
            : PaystackTransfer::where('project_id', $project->id)
            ->latest()
            ->first();

        if (!$transfer) return;

        if ($outcome === 'success') {
            $this->webhooks->fireTransferSuccess($transfer);
        } else {
            $this->webhooks->fireTransferFailed($transfer);
        }
    }
}
