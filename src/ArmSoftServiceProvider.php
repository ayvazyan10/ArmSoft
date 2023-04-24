<?php

namespace Ayvazyan10\ArmSoft;

use Illuminate\Support\ServiceProvider;

class ArmSoftServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        require_once __DIR__ . '/helper.php';

        $this->mergeConfigFrom(__DIR__ . '/../config/armsoft.php', 'armsoft');

        // Register the service the package provides.
        $this->app->singleton('armsoft', function ($app) {
            return new ArmSoft;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['armsoft'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/armsoft.php' => config_path('armsoft.php'),
        ], 'armsoft.config');
    }
}
