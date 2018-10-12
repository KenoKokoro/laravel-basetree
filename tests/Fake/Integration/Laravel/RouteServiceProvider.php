<?php


namespace BaseTree\Tests\Fake\Integration\Laravel;


use Illuminate\Foundation\Support\Providers\RouteServiceProvider as IlluminateRouteServiceProvider;
use Illuminate\Routing\Router;

class RouteServiceProvider extends IlluminateRouteServiceProvider
{
    public function map()
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);

        // Define stub routes here
        $router->get('get-route-no-action', 'MissingController@index');
        $router->apiResource('users', UsersController::class);
    }
}