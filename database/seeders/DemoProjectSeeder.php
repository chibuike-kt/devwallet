<?php

namespace Database\Seeders;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\LedgerEntry;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use App\Services\TransactionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoProjectSeeder extends Seeder
{
    public function run(): void
    {
        // ── Demo user ─────────────────────────────────────────────────────────
        $user = User::firstOrCreate(
            ['email' => 'demo@devwallet.dev'],
            [
                'name'     => 'Ada Okonkwo',
                'password' => Hash::make('password'),
            ]
        );

        // ── Project 1: Chowdeck Payments ──────────────────────────────────────
        $project1 = $this->createProject($user, [
            'name'        => 'Chowdeck Payments',
            'description' => 'Simulating wallet funding and food delivery checkout flows.',
            'environment' => 'test',
            'color'       => '#0e8de6',
        ]);

        $mainWallet = $this->createWallet($project1, 'Main Wallet', 'NGN');
        $payoutWallet = $this->createWallet($project1, 'Payout Wallet', 'NGN');

        // Fund main wallet
        $this->seedFunding($user, $mainWallet, 5000000, 'Initial float funding');
        $this->seedFunding($user, $mainWallet, 2500000, 'Top-up from bank');

        // Debit for orders
        $this->seedDebit($user, $mainWallet, 350000, 'Customer order #ORD-001');
        $this->seedDebit($user, $mainWallet, 120000, 'Customer order #ORD-002');
        $this->seedDebit($user, $mainWallet, 780000, 'Customer order #ORD-003');

        // Transfer to payout wallet
        $this->seedTransfer($user, $mainWallet, $payoutWallet, 1000000, 'Weekly payout batch');

        // Failed debit — overdraft attempt
        $this->seedFailedDebit($user, $mainWallet, 999999999, 'Attempted overdraft');

        // ── Project 2: Kuda Savings Engine ────────────────────────────────────
        $project2 = $this->createProject($user, [
            'name'        => 'Kuda Savings Engine',
            'description' => 'Testing round-up savings and inter-wallet transfers.',
            'environment' => 'staging',
            'color'       => '#059669',
        ]);

        $savingsWallet  = $this->createWallet($project2, 'Savings Wallet', 'NGN');
        $currentWallet  = $this->createWallet($project2, 'Current Account', 'NGN');
        $usdWallet      = $this->createWallet($project2, 'USD Wallet', 'USD');

        $this->seedFunding($user, $currentWallet, 10000000, 'Salary credit');
        $this->seedFunding($user, $usdWallet, 50000, 'USD deposit');
        $this->seedTransfer($user, $currentWallet, $savingsWallet, 2000000, 'Round-up savings');
        $this->seedTransfer($user, $currentWallet, $savingsWallet, 500000, 'Manual savings top-up');
        $this->seedDebit($user, $currentWallet, 450000, 'Bill payment — DSTV');
        $this->seedDebit($user, $currentWallet, 180000, 'Airtime purchase');

        // Frozen wallet scenario
        $frozenWallet = $this->createWallet($project2, 'Restricted Account', 'NGN');
        $this->seedFunding($user, $frozenWallet, 3000000, 'Pre-freeze funding');
        $frozenWallet->update(['status' => 'frozen']);
        $this->seedFailedDebit($user, $frozenWallet, 500000, 'Blocked — wallet frozen');

        // ── Project 3: Flutterwave Retry Suite ────────────────────────────────
        $project3 = $this->createProject($user, [
            'name'        => 'Flutterwave Retry Suite',
            'description' => 'Webhook retry logic and provider timeout simulation.',
            'environment' => 'test',
            'color'       => '#7c3aed',
        ]);

        $floatWallet = $this->createWallet($project3, 'Float Account', 'NGN');
        $escrowWallet = $this->createWallet($project3, 'Escrow Wallet', 'NGN');

        $this->seedFunding($user, $floatWallet, 8000000, 'Float top-up');
        $this->seedTransfer($user, $floatWallet, $escrowWallet, 2000000, 'Escrow hold');

        // Pending bank transfer (timeout simulation)
        $this->seedPendingBankTransfer($floatWallet, 500000, 'Zenith Bank transfer — provider timeout');

        // Reversal
        $tx = $this->seedDebit($user, $floatWallet, 300000, 'Erroneous charge');
        $this->seedReversal($tx, $floatWallet, 'Customer dispute — charge reversed');

        // ── Webhook data for project 1 ────────────────────────────────────────
        $this->seedWebhooks($user, $project1);

        $this->command->info('✓ Demo data seeded successfully.');
        $this->command->info('  Login: demo@devwallet.dev / password');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function createProject(User $user, array $data): Project
    {
        $name = $data['name'];
        $slug = Str::slug($name) . '-demo';

        return Project::firstOrCreate(
            ['slug' => $slug],
            array_merge($data, [
                'user_id' => $user->id,
                'slug'    => $slug,
                'status'  => 'active',
            ])
        );
    }

    private function createWallet(Project $project, string $name, string $currency): Wallet
    {
        return $project->wallets()->firstOrCreate(
            ['name' => $name, 'currency' => $currency],
            ['status' => 'active']
        );
    }

    private function seedFunding(User $user, Wallet $wallet, int $amount, string $narration): Transaction
    {
        $svc = app(TransactionService::class);
        $tx  = $svc->fund($wallet, $amount, $narration, 'simulation');

        activity()
            ->causedBy($user)
            ->performedOn($wallet->project)
            ->log("Seeded: Fund Wallet — {$wallet->name} +{$wallet->formatAmount($amount)}");

        return $tx;
    }

    private function seedDebit(User $user, Wallet $wallet, int $amount, string $narration): Transaction
    {
        $svc = app(TransactionService::class);
        $tx  = $svc->debit($wallet->fresh(), $amount, $narration, 'simulation');

        return $tx;
    }

    private function seedFailedDebit(User $user, Wallet $wallet, int $amount, string $narration): Transaction
    {
        return Transaction::create([
            'wallet_id'      => $wallet->id,
            'project_id'     => $wallet->project_id,
            'type'           => TransactionType::WalletDebit,
            'status'         => TransactionStatus::Failed,
            'amount'         => $amount,
            'currency'       => $wallet->currency,
            'balance_before' => $wallet->balance,
            'balance_after'  => $wallet->balance,
            'narration'      => $narration,
            'failure_reason' => $wallet->isFrozen()
                ? 'Wallet is frozen. Cannot debit.'
                : 'Insufficient balance.',
            'provider'       => 'simulation',
            'completed_at'   => now(),
        ]);
    }

    private function seedTransfer(User $user, Wallet $from, Wallet $to, int $amount, string $narration): Transaction
    {
        $svc = app(TransactionService::class);
        return $svc->transfer($from->fresh(), $to->fresh(), $amount, $narration);
    }

    private function seedPendingBankTransfer(Wallet $wallet, int $amount, string $narration): Transaction
    {
        return Transaction::create([
            'wallet_id'      => $wallet->id,
            'project_id'     => $wallet->project_id,
            'type'           => TransactionType::BankTransfer,
            'status'         => TransactionStatus::Pending,
            'amount'         => $amount,
            'currency'       => $wallet->currency,
            'balance_before' => $wallet->balance,
            'balance_after'  => $wallet->balance,
            'narration'      => $narration,
            'provider'       => 'flutterwave',
            'failure_reason' => 'Provider did not respond within timeout window.',
            'completed_at'   => null,
        ]);
    }

    private function seedReversal(Transaction $original, Wallet $wallet, string $reason): Transaction
    {
        $svc = app(TransactionService::class);
        return $svc->reverse($original->fresh(), $reason);
    }

    private function seedWebhooks(User $user, Project $project): void
    {
        // Register a test endpoint
        $endpoint = WebhookEndpoint::firstOrCreate(
            ['project_id' => $project->id, 'url' => 'https://example.com/webhooks/devwallet'],
            [
                'description' => 'Demo webhook receiver',
                'events'      => ['wallet.funded', 'transfer.success', 'transaction.reversed'],
                'status'      => 'active',
            ]
        );

        // Create a few webhook events from recent transactions
        $transactions = Transaction::where('project_id', $project->id)
            ->where('status', 'success')
            ->take(3)
            ->get();

        $webhookService = app(\App\Services\WebhookService::class);

        foreach ($transactions as $tx) {
            $tx->load('wallet');
            $event = $webhookService->createEvent($tx);

            // First delivery succeeds
            $webhookService->deliverEvent($event, false);

            // Second event gets a failed delivery for demo purposes
            if ($transactions->last()->id === $tx->id) {
                $event2 = $webhookService->createEvent($tx);
                $webhookService->deliverEvent($event2, true);
            }
        }
    }
}
