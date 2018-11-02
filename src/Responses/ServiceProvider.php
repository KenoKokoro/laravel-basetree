<?php


namespace BaseTree\Responses;


use BaseTree\Responses\Facades\HttpResponse as HttpFacade;
use BaseTree\Responses\Facades\JsonResponse as JsonFacade;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function register()
    {
        $this->app->bind('basetree.response.http', function() {
            return new HttpResponse;
        });

        $this->app->bind('basetree.response.json', function() {
            return new JsonResponse;
        });

        $this->registerAliases();
    }

    private function registerAliases()
    {
        $this->app->alias('Json', JsonFacade::class);
        $this->app->alias('Http', HttpFacade::class);
    }
}