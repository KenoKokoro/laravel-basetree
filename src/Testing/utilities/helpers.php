<?php

use BaseTree\Models\BaseTreeModel;
use Illuminate\Database\Eloquent\Collection;

if ( ! function_exists('create')) {
    /**
     * Wrapper for the factory to make a single instance or collection of instances from the laravel database factory
     * and store it to the database
     * @param string       $class
     * @param integer|null $quantity
     * @param array        $attributes
     * @return BaseTreeModel|Collection
     */
    function create(string $class, $quantity = null, $attributes = [])
    {
        return factory($class, $quantity)->create($attributes);
    }
}

if ( ! function_exists('make')) {
    /**
     * Wrapper for the factory to make a single instance or collection of instances from the laravel database factory
     * @param string       $class
     * @param integer|null $quantity
     * @param array        $attributes
     * @return BaseTreeModel|Collection
     */
    function make(string $class, $quantity = null, $attributes = [])
    {
        return factory($class, $quantity)->make($attributes);
    }
}
