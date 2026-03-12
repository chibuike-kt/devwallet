<?php

namespace Database\Seeders;

use App\Models\PaystackCustomer;
use App\Models\PaystackTransaction;
use App\Models\PaystackTransfer;
use App\Models\Project;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WebhookEndpoint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoProjectSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'demo@devwallet.dev')->first();
        if (!$user) return;

        // ── Paystack project ──────────────────────────────────────────────────
        $paystack = Project::create([
            'user_id'     => $user->id,
            'name'        => 'Chowdeck Checkout',
            'slug'        => 'chowdeck-checkout-' . Str::random(5),
            'description' => 'Paystack payment integration',
            'provider'    => 'paystack',
            'environment' => 'test',
            'color'       => '#00C3F7',
            'status'      => 'active',
        ]);

        $this->seedPaystackData($paystack);

        // ── Flutterwave project ───────────────────────────────────────────────
        $flutterwave = Project::create([
            'user_id'     => $user->id,
            'name'        => 'Piggyvest Savings',
            'slug'        => 'piggyvest-savings-' . Str::random(5),
            'description' => 'Flutterwave disbursements',
            'provider'    => 'flutterwave',
            'environment' => 'test',
            'color'       => '#F5A623',
            'status'      => 'active',
        ]);

        $this->seedFlutterwaveData($flutterwave);

        // ── Stripe project ────────────────────────────────────────────────────
        $stripe = Project::create([
            'user_id'     => $user->id,
            'name'        => 'Shuttlers Global',
            'slug'        => 'shuttlers-global-' . Str::random(5),
            'description' => 'Stripe international payments',
            'provider'    => 'stripe',
            'environment' => 'test',
            'color'       => '#635BFF',
            'status'      => 'active',
        ]);

        $this->seedStripeData($stripe);

        // Set active project to Paystack
        // (dashboard will redirect there on first login)
    }

    // ── Paystack seed data ────────────────────────────────────────────────────

    private function seedPaystackData(Project $project): void
    {
        $customers = [
            ['email' => 'tunde@chowdeck.com',   'first_name' => 'Tunde',   'last_name' => 'Bakare'],
            ['email' => 'amara@chowdeck.com',   'first_name' => 'Amara',   'last_name' => 'Obi'],
            ['email' => 'kemi@example.com',     'first_name' => 'Kemi',    'last_name' => 'Adeyemi'],
            ['email' => 'emeka@example.com',    'first_name' => 'Emeka',   'last_name' => 'Nwosu'],
        ];

        $createdCustomers = collect($customers)->map(
            fn($c) =>
            PaystackCustomer::create(array_merge($c, [
                'project_id'    => $project->id,
                'customer_code' => 'CUS_' . strtolower(Str::random(12)),
            ]))
        );

        $txData = [
            ['amount' => 250000, 'status' => 'success', 'customer_index' => 0],
            ['amount' => 150000, 'status' => 'success', 'customer_index' => 1],
            ['amount' => 75000,  'status' => 'failed',  'customer_index' => 2],
            ['amount' => 500000, 'status' => 'success', 'customer_index' => 3],
            ['amount' => 99000,  'status' => 'success', 'customer_index' => 0],
            ['amount' => 32000,  'status' => 'abandoned', 'customer_index' => 1],
        ];

        foreach ($txData as $t) {
            PaystackTransaction::create([
                'project_id'           => $project->id,
                'paystack_customer_id' => $createdCustomers[$t['customer_index']]->id,
                'reference'            => strtolower(Str::random(16)),
                'status'               => $t['status'],
                'amount'               => $t['amount'],
                'currency'             => 'NGN',
                'channel'              => 'card',
                'gateway_response'     => $t['status'] === 'success' ? 'Approved' : 'Declined',
                'authorization_code'   => $t['status'] === 'success' ? 'AUTH_' . Str::random(8) : null,
                'card_type'            => 'visa',
                'last4'                => (string) rand(1000, 9999),
                'exp_month'            => '12',
                'exp_year'             => '2030',
                'bank'                 => 'GTB',
                'paid_at'              => $t['status'] === 'success' ? now()->subMinutes(rand(5, 200)) : null,
                'created_at'           => now()->subMinutes(rand(5, 500)),
            ]);
        }

        PaystackTransfer::create([
            'project_id'               => $project->id,
            'amount'                   => 100000,
            'currency'                 => 'NGN',
            'status'                   => 'success',
            'narration'                => 'Vendor payout',
            'recipient_name'           => 'Seun Abiodun',
            'recipient_account_number' => '0123456789',
            'recipient_bank_code'      => '058',
            'recipient_bank_name'      => 'GTBank',
            'completed_at'             => now()->subHours(2),
        ]);

        WebhookEndpoint::create([
            'project_id' => $project->id,
            'url'        => 'https://chowdeck.example.com/webhooks/paystack',
            'secret'     => Str::random(32),
            'status'     => 'active',
        ]);
    }

    // ── Flutterwave seed data ─────────────────────────────────────────────────

    private function seedFlutterwaveData(Project $project): void
    {
        $customers = [
            ['email' => 'david@piggyvest.com', 'first_name' => 'David', 'last_name' => 'Afolabi'],
            ['email' => 'ngozi@piggyvest.com', 'first_name' => 'Ngozi', 'last_name' => 'Eze'],
        ];

        $createdCustomers = collect($customers)->map(
            fn($c) =>
            PaystackCustomer::create(array_merge($c, [
                'project_id'    => $project->id,
                'customer_code' => 'CUS_' . strtolower(Str::random(12)),
            ]))
        );

        foreach (
            [
                ['amount' => 500000, 'status' => 'success', 'ci' => 0],
                ['amount' => 200000, 'status' => 'success', 'ci' => 1],
                ['amount' => 800000, 'status' => 'failed',  'ci' => 0],
            ] as $t
        ) {
            PaystackTransaction::create([
                'project_id'           => $project->id,
                'paystack_customer_id' => $createdCustomers[$t['ci']]->id,
                'reference'            => 'flw-ref-' . strtolower(Str::random(10)),
                'status'               => $t['status'],
                'amount'               => $t['amount'],
                'currency'             => 'NGN',
                'channel'              => 'card',
                'gateway_response'     => $t['status'] === 'success' ? 'Approved' : 'Declined',
                'paid_at'              => $t['status'] === 'success' ? now()->subHours(rand(1, 12)) : null,
                'created_at'           => now()->subHours(rand(1, 24)),
            ]);
        }

        PaystackTransfer::create([
            'project_id'               => $project->id,
            'amount'                   => 250000,
            'currency'                 => 'NGN',
            'status'                   => 'pending',
            'narration'                => 'Savings payout',
            'recipient_name'           => 'Bola Tinubu',
            'recipient_account_number' => '9876543210',
            'recipient_bank_code'      => '044',
            'recipient_bank_name'      => 'Access Bank',
        ]);

        WebhookEndpoint::create([
            'project_id' => $project->id,
            'url'        => 'https://piggyvest.example.com/webhooks/flutterwave',
            'secret'     => Str::random(32),
            'status'     => 'active',
        ]);
    }

    // ── Stripe seed data ──────────────────────────────────────────────────────

    private function seedStripeData(Project $project): void
    {
        $customers = [
            ['email' => 'james@shuttlers.africa', 'first_name' => 'James', 'last_name' => 'Okafor'],
            ['email' => 'sarah@shuttlers.africa', 'first_name' => 'Sarah', 'last_name' => 'Mensah'],
        ];

        $createdCustomers = collect($customers)->map(
            fn($c) =>
            PaystackCustomer::create(array_merge($c, [
                'project_id'    => $project->id,
                'customer_code' => 'CUS_' . strtolower(Str::random(12)),
            ]))
        );

        foreach (
            [
                ['amount' => 2000,  'currency' => 'USD', 'status' => 'success', 'ci' => 0],
                ['amount' => 5000,  'currency' => 'USD', 'status' => 'success', 'ci' => 1],
                ['amount' => 1500,  'currency' => 'USD', 'status' => 'failed',  'ci' => 0],
            ] as $t
        ) {
            PaystackTransaction::create([
                'project_id'           => $project->id,
                'paystack_customer_id' => $createdCustomers[$t['ci']]->id,
                'reference'            => 'pi_' . strtolower(Str::random(24)),
                'status'               => $t['status'],
                'amount'               => $t['amount'],
                'currency'             => $t['currency'],
                'channel'              => 'card',
                'gateway_response'     => $t['status'] === 'success' ? 'Approved' : 'Declined',
                'authorization_code'   => $t['status'] === 'success' ? 'AUTH_' . Str::random(8) : null,
                'card_type'            => 'visa',
                'last4'                => '4242',
                'paid_at'              => $t['status'] === 'success' ? now()->subHours(rand(1, 6)) : null,
                'created_at'           => now()->subHours(rand(1, 12)),
            ]);
        }

        PaystackTransfer::create([
            'project_id'               => $project->id,
            'amount'                   => 10000,
            'currency'                 => 'USD',
            'status'                   => 'success',
            'narration'                => 'Driver payout',
            'recipient_name'           => 'Stripe Connect Account',
            'recipient_account_number' => 'acct_1Nq8123abc',
            'recipient_bank_code'      => 'STRIPE',
            'completed_at'             => now()->subHours(1),
        ]);

        WebhookEndpoint::create([
            'project_id' => $project->id,
            'url'        => 'https://shuttlers.example.com/webhooks/stripe',
            'secret'     => Str::random(32),
            'status'     => 'active',
        ]);
    }
}
