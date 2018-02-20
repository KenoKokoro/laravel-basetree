<?php


namespace BaseTree\Tests\Fake;

use BaseTree\Eloquent\BaseEloquent;
use BaseTree\Eloquent\RepositoryInterface;

class EloquentDummyWithConstraints extends BaseEloquent implements RepositoryInterface
{
    protected $relations = ['Relation1'];

    protected $defaultConstraints = [['column-default', '=', 'value-default']];

    public function __construct(DummyModel $model)
    {
        parent::__construct($model);
    }
}

