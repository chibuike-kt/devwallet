<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWalletRequest;
use App\Models\Project;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function __construct(protected WalletService $walletService) {}

    public function index(Request $request, Project $project): View
    {
        $this->authorize('view', $project);

        $wallets = $project->wallets()->latest()->get();

        return view('wallets.index', compact('project', 'wallets'));
    }

    public function create(Request $request, Project $project): View
    {
        $this->authorize('view', $project);

        return view('wallets.create', compact('project'));
    }

    public function store(StoreWalletRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('view', $project);

        $wallet = $project->wallets()->create([
            'name'     => $request->name,
            'currency' => $request->currency,
        ]);

        activity()
            ->causedBy($request->user())
            ->performedOn($wallet)
            ->log("Created wallet '{$wallet->name}' ({$wallet->currency}) in project '{$project->name}'");

        return redirect()
            ->route('projects.wallets.show', [$project, $wallet])
            ->with('success', "Wallet \"{$wallet->name}\" created successfully.");
    }

    public function show(Request $request, Project $project, Wallet $wallet): View
    {
        $this->authorize('view', $wallet);

        return view('wallets.show', compact('project', 'wallet'));
    }

    public function destroy(Request $request, Project $project, Wallet $wallet): RedirectResponse
    {
        $this->authorize('delete', $wallet);

        $wallet->update(['status' => 'closed']);

        activity()
            ->causedBy($request->user())
            ->performedOn($wallet)
            ->log("Closed wallet '{$wallet->name}' in project '{$project->name}'");

        return redirect()
            ->route('projects.wallets.index', $project)
            ->with('success', 'Wallet closed.');
    }
}
