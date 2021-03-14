
<?php


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
    }
}