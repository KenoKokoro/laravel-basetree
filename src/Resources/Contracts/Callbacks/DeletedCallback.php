<?php


namespace BaseTree\Resources\Contracts\Callbacks;


use BaseTree\Models\BaseTreeModel;

interface DeletedCallback
{
    /**
     * Used as callback when entity is created
     * @param BaseTreeModel $model
     * @param array $attributes
     * @return void
     */
    public function deleted(BaseTreeModel $model, array $attributes): void;
}