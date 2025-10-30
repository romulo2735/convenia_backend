<?php

namespace App\Providers;

use App\Models\Collaborator;
use App\Observers\CollaboratorObserver;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::tokensExpireIn(Carbon::now()->addHours());
        Passport::refreshTokensExpireIn(Carbon::now()->addDays(7));
        Collaborator::observe(CollaboratorObserver::class);
    }
}
