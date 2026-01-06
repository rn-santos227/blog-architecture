<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Search\SphinxClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SphinxClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
