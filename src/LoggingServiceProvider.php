<?php

namespace Corb\Logging;

use Illuminate\Support\ServiceProvider;

class LoggingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes(
            [
                __DIR__ . '/config' => config_path()
            ],
            'config'
        );

        $this->publishes(
            [
                __DIR__ . '/migrations' => database_path('migrations/')
            ],
            'migrations'
        );
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
