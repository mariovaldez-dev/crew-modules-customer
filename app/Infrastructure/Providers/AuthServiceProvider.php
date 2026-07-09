<?php

namespace App\Infrastructure\Providers;

use App\Infrastructure\Auth\CustomUserProvider;
use App\Infrastructure\Auth\Models\AuthenticatedUser;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('acceder-biblioteca', function (AuthenticatedUser $user) {
            return $user->rol === '2';
        });

        Auth::provider('custom', function ($app, array $config) {
            return new CustomUserProvider;
        });
    }
}
