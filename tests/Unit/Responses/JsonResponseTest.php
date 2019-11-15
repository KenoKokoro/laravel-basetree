<?php


namespace BaseTree\Tests\Unit\Responses;


use BaseTree\Providers\BaseTreeServiceProvider;
use BaseTree\Responses\JsonResponse;
use BaseTree\Tests\Fake\Unit\ClientLoggerStub;
use BaseTree\Tests\Unit\TestCase;
use Illuminate\Http\JsonResponse as LaravelJsonResponse;
use Illuminate\Support\Facades\App;
use Mockery as m;

class JsonResponseTest extends TestCase
{
    /**
     * @var JsonResponse
     */
    protected $instance;

    public function setUp(): void
    {
        parent::setUp();
        $this->instance = new JsonResponse;
    }

    /** @test */
    public function json_response_is_child_of_laravel_json_response(): void
    {
        $this->assertTrue($this->instance instanceof LaravelJsonResponse);
    }

    /** @test */
    public function success_default_response(): void
    {
        $success = $this->instance->success();
        $response = $success->getData();

        $this->assertEquals(JsonResponse::HTTP_OK, $success->getStatusCode());
        $this->assertCount(1, (array)$response);
        $this->assertEquals($response->message, 'Successfully executed.');
    }

    /** @test */
    public function success_modify_default_message(): void
    {
        $response = $this->instance->success('OK')->getData();
        $this->assertEquals($response->message, 'OK');
    }

    /** @test */
    public function success_append_new_key_to_response(): void
    {
        $response = $this->instance->success('', ['key' => 'value'])->getData();
        $this->assertCount(2, (array)$response);
        $this->assertNotEmpty($response->key);
        $this->assertEquals($response->key, 'value');
    }

    /** @test */
    public function created_default_response(): void
    {
        $created = $this->instance->created();
        $response = $created->getData();

        $this->assertEquals(JsonResponse::HTTP_CREATED, $created->getStatusCode());
        $this->assertCount(1, (array)$response);
        $this->assertEquals($response->message, 'Successfully created.');
    }

    /** @test */
    public function created_modify_default_message(): void
    {
        $response = $this->instance->created('OK')->getData();
        $this->assertEquals($response->message, 'OK');
    }

    /** @test */
    public function created_append_new_key_to_response(): void
    {
        $response = $this->instance->created('', ['key' => 'value'])->getData();
        $this->assertCount(2, (array)$response);
        $this->assertNotEmpty($response->key);
        $this->assertEquals($response->key, 'value');
    }

    /** @test */
    public function forbidden_default_response(): void
    {
        $forbidden = $this->instance->forbidden();
        $response = $forbidden->getData();

        $this->assertEquals(JsonResponse::HTTP_FORBIDDEN, $forbidden->getStatusCode());
        $this->assertCount(1, (array)$response);
        $this->assertEquals($response->message, 'Forbidden.');
    }

    /** @test */
    public function forbidden_modify_default_message(): void
    {
        $response = $this->instance->forbidden('OK')->getData();
        $this->assertEquals($response->message, 'OK');
    }

    /** @test */
    public function unauthorized_default_response(): void
    {
        $unauthorized = $this->instance->unauthorized();
        $response = $unauthorized->getData();

        $this->assertEquals(JsonResponse::HTTP_UNAUTHORIZED, $unauthorized->getStatusCode());
        $this->assertCount(1, (array)$response);
        $this->assertEquals($response->message, 'Unauthorized.');
    }

    /** @test */
    public function unauthorized_modify_default_message(): void
    {
        $response = $this->instance->forbidden('OK')->getData();
        $this->assertEquals($response->message, 'OK');
    }

    /** @test */
    public function unprocessed_entity_default_response(): void
    {
        $unprocessedEntity = $this->instance->unprocessableEntity();
        $response = $unprocessedEntity->getData();

        $this->assertEquals(JsonResponse::HTTP_UNPROCESSABLE_ENTITY, $unprocessedEntity->getStatusCode());
        $this->assertCount(1, (array)$response);
        $this->assertEquals($response->message, 'Unprocessable entity.');
    }

    /** @test */
    public function unprocessable_entity_modify_default_message(): void
    {
        $response = $this->instance->unprocessableEntity('OK')->getData();
        $this->assertEquals($response->message, 'OK');
    }

    /** @test */
    public function unprocessable_entity_append_new_key_to_response(): void
    {
        $response = $this->instance->unprocessableEntity('', ['key' => 'value'])->getData();
        $this->assertCount(2, (array)$response);
        $this->assertNotEmpty($response->key);
        $this->assertEquals($response->key, 'value');
    }

    /** @test */
    public function not_found_default_response(): void
    {
        $notFound = $this->instance->notFound();
        $response = $notFound->getData();

        $this->assertEquals(JsonResponse::HTTP_NOT_FOUND, $notFound->getStatusCode());
        $this->assertCount(1, (array)$response);
        $this->assertEquals($response->message, 'Not found.');
    }

    /** @test */
    public function not_found_modify_default_message(): void
    {
        $response = $this->instance->notFound('OK')->getData();
        $this->assertEquals($response->message, 'OK');
    }

    /** @test */
    public function not_found_entity_append_new_key_to_response(): void
    {
        $response = $this->instance->notFound('', ['key' => 'value'])->getData();
        $this->assertCount(2, (array)$response);
        $this->assertNotEmpty($response->key);
        $this->assertEquals($response->key, 'value');
    }

    /** @test */
    public function internal_error_default_response(): void
    {
        $internalError = $this->instance->internalError();
        $response = $internalError->getData();

        $this->assertEquals(JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $internalError->getStatusCode());
        $this->assertCount(1, (array)$response);
        $this->assertEquals($response->message, 'Internal error.');
    }

    /** @test */
    public function internal_error_modify_default_message(): void
    {
        $response = $this->instance->internalError('OK')->getData();
        $this->assertEquals($response->message, 'OK');
    }

    /** @test */
    public function internal_error_entity_append_new_key_to_response(): void
    {
        $response = $this->instance->internalError('', ['key' => 'value'])->getData();
        $this->assertCount(2, (array)$response);
        $this->assertNotEmpty($response->key);
        $this->assertEquals($response->key, 'value');
    }

    /** @test */
    public function json_response_facade_is_registered(): void
    {
        $this->app->register(BaseTreeServiceProvider::class);
        $this->assertInstanceOf(JsonResponse::class, $this->app->make('basetree.response.json'));
    }

    /** @test */
    public function response_should_try_to_call_the_logger_if_config_enables_it(): void
    {
        $this->config->set('base-tree.log', true);
        $loggerStub = m::mock(new ClientLoggerStub);
        App::shouldReceive('make')->with('BaseTree\Modules\Log\ClientLogger')->andReturn($loggerStub);
        $loggerStub->shouldReceive('writeIncoming');

        $this->instance->success();
    }
}
