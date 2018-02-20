<?php


namespace BaseTree\Tests\Fake;


use BaseTree\Models\BaseTreeModel;
use BaseTree\Resources\BaseResource;
use BaseTree\Resources\Contracts\ResourceCallbacks;

class DummyResourceWithCallback extends BaseResource implements ResourceCallbacks
{
    public function __construct(EloquentDummy $repository)
    {
        parent::__construct($repository);
    }

    public function created(BaseTreeModel $model, array $dependencyAttributes = [], array $attributes): void
    {
        $this->repository->update($model, ['fake' => 'value']);
    }

    public function updated(
        BaseTreeModel $before,
        BaseTreeModel $updated = null,
        array $dependencyAttributes = [],
        array $attributes
    ): void {
        $this->repository->update($before, ['fake' => 'value']);
    }

    public function deleted(BaseTreeModel $model, array $attributes): void
    {
        $this->repository->find($attributes['not-fillable-id']);
    }
}