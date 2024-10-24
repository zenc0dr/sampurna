<?php

namespace Zenc0dr\Sampurna\Providers;

use Illuminate\Support\ServiceProvider;
use Zenc0dr\Sampurna\Commands\SampurnaCommand;
use Zenc0dr\Sampurna\Commands\SampurnaStackCommand;
use Zenc0dr\Sampurna\Commands\SampurnaUnitCommand;

class SampurnaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sampurna.php', 'sampurna');
    }

    public function boot(): void
    {
        require __DIR__.'/../helpers.php';

        $this->loadRoutesFrom(__DIR__ . '/../Http/routes.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SampurnaCommand::class,
                SampurnaStackCommand::class,
                SampurnaUnitCommand::class
            ]);
        }

        $this->publishes([
            __DIR__ . '/../config/sampurna.php' => config_path('sampurna.php'),
        ], 'config');
    }
}