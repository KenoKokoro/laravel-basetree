<?php


namespace BaseTree\Resources\Contracts;


use BaseTree\Models\Model;

interface ResourceCallbacks
{
    /**
     * Used as callback when entity is created
     * @param Model $model
     * @param array $dependencyAttributes
     * @param array $attributes
     * @return void
     */
    public function created(Model $model, array $dependencyAttributes = [], array $attributes);

    /**
     * Used as callback when entity is updated
     * @param Model $before
     * @param Model|null $updated
     * @param array $dependencyAttributes
     * @param array $attributes
     * @return void
     */
    public function updated(Model $before, Model $updated = null, array $dependencyAttributes = [], array $attributes);
}