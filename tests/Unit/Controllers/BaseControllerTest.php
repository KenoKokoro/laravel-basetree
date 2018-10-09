<?php


namespace BaseTree\Tests\Unit\Controllers;


use BaseTree\Controllers\BaseController;
use BaseTree\Responses\HttpResponse;
use BaseTree\Responses\JsonResponse;
use BaseTree\Tests\Fake\Unit\DummyModel;
use BaseTree\Tests\Fake\Wrappers\BaseControllerTestWrapper;
use BaseTree\Tests\Unit\TestCase;
use Illuminate\Routing\Route;

class BaseControllerTest extends TestCase
{
    /**
     * @var BaseControllerTestWrapper
     */
    protected $controller;

    public function setUp()
    {
        parent::setUp();
        $this->controller = new BaseControllerTestWrapper;
    }

    /** @test */
    public function response_should_be_json_if_requested(): void
    {
        $this->request->headers->set('accept', ['application/json']);

        $response = $this->controller->testResponse();

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /** @test */
    public function default_response_should_be_http_response(): void
    {
        $response = $this->controller->testResponse();

        $this->assertInstanceOf(HttpResponse::class, $response);
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