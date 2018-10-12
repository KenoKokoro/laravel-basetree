<?php


namespace BaseTree\Tests\Unit\Controllers\Laravel;


use BaseTree\Controllers\Laravel\BaseController;
use BaseTree\Tests\Fake\Unit\DummyModel;
use BaseTree\Tests\Fake\Wrappers\BaseControllerWrapper;
use BaseTree\Tests\Unit\TestCase;
use Illuminate\Routing\Route;

class BaseControllerTest extends TestCase
{
    /**
     * @var BaseControllerWrapper
     */
    protected $controller;

    public function setUp()
    {
        parent::setUp();
        $this->controller = new BaseControllerWrapper;
    }

    /**
     * @test
     * @expectedException \Illuminate\Auth\Access\AuthorizationException
     */
    public function check_access_should_be_called_if_the_environment_variable_is_set(): void
    {
        $this->config->set('base-tree.authorization', true);
        $this->request->setRouteResolver(function() {
            return new Route([], '', ['uses' => BaseController::class . "@index"]);
        });
        $this->controller->testCheckAccess('view', DummyModel::class, new DummyModel);
    }

    /** @test */
    public function check_access_should_not_be_called_if_the_action_method_is_excluded_for_that_controller(): void
    {
        $this->config->set('base-tree.authorization', true);
        $this->request->setRouteResolver(function() {
            return new Route([], '', ['controller' => BaseController::class . "@index"]);
        });
        $this->controller->setExcluded(['index']);
        $this->controller->testCheckAccess('view', DummyModel::class, new DummyModel);
        $this->assertTrue(true);
    }
}