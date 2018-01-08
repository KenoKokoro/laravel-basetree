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
    public function register()
    {
        $this->app->register(ResponseServiceProvider::class);
        $this->app->register(DataTablesServiceProvider::class);
        $this->app->register(ConsoleServiceProvider::class);

        if ($this->app->environment('local')) {
            $this->app->register(IdeHelperServiceProvider::class);
        }

        $this->loadAliases();
    }

    private function loadAliases()
    {
        $this->app->alias(DataTables::class, 'DataTables');
    }
}