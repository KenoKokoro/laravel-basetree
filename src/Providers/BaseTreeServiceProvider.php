<?php


namespace BaseTree\Providers;


use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use BaseTree\Console\Generators\ServiceProvider as ConsoleServiceProvider;
use BaseTree\Responses\ServiceProvider as ResponseServiceProvider;
use Illuminate\Support\ServiceProvider;
use Yajra\DataTables\DataTablesServiceProvider;
use Yajra\DataTables\Facades\DataTables;

class BaseTreeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerProviders();
        $this->registerConfig();

        if (config('base-tree.log')) {
            $this->registerLogger();
        }

        $this->loadAliases();
    }

    private function registerProviders(): void
    {
        $this->app->register(ResponseServiceProvider::class);
        $this->app->register(DataTablesServiceProvider::class);
        $this->app->register(ConsoleServiceProvider::class);

        if ($this->app->environment('local')) {
            $this->app->register(IdeHelperServiceProvider::class);
        }
    }

    private function registerConfig(): void
    {
        $this->publishes([__DIR__ . '/../../config/base-tree.php' => config_path('base-tree.php')]);
        $this->mergeConfigFrom(__DIR__ . '/../../config/base-tree.php', 'base-tree');
    }

    private function loadAliases(): void
    {
        $this->app->alias(DataTables::class, 'DataTables');
    }

    private function registerLogger(): void
    {
        if ( ! class_exists(BaseTreeLoggerServiceProvider::class)) {
            throw new \Exception("Logger is enabled, but package is not installed\n Please require the kenokokoro/laravel-basetree-logger package");
        }

        $this->app->register(BaseTreeLoggerServiceProvider::class);
    }
}