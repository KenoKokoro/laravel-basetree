<?php


namespace BaseTree\Tests\Fake\Integration;


use BaseTree\Controllers\RestfulJsonController;

class UsersController extends RestfulJsonController
{
    public function __construct(UserResource $resource)
    {
        parent::__construct($resource);
    }
}