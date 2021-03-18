<?php

namespace JulesGraus\FaviconTracker;

use Illuminate\Support\ServiceProvider;

class TrackerProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . DIRECTORY_SEPARATOR . 'routes.php');

        $this->publishes([
            __DIR__. DIRECTORY_SEPARATOR .'config.php' => config_path('favicontracker.php'),
        ], 'fit');

        $this->loadViewsFrom(__DIR__.DIRECTORY_SEPARATOR.'views', 'fit');
    }
}