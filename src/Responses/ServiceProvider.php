<?php


namespace BaseTree\Responses;


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
    }
}