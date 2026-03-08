<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Wallet;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LedgerController extends Controller
{
    public function __construct(protected LedgerService $ledger) {}

    public function index(Request $request, Project $project, Wallet $wallet): View
    {
        $this->authorize('view', $wallet);

        $entries = $wallet->ledgerEntries()
            ->with('transaction')
            ->latest()
            ->paginate(30);

        $integrity = $this->ledger->verifyBalance($wallet);

        return view('ledger.index', compact('project', 'wallet', 'entries', 'integrity'));
    }
}
