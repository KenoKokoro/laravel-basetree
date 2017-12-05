<?php


namespace BaseTree\Responses;


use Illuminate\Foundation\AliasLoader;
use BaseTree\Responses\Facades\JsonResponse as JsonFacade;
use BaseTree\Responses\Facades\HttpResponse as HttpFacade;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->app->bind('basetree.response.http', function () {
            return new HttpResponse;
        });

        $this->app->bind('basetree.response.json', function () {
            return new JsonResponse;
        });

        $this->registerAliases();
    }

    private function registerAliases()
    {
        $loader = AliasLoader::getInstance();
        $loader->alias('Json', JsonFacade::class);
        $loader->alias('Http', HttpFacade::class);
    }
}