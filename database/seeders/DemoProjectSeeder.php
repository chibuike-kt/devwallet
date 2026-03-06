<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoProjectSeeder extends Seeder
{
    public function run(): void
    {
        // Create a demo user if one doesn't exist
        $user = User::firstOrCreate(
            ['email' => 'demo@devwallet.dev'],
            [
                'name'     => 'Ada Okonkwo',
                'password' => Hash::make('password'),
            ]
        );

        $projects = [
            [
                'name'        => 'Chowdeck Payments',
                'description' => 'Simulating wallet funding and food delivery checkout flows.',
                'environment' => 'test',
                'color'       => '#0e8de6',
            ],
            [
                'name'        => 'Kuda Savings Engine',
                'description' => 'Testing round-up savings rules and inter-wallet transfers.',
                'environment' => 'staging',
                'color'       => '#059669',
            ],
            [
                'name'        => 'Flutterwave Retry Suite',
                'description' => 'Webhook retry logic and provider timeout simulation.',
                'environment' => 'test',
                'color'       => '#7c3aed',
            ],
        ];

        foreach ($projects as $data) {
            $name = $data['name'];

            Project::firstOrCreate(
                ['slug' => Str::slug($name) . '-demo'],
                array_merge($data, [
                    'user_id' => $user->id,
                    'slug'    => Str::slug($name) . '-demo',
                    'status'  => 'active',
                ])
            );
        }

        $this->command->info('Demo projects seeded for demo@devwallet.dev (password: password)');
    }
}
