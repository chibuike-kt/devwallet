<?php

namespace App\Http\Controllers;

use App\Http\Requests\RunScenarioRequest;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\TransactionService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScenarioController extends Controller
{
    public function __construct(
        protected TransactionService $transactions,
        protected WalletService      $wallets,
    ) {}

    public function index(Request $request, Project $project): View
    {
        $this->authorize('view', $project);

        $wallets = $project->wallets()->active()->get();
        $allWallets = $project->wallets()->get();

        $recentTransactions = Transaction::where('project_id', $project->id)
            ->with('wallet')
            ->latest()
            ->take(5)
            ->get();

        $reversibleTransaction = Transaction::where('project_id', $project->id)
            ->where('status', 'success')
            ->whereNotIn('type', ['reversal'])
            ->latest()
            ->first();

        return view('scenarios.index', compact(
            'project',
            'wallets',
            'allWallets',
            'recentTransactions',
            'reversibleTransaction',
        ));
    }

    public function run(RunScenarioRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('view', $project);

        $scenario = $request->scenario;

        try {
            $result = match ($scenario) {
                'fund_wallet'       => $this->fundWallet($request, $project),
                'debit_wallet'      => $this->debitWallet($request, $project),
                'wallet_transfer'   => $this->walletTransfer($request, $project),
                'freeze_wallet'     => $this->freezeWallet($request, $project),
                'unfreeze_wallet'   => $this->unfreezeWallet($request, $project),
                'failed_transfer'   => $this->failedTransfer($request, $project),
                'reverse_transaction' => $this->reverseTransaction($request, $project),
                'bank_transfer_timeout' => $this->bankTransferTimeout($request, $project),
                default => throw new \InvalidArgumentException("Unknown scenario: {$scenario}"),
            };

            return redirect()
                ->route('projects.scenarios.index', $project)
                ->with('success', $result['message'])
                ->with('scenario_result', $result);
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('projects.scenarios.index', $project)
                ->with('error', $e->getMessage());
        }
    }

    // ─── Scenario Handlers ────────────────────────────────────────────────────

    private function fundWallet(RunScenarioRequest $request, Project $project): array
    {
        $wallet = $this->resolveWallet($request->wallet_id, $project);
        $amount = $request->amountInMinorUnits();
        $narration = $request->narration ?: 'Simulated wallet funding';

        $tx = $this->transactions->fund(
            wallet: $wallet,
            amount: $amount,
            narration: $narration,
            provider: 'simulation',
        );

        activity()
            ->causedBy($request->user())
            ->performedOn($project)
            ->log("Scenario: Fund Wallet — {$wallet->name} credited {$wallet->formatAmount($amount)}");

        return [
            'message'    => "✓ {$wallet->name} funded successfully. New balance: {$wallet->fresh()->formattedBalance()}",
            'tx_id'      => $tx->id,
            'status'     => $tx->status->value,
        ];
    }

    private function debitWallet(RunScenarioRequest $request, Project $project): array
    {
        $wallet = $this->resolveWallet($request->wallet_id, $project);
        $amount = $request->amountInMinorUnits();
        $narration = $request->narration ?: 'Simulated wallet debit';

        $tx = $this->transactions->debit(
            wallet: $wallet,
            amount: $amount,
            narration: $narration,
            provider: 'simulation',
        );

        activity()
            ->causedBy($request->user())
            ->performedOn($project)
            ->log("Scenario: Debit Wallet — {$wallet->name} debited {$wallet->formatAmount($amount)} [{$tx->status->value}]");

        $balance = $wallet->fresh()->formattedBalance();

        if ($tx->isFailed()) {
            return [
                'message' => "⚠ Debit failed: {$tx->failure_reason} Balance unchanged: {$balance}",
                'tx_id'   => $tx->id,
                'status'  => $tx->status->value,
            ];
        }

        return [
            'message' => "✓ {$wallet->name} debited successfully. New balance: {$balance}",
            'tx_id'   => $tx->id,
            'status'  => $tx->status->value,
        ];
    }

    private function walletTransfer(RunScenarioRequest $request, Project $project): array
    {
        $from = $this->resolveWallet($request->wallet_id, $project);
        $to   = $this->resolveWallet($request->target_wallet_id, $project);

        if ($from->id === $to->id) {
            throw new \RuntimeException('Source and destination wallets must be different.');
        }

        if ($from->currency !== $to->currency) {
            throw new \RuntimeException(
                "Currency mismatch: cannot transfer from {$from->currency} to {$to->currency}."
            );
        }

        $amount    = $request->amountInMinorUnits();
        $narration = $request->narration ?: "Transfer from {$from->name} to {$to->name}";

        $tx = $this->transactions->transfer($from, $to, $amount, $narration);

        activity()
            ->causedBy($request->user())
            ->performedOn($project)
            ->log("Scenario: Wallet Transfer — {$from->name} → {$to->name}, {$from->formatAmount($amount)} [{$tx->status->value}]");

        if ($tx->isFailed()) {
            return [
                'message' => "⚠ Transfer failed: {$tx->failure_reason}",
                'tx_id'   => $tx->id,
                'status'  => $tx->status->value,
            ];
        }

        return [
            'message' => "✓ Transferred {$from->formatAmount($amount)} from {$from->name} to {$to->name}. "
                . "New balance: {$from->fresh()->formattedBalance()}",
            'tx_id'   => $tx->id,
            'status'  => $tx->status->value,
        ];
    }

    private function freezeWallet(RunScenarioRequest $request, Project $project): array
    {
        $wallet = $this->resolveWallet($request->wallet_id, $project);

        $this->wallets->freeze($wallet);

        activity()
            ->causedBy($request->user())
            ->performedOn($project)
            ->log("Scenario: Freeze Wallet — {$wallet->name} frozen");

        return [
            'message' => "✓ Wallet \"{$wallet->name}\" has been frozen. All transactions are now blocked.",
            'status'  => 'frozen',
        ];
    }

    private function unfreezeWallet(RunScenarioRequest $request, Project $project): array
    {
        $wallet = $this->resolveWallet($request->wallet_id, $project);

        if (! $wallet->isFrozen()) {
            throw new \RuntimeException("Wallet \"{$wallet->name}\" is not frozen.");
        }

        $this->wallets->unfreeze($wallet);

        activity()
            ->causedBy($request->user())
            ->performedOn($project)
            ->log("Scenario: Unfreeze Wallet — {$wallet->name} unfrozen");

        return [
            'message' => "✓ Wallet \"{$wallet->name}\" has been unfrozen and is active again.",
            'status'  => 'active',
        ];
    }

    private function failedTransfer(RunScenarioRequest $request, Project $project): array
    {
        $wallet = $this->resolveWallet($request->wallet_id, $project);
        $amount = $request->amountInMinorUnits();

        // Temporarily freeze, attempt debit, then restore original status
        $originalStatus = $wallet->status;

        if ($wallet->isActive()) {
            $this->wallets->freeze($wallet);
            $wallet = $wallet->fresh();
        }

        $tx = $this->transactions->debit(
            wallet: $wallet,
            amount: $amount,
            narration: 'Simulated failed transfer — wallet frozen',
            provider: 'simulation',
        );

        // Restore wallet to active if we froze it for this simulation
        if ($originalStatus === 'active') {
            $this->wallets->unfreeze($wallet->fresh());
        }

        activity()
            ->causedBy($request->user())
            ->performedOn($project)
            ->log("Scenario: Failed Transfer — {$wallet->name} blocked, transaction failed as expected");

        return [
            'message' => "✓ Simulated failed transfer on \"{$wallet->name}\". "
                . "Transaction recorded as failed: {$tx->failure_reason}",
            'tx_id'   => $tx->id,
            'status'  => $tx->status->value,
        ];
    }

    private function reverseTransaction(RunScenarioRequest $request, Project $project): array
    {
        $txId = $request->transaction_id;

        $transaction = Transaction::where('project_id', $project->id)
            ->where('id', $txId)
            ->firstOrFail();

        $reversalTx = $this->transactions->reverse(
            $transaction,
            'Manually reversed via simulation scenario'
        );

        activity()
            ->causedBy($request->user())
            ->performedOn($project)
            ->log("Scenario: Reverse Transaction — {$transaction->reference} reversed");

        return [
            'message' => "✓ Transaction {$transaction->reference} reversed successfully. "
                . "Reversal reference: {$reversalTx->reference}",
            'tx_id'   => $reversalTx->id,
            'status'  => $reversalTx->status->value,
        ];
    }

    private function bankTransferTimeout(RunScenarioRequest $request, Project $project): array
    {
        $wallet = $this->resolveWallet($request->wallet_id, $project);
        $amount = $request->amountInMinorUnits();

        if (! $wallet->canTransact()) {
            throw new \RuntimeException("Wallet \"{$wallet->name}\" is {$wallet->status}. Cannot simulate transfer.");
        }

        if ($wallet->available_balance < $amount) {
            throw new \RuntimeException(
                "Insufficient balance for bank transfer simulation. "
                    . "Available: {$wallet->formattedAvailableBalance()}"
            );
        }

        // Create a pending transaction that intentionally never completes
        // This simulates a provider timeout — the money is reserved but not gone
        $tx = Transaction::create([
            'wallet_id'      => $wallet->id,
            'project_id'     => $project->id,
            'type'           => \App\Enums\TransactionType::BankTransfer,
            'status'         => \App\Enums\TransactionStatus::Pending,
            'amount'         => $amount,
            'currency'       => $wallet->currency,
            'balance_before' => $wallet->balance,
            'balance_after'  => $wallet->balance,
            'narration'      => $request->narration ?: 'Bank transfer — provider timeout simulation',
            'provider'       => 'simulation',
            'failure_reason' => 'Provider did not respond within timeout window.',
            'completed_at'   => null,
        ]);

        activity()
            ->causedBy($request->user())
            ->performedOn($project)
            ->log("Scenario: Bank Transfer Timeout — {$wallet->name}, {$wallet->formatAmount($amount)}, stuck pending");

        return [
            'message' => "✓ Bank transfer initiated and stuck in pending. "
                . "Provider timeout simulated. Reference: {$tx->reference}",
            'tx_id'   => $tx->id,
            'status'  => $tx->status->value,
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function resolveWallet(?int $walletId, Project $project): Wallet
    {
        if (! $walletId) {
            throw new \RuntimeException('No wallet selected for this scenario.');
        }

        $wallet = Wallet::where('id', $walletId)
            ->where('project_id', $project->id)
            ->firstOrFail();

        return $wallet;
    }
}
