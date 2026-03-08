<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet;

class WalletPolicy
{
    public function view(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->project->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->project->user_id;
    }

    public function delete(User $user, Wallet $wallet): bool
    {
        return $user->id === $wallet->project->user_id;
    }
}
