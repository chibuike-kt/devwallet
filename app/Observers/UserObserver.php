<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Str;

class UserObserver
{
    /**
     * Seed a default demo project when a new user registers.
     */
    public function created(User $user): void
    {
        $name = 'My First Project';

        $user->projects()->create([
            'name'        => $name,
            'slug'        => Str::slug($name) . '-' . Str::random(6),
            'description' => 'Auto-generated demo project. Use this to explore DevWallet.',
            'environment' => 'test',
            'color'       => '#0e8de6',
            'status'      => 'active',
        ]);
    }
}
