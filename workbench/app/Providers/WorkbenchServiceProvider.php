<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load views from the workbench resources/views directory
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'workbench');

        // set app config to use file cache
        config(['cache.default' => 'file']);

    }
}
