<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\User;
use App\Observers\UserObserver;
use App\Policies\ProjectPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Models\Wallet;
use App\Policies\WalletPolicy;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        User::observe(UserObserver::class);

        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Wallet::class, WalletPolicy::class);
    }
}
