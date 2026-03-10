<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionExportController extends Controller
{
    public function __invoke(Request $request, Project $project): StreamedResponse
    {
        $this->authorize('view', $project);

        $request->validate([
            'status'     => ['nullable', 'string'],
            'wallet_id'  => ['nullable', 'exists:wallets,id'],
            'date_from'  => ['nullable', 'date'],
            'date_to'    => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $query = Transaction::where('project_id', $project->id)
            ->with('wallet')
            ->oldest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('wallet_id')) {
            $query->where('wallet_id', $request->wallet_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $filename = implode('-', array_filter([
            'devwallet',
            \Str::slug($project->name),
            'transactions',
            $request->date_from ?? null,
            $request->date_to   ?? null,
        ])) . '.csv';

        return response()->streamDownload(function () use ($query) {

            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'Reference',
                'Type',
                'Status',
                'Amount',
                'Currency',
                'Wallet',
                'Wallet Reference',
                'Narration',
                'Balance Before',
                'Balance After',
                'Failure Reason',
                'Provider',
                'Idempotency Key',
                'Completed At',
                'Created At',
            ]);

            // Stream rows in chunks to handle large datasets
            $query->chunk(200, function ($transactions) use ($handle) {
                foreach ($transactions as $tx) {
                    fputcsv($handle, [
                        $tx->reference,
                        $tx->type->value,
                        $tx->status->value,
                        number_format($tx->amount / 100, 2, '.', ''),
                        $tx->currency,
                        $tx->wallet->name      ?? '',
                        $tx->wallet->reference ?? '',
                        $tx->narration,
                        number_format($tx->balance_before / 100, 2, '.', ''),
                        number_format($tx->balance_after  / 100, 2, '.', ''),
                        $tx->failure_reason    ?? '',
                        $tx->provider          ?? '',
                        $tx->idempotency_key   ?? '',
                        $tx->completed_at?->format('Y-m-d H:i:s') ?? '',
                        $tx->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
