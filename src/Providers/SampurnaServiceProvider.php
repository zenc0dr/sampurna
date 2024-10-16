<?php

namespace Zenc0dr\Sampurna\Providers;

use Illuminate\Support\ServiceProvider;
use Zenc0dr\Sampurna\Commands\StackCommand;

class SampurnaServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Регистрация конфигураций
        $this->mergeConfigFrom(__DIR__ . '/../config/sampurna.php', 'sampurna');
    }

    public function boot()
    {
        // Загрузка маршрутов
        $this->loadRoutesFrom(__DIR__ . '/../Http/routes.php');

        // Регистрация консольных команд
        if ($this->app->runningInConsole()) {
            $this->commands([
                StackCommand::class,
            ]);
        }

        // Публикация конфигураций
        $this->publishes([
            __DIR__ . '/../config/sampurna.php' => config_path('sampurna.php'),
        ], 'config');
    }
}