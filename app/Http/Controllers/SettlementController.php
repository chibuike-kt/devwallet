<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SettlementBatch;
use App\Models\Wallet;
use App\Services\SettlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettlementController extends Controller
{
    public function __construct(protected SettlementService $settlement) {}

    public function index(Request $request, Project $project): View
    {
        $this->authorize('view', $project);

        $batches = SettlementBatch::where('project_id', $project->id)
            ->with(['wallet'])
            ->latest()
            ->get();

        $wallets = $project->wallets()->active()->get();

        // Build preview for each active wallet
        $previews = $wallets->mapWithKeys(function (Wallet $wallet) {
            return [$wallet->id => $this->settlement->preview($wallet)];
        });

        return view('settlements.index', compact(
            'project',
            'batches',
            'wallets',
            'previews'
        ));
    }

    public function run(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('view', $project);

        $request->validate([
            'wallet_id' => ['required', 'exists:wallets,id'],
            'notes'     => ['nullable', 'string', 'max:300'],
        ]);

        $wallet = Wallet::where('id', $request->wallet_id)
            ->where('project_id', $project->id)
            ->firstOrFail();

        try {
            $batch = $this->settlement->run($wallet, $project, $request->notes);

            activity()
                ->causedBy($request->user())
                ->performedOn($project)
                ->log(
                    "Settlement run: {$batch->reference} — "
                        . "{$batch->transaction_count} transactions, "
                        . "{$batch->formattedTotal()}"
                );

            return redirect()
                ->route('projects.settlements.show', [$project, $batch])
                ->with(
                    'success',
                    "Settlement {$batch->reference} completed. "
                        . "{$batch->transaction_count} transactions totalling "
                        . "{$batch->formattedTotal()} settled."
                );
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('projects.settlements.index', $project)
                ->with('error', $e->getMessage());
        }
    }

    public function show(Request $request, Project $project, SettlementBatch $batch): View
    {
        $this->authorize('view', $project);

        abort_if($batch->project_id !== $project->id, 404);

        $batch->load(['wallet', 'transactions.wallet']);

        return view('settlements.show', compact('project', 'batch'));
    }
}
