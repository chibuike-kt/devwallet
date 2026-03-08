<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\User;
use App\Observers\UserObserver;
use App\Policies\ProjectPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register observers
        User::observe(UserObserver::class);

        // Register policies
        Gate::policy(Project::class, ProjectPolicy::class);
    }
}
