<?php


namespace BaseTree\Tests\Unit\Controllers;


use BaseTree\Responses\HttpResponse;
use BaseTree\Responses\JsonResponse;
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
}