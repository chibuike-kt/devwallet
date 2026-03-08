<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WalletFactory extends Factory
{
    public function definition(): array
    {
        $currencies = ['NGN', 'USD', 'KES', 'GHS'];
        $currency   = $this->faker->randomElement($currencies);

        $names = [
            'Main Wallet', 'Savings Wallet', 'Payout Wallet',
            'Escrow Wallet', 'Float Account', 'Collections Wallet',
            'Merchant Wallet', 'Customer Wallet',
        ];

        return [
            'project_id'        => Project::factory(),
            'name'              => $this->faker->randomElement($names),
            'reference'         => 'WLT-' . strtoupper(Str::random(12)),
            'currency'          => $currency,
            'balance'           => $this->faker->numberBetween(0, 10_000_000), // in kobo/cents
            'available_balance' => function (array $attrs) {
                return $attrs['balance'];
            },
            'ledger_balance'    => function (array $attrs) {
                return $attrs['balance'];
            },
            'status'            => $this->faker->randomElement(['active', 'active', 'active', 'frozen']),
            'metadata'          => null,
        ];
    }
}
