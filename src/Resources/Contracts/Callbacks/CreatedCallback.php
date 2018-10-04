<?php


namespace BaseTree\Resources\Contracts\Callbacks;


use BaseTree\Models\BaseTreeModel;

interface CreatedCallback
{
    /**
     * Used as callback when entity is created
     * @param BaseTreeModel $model
     * @param array         $attributes
     * @param array         $dependencyAttributes
     * @return void
     */
    public function created(BaseTreeModel $model, array $attributes, array $dependencyAttributes = []): void;
}