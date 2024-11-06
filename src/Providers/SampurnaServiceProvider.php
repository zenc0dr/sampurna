<?php

namespace Zenc0dr\Sampurna\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Scheduling\Schedule;
use Zenc0dr\Sampurna\Commands\SampurnaDispatcherCommand;
use Zenc0dr\Sampurna\Commands\SampurnaDaemonCommand;
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
                SampurnaDispatcherCommand::class,
                SampurnaDaemonCommand::class,
                SampurnaStackCommand::class,
                SampurnaUnitCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../config/sampurna.php' => config_path('sampurna.php'),
        ], 'config');

        # artisan vendor:publish --tag=sampurna-assets
        $this->publishes([
            __DIR__.'/../public' => public_path('sampurna'),
        ], 'sampurna-assets');

        if (!File::exists(public_path('sampurna'))) {
            File::link(
                __DIR__.'/../public',
                public_path('sampurna')
            );
        }

        # Добавление задач для планировщика
        $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
            $this->scheduleTasks($schedule);
        });
    }

    protected function scheduleTasks(Schedule $schedule): void
    {
        $schedule->command('sampurna:dispatcher');
    }
}