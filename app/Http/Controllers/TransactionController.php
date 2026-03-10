<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request, Project $project): View
    {
        $this->authorize('view', $project);

        $project->load('wallets');

        $transactions = Transaction::where('project_id', $project->id)
            ->with('wallet')
            ->latest()
            ->paginate(20);

        return view('transactions.index', compact('project', 'transactions'));
    }

    public function show(Request $request, Project $project, Transaction $transaction): View
    {
        $this->authorize('view', $project);

        abort_if($transaction->project_id !== $project->id, 404);

        $transaction->load(['wallet', 'settlementBatch']);

        $ledgerEntries = $transaction->wallet
            ->ledgerEntries()
            ->where('transaction_id', $transaction->id)
            ->get();

        return view('transactions.show', compact('project', 'transaction', 'ledgerEntries'));
    }
}
