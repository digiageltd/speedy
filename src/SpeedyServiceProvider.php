<?php

namespace Digiageltd\Speedy;

use Illuminate\Support\ServiceProvider;

class SpeedyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make(\Digiageltd\Speedy\SpeedyController::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
