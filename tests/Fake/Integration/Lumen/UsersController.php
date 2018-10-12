<?php


namespace BaseTree\Tests\Fake\Integration\Lumen;


use BaseTree\Controllers\Lumen\JsonController;
use BaseTree\Tests\Fake\Integration\UserResource;

class UsersController extends JsonController
{
    public function __construct(UserResource $resource)
    {
        parent::__construct($resource);
    }
}