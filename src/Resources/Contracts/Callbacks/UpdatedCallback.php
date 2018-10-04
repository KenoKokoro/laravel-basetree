<?php


namespace BaseTree\Resources\Contracts\Callbacks;


use BaseTree\Models\BaseTreeModel;

interface UpdatedCallback
{
    /**
     * Used as callback when entity is updated
     * @param BaseTreeModel      $before
     * @param BaseTreeModel|null $updated
     * @param array              $attributes
     * @param array              $dependencyAttributes
     * @return void
     */
    public function updated(
        BaseTreeModel $before,
        ?BaseTreeModel $updated,
        array $attributes,
        array $dependencyAttributes = []
    ): void;
}