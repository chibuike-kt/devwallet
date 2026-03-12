<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'demo@devwallet.dev'],
            [
                'name'              => 'Demo User',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            DemoProjectSeeder::class,
        ]);
    }
}
