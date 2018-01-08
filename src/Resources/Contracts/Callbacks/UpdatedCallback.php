<?php


namespace BaseTree\Resources\Contracts\Callbacks;


use BaseTree\Models\BaseTreeModel;

interface UpdatedCallback
{
    /**
     * Used as callback when entity is updated
     * @param BaseTreeModel $before
     * @param BaseTreeModel|null $updated
     * @param array $dependencyAttributes
     * @param array $attributes
     * @return void
     */
    public function updated(
        BaseTreeModel $before,
        BaseTreeModel $updated = null,
        array $dependencyAttributes = [],
        array $attributes
    ): void;
}