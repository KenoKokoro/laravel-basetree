<?php


namespace BaseTree\Tests\Fake;


use BaseTree\Resources\BaseResource;

class DummyResource extends BaseResource
{
    protected $excludedOnUpdate = ['attr3'];

    protected $excludeOnUpdateIfEmpty = ['attr4', 'attr5', 'attr6', 'attr7'];

    public function __construct(EloquentDummy $repository)
    {
        parent::__construct($repository);
    }
}