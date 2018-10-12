<?php


namespace BaseTree\Tests\Fake\Integration\Laravel;


use BaseTree\Controllers\Laravel\JsonController;
use BaseTree\Tests\Fake\Integration\UserResource;

class UsersController extends JsonController
{
    public function __construct(UserResource $resource)
    {
        parent::__construct($resource);
    }
}