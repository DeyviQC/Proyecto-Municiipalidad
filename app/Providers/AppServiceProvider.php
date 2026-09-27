<?php

namespace App\Providers;

use App\Models\Acta;
use App\Models\Auditoria;
use App\Models\User;
use App\Policies\ActaPolicy;
use App\Policies\AuditoriaPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Gate::policy(Acta::class, ActaPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Auditoria::class, AuditoriaPolicy::class);
    }
}
