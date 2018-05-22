<?php


namespace BaseTree\Tests\Fake\Wrappers;


use BaseTree\Controllers\BaseController;

class BaseControllerTestWrapper extends BaseController
{
    public function testResponse()
    {
        return parent::response();
    }

    public function testCheckAccess(string $ability, string $key, $model = null)
    {
        return parent::checkAccess($ability, $key, $model);
    }

    public function setExcluded(array $exclude)
    {
        $this->excludedAuthorization = $exclude;
    }
}