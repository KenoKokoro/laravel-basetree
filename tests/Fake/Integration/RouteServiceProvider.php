<?php


namespace BaseTree\Tests\Fake\Integration;


use Illuminate\Foundation\Support\Providers\RouteServiceProvider as LaravelRouteServiceProvider;
use Illuminate\Routing\Router;

class RouteServiceProvider extends LaravelRouteServiceProvider
{
    public function map()
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);

        // Define stub routes here
    }
}