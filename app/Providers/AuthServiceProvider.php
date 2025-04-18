<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\PrintConfiguration;
use App\Policies\PrintConfigurationPolicy;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        PrintConfiguration::class => PrintConfigurationPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        Gate::define('admin-access', function (User $user) {
            return $user->is_admin === true;
        });
    }
} 