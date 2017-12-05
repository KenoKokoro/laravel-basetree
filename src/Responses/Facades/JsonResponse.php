<?php


namespace BaseTree\Responses\Facades;


use Illuminate\Support\Facades\Facade;

class JsonResponse extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'basetree.response.json';
    }
}