<?php

namespace Zenc0dr\Sampurna\Providers;

use Illuminate\Support\ServiceProvider;
use Zenc0dr\Sampurna\Commands\StackCommand;

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
                StackCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../config/sampurna.php' => config_path('sampurna.php'),
        ], 'config');
    }
}