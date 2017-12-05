<?php


namespace BaseTree\Responses\Facades;


use Illuminate\Support\Facades\Facade;

class HttpResponse extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'basetree.response.http';
    }
}