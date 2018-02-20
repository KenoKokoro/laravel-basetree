<?php


namespace BaseTree\Tests\Unit\Controllers;


use BaseTree\Responses\HttpResponse;
use BaseTree\Responses\JsonResponse;
use BaseTree\Tests\Fake\DummyModel;
use BaseTree\Tests\Fake\Wrappers\BaseControllerTestWrapper;
use Tests\TestCase;

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
    public function response_should_be_json_if_requested()
    {
        request()->headers->set('accept', ['application/json']);

        $response = $this->controller->testResponse();

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    /** @test */
    public function default_response_should_be_http_response()
    {
        $response = $this->controller->testResponse();

        $this->assertInstanceOf(HttpResponse::class, $response);
    }

    /**
     * @test
     * @expectedException \Illuminate\Auth\Access\AuthorizationException
     */
    public function check_access_should_be_called_if_the_environment_variable_is_set()
    {
        config()->set('base-tree.authorization', true);
        $this->controller->testCheckAccess('view', DummyModel::class, new DummyModel);
    }
}