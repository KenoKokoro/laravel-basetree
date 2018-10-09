<?php


namespace BaseTree\Tests\Fake\Integration;


use BaseTree\Eloquent\BaseEloquent;

class EloquentUser extends BaseEloquent implements UserRepository
{
    public function __construct(UserModel $model)
    {
        parent::__construct($model);
    }
}