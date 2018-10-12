<?php


namespace BaseTree\Tests\Fake\Wrappers;


use BaseTree\Controllers\Laravel\BaseController;

class BaseControllerWrapper extends BaseController
{
    /**
     * @param string $ability
     * @param string $key
     * @param null   $model
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function testCheckAccess(string $ability, string $key, $model = null): void
    {
        parent::checkAccess($ability, $key, $model);
    }

    /**
     * @param array $exclude
     */
    public function setExcluded(array $exclude): void
    {
        $this->excludedAuthorization = $exclude;
    }
}