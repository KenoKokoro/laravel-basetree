<?php


namespace BaseTree\Console\Generators;


use BaseTree\Console\ApplicationNamespace;
use BaseTree\Console\Generators\BLL\GenerateBll;
use BaseTree\Console\Generators\Controller\GenerateController;
use BaseTree\Console\Generators\DAL\GenerateDal;
use BaseTree\Console\Publishers\PublishBoilerplate;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function boot()
    {
        # Generators
        $this->commands([GenerateDal::class, GenerateBll::class, GenerateController::class, ApplicationNamespace::class]);

        # Publisher
        $this->commands([PublishBoilerplate::class]);
    }
}
