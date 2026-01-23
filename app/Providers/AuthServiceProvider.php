<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        //Passport::routes();

        // No more Passport::routes() in Passport v11+
        // Instead, enable routes manually if you still need them:
        //Passport::loadKeysFrom(base_path('storage/oauth-private.key'));
        Passport::loadKeysFrom(storage_path());
    }
}
