<?php


namespace BaseTree\Controllers;


use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

trait BaseControllerMethods
{
    protected $permission;

    /**
     * Used to exclude methods from being authorized on the restful resource
     * @var array
     */
    protected $excludedAuthorization = [];

    /**
     * @param string $ability
     * @param string $key
     * @param array  $extra
     * @return void
     */
    protected function checkAccess(string $ability, string $key, ...$extra): void
    {
        $method = $this->getActionMethod(request());
        if (config('base-tree.authorization') and ! in_array($method, $this->excludedAuthorization)) {
            $this->authorize($ability, array_merge([$key, $this->permission], $extra));
        }
    }
}