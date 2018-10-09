<?php


namespace BaseTree\Tests\Fake\Unit;


use BaseTree\Controllers\WebController;

class DummyWebController extends WebController
{
    protected $viewPath = 'dummy';

    protected $redirectTo = '/dummies/index';
}