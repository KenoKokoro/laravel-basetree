<?php


namespace BaseTree\Resources\Contracts\Callbacks;


use BaseTree\Models\BaseTreeModel;

interface CreatedCallback
{
    /**
     * Used as callback when entity is created
     * @param BaseTreeModel $model
     * @param array $dependencyAttributes
     * @param array $attributes
     * @return void
     */
    public function created(BaseTreeModel $model, array $dependencyAttributes = [], array $attributes): void;
}