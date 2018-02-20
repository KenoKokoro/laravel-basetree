<?php


namespace BaseTree\Tests\Fake;


use BaseTree\Resources\BaseResource;

class DummyResource extends BaseResource
{
    protected $excludedOnUpdate = ['attr3'];

    public function __construct(EloquentDummy $repository)
    {
        parent::__construct($repository);
    }
}