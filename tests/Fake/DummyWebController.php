<?php


namespace BaseTree\Tests\Fake;


use BaseTree\Controllers\WebController;

class DummyWebController extends WebController
{
    protected $viewPath = 'dummy';

    protected $redirectTo = '/dummies/index';
}