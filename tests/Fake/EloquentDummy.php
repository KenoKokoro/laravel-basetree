<?php


namespace BaseTree\Tests\Fake;


use BaseTree\Eloquent\BaseEloquent;
use BaseTree\Eloquent\RepositoryInterface;

class EloquentDummy extends BaseEloquent implements RepositoryInterface
{
    protected $relations = ['Relation1'];

    public function __construct(DummyModel $model)
    {
        parent::__construct($model);
    }
}