<?php


namespace BaseTree\Tests\Fake\Wrappers;


use BaseTree\Controllers\BaseController;

class BaseControllerTestWrapper extends BaseController
{
    public function testResponse()
    {
        return parent::response();
    }
}