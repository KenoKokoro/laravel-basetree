<?php


namespace BaseTree\Tests\Unit\Controllers;


use BaseTree\Controllers\Laravel\JsonController;
use BaseTree\Tests\Fake\Unit\DummyModel;
use BaseTree\Tests\Fake\Unit\DummyResource;
use BaseTree\Tests\Fake\Unit\DummyResourceWithValidationsRules;
use BaseTree\Tests\Unit\TestCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mockery;

/**
 * @property Mockery\MockInterface resourceMock
 */
class LaravelJsonControllerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->request->headers->set('accept', ['application/json']);
    }

    /** @test */
    public function index_should_return_all_info(): void
    {
        $instance = $this->controller('index');
        $data = Collection::make(['value1', 'value2', 'value3']);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('model')->andReturn(new DummyModel);
        $this->resourceMock->shouldReceive('index')->andReturn($data);

        $array = $this->arrayResponse($instance->index());
        $this->assertEquals($data->toArray(), $array['data']);
    }

    /** @test */
    public function show_should_return_instance_without_fields_if_not_defined(): void
    {
        $instance = $this->controller('show');
        $data = ['name' => 'Test Name'];

        $this->resourceMock->shouldReceive('show')->with(1, [])->andReturn($data);
        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');

        $array = $this->arrayResponse($instance->show($this->request, 1));
        $this->assertEquals($data, $array['data']);
    }

    /** @test */
    public function show_should_return_found_instance_with_fields_if_defined_on_request(): void
    {
        $this->request->query->set('fields', ['Field1', 'Field2']);
        $instance = $this->controller('show');
        $data = ['name' => 'Test Name'];

        $this->resourceMock->shouldReceive('show')->with(1, ['Field1', 'Field2'])->andReturn($data);
        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');

        $array = $this->arrayResponse($instance->show($this->request, 1));
        $this->assertEquals($data, $array['data']);
    }

    /** @test */
    public function store_can_be_called_without_validation_rules(): void
    {
        $this->request->setMethod('POST');
        $this->request->request->set('name', 'Dummy');
        $instance = $this->controller('store');

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('model')->andReturn(new DummyModel);
        DB::shouldReceive('beginTransaction')->once();
        $this->resourceMock->shouldReceive('store')->with(['name' => 'Dummy'])->andReturn(['name' => 'Dummy']);
        DB::shouldReceive('commit')->once();

        $array = $this->arrayResponse($instance->store($this->request), JsonResponse::HTTP_CREATED);
        $this->assertEquals(['name' => 'Dummy'], $array['data']);
    }

    /**
     * @test
     * @expectedException \Illuminate\Validation\ValidationException
     */
    public function store_should_throw_validation_exception_response_if_rules_does_not_match(): void
    {
        $this->request->setMethod('POST');
        $this->request->request->set('name', 'Dummy');
        $instance = $this->controller('store', DummyResourceWithValidationsRules::class);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('model')->andReturn(new DummyModel);
        $this->resourceMock->shouldReceive('storeRules')->andReturn(['missing' => 'required']);
        $instance->store($this->request);
    }

    /** @test */
    public function store_should_validate_the_request_input(): void
    {
        $this->request->setMethod('POST');
        $this->request->request->set('name', 'Dummy');
        $instance = $this->controller('store', DummyResourceWithValidationsRules::class);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('model')->andReturn(new DummyModel);
        $this->resourceMock->shouldReceive('storeRules')->andReturn(['name' => 'required']);
        DB::shouldReceive('beginTransaction')->once();
        $this->resourceMock->shouldReceive('store')->with(['name' => 'Dummy'])->andReturn(['name' => 'Dummy']);
        DB::shouldReceive('commit')->once();

        $array = $this->arrayResponse($instance->store($this->request), JsonResponse::HTTP_CREATED);
        $this->assertEquals(['name' => 'Dummy'], $array['data']);
    }

    /** @test */
    public function update_can_be_called_without_rules(): void
    {
        $this->request->setMethod('POST');
        $this->request->request->set('name', 'Dummy');
        $instance = $this->controller('update');
        $entityStub = Mockery::mock(DummyModel::class);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('findWithoutRelations')->with(1)->andReturn($entityStub);
        DB::shouldReceive('beginTransaction')->once();
        $this->resourceMock->shouldReceive('updateEntity')->with($entityStub, ['name' => 'Dummy'])
                           ->andReturn(['name' => 'Dummy']);
        DB::shouldReceive('commit')->once();
        $array = $this->arrayResponse($instance->update($this->request, 1), JsonResponse::HTTP_OK);
        $this->assertEquals(['name' => 'Dummy'], $array['data']);
    }

    /**
     * @test
     * @expectedException \Illuminate\Validation\ValidationException
     */
    public function update_should_throw_validation_exception_response_if_rules_does_not_match(): void
    {
        $this->request->setMethod('POST');
        $this->request->request->set('name', 'Dummy');
        $instance = $this->controller('update', DummyResourceWithValidationsRules::class);
        $entityStub = Mockery::mock(DummyModel::class);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('findWithoutRelations')->with(1)->andReturn($entityStub);
        $this->resourceMock->shouldReceive('updateRules')->with(1, $this->request)
                           ->andReturn(['missing' => 'required']);
        $instance->update($this->request, 1);
    }

    /** @test */
    public function update_should_validate_the_request_input(): void
    {
        $this->request->setMethod('POST');
        $this->request->request->set('name', 'Dummy');
        $instance = $this->controller('update', DummyResourceWithValidationsRules::class);
        $entityStub = Mockery::mock(DummyModel::class);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('findWithoutRelations')->with(1)->andReturn($entityStub);
        $this->resourceMock->shouldReceive('updateRules')->andReturn(['name' => 'required']);
        DB::shouldReceive('beginTransaction')->once();
        $this->resourceMock->shouldReceive('updateEntity')->with($entityStub, ['name' => 'Dummy'])
                           ->andReturn(['name' => 'Dummy']);
        DB::shouldReceive('commit')->once();
        $array = $this->arrayResponse($instance->update($this->request, 1), JsonResponse::HTTP_OK);
        $this->assertEquals(['name' => 'Dummy'], $array['data']);
    }

    /** @test */
    public function destroy_can_be_called_without_rules(): void
    {
        $this->request->setMethod('DELETE');
        $this->request->request->set('name', 'Dummy');
        $instance = $this->controller('destroy');
        $entityStub = Mockery::mock(DummyModel::class);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('findWithoutRelations')->with(1)->andReturn($entityStub);
        DB::shouldReceive('beginTransaction')->once();
        $this->resourceMock->shouldReceive('destroy')->with($entityStub, ['name' => 'Dummy'])->andReturn(true);
        DB::shouldReceive('commit')->once();
        $this->arrayResponse($instance->destroy($this->request, 1), JsonResponse::HTTP_OK);
    }

    /**
     * @test
     * @expectedException \Illuminate\Validation\ValidationException
     */
    public function destroy_should_throw_validation_exception_response_if_rules_does_not_match(): void
    {
        $this->request->setMethod('DELETE');
        $this->request->request->set('name', 'Dummy');
        $instance = $this->controller('destroy', DummyResourceWithValidationsRules::class);
        $entityStub = Mockery::mock(DummyModel::class);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('findWithoutRelations')->with(1)->andReturn($entityStub);
        $this->resourceMock->shouldReceive('destroyRules')->with(1, $this->request)
                           ->andReturn(['missing' => 'required']);
        $instance->destroy($this->request, 1);
    }

    /** @test */
    public function destroy_should_validate_the_request_input(): void
    {
        $this->request->setMethod('POST');
        $this->request->request->set('name', 'Dummy');
        $instance = $this->controller('destroy', DummyResourceWithValidationsRules::class);
        $entityStub = Mockery::mock(DummyModel::class);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('findWithoutRelations')->with(1)->andReturn($entityStub);
        $this->resourceMock->shouldReceive('destroyRules')->with(1, $this->request)->andReturn(['name' => 'required']);
        DB::shouldReceive('beginTransaction')->once();
        $this->resourceMock->shouldReceive('destroy')->with($entityStub, ['name' => 'Dummy'])
                           ->andReturn(true);
        DB::shouldReceive('commit')->once();
        $this->arrayResponse($instance->destroy($this->request, 1), JsonResponse::HTTP_OK);
    }

    protected function controller(
        string $method,
        string $resource = DummyResource::class,
        Request $request = null
    ): JsonController {

        if (is_null($request)) {
            $request = $this->request;
            $request->setRouteResolver(function() use ($method) {
                return new LaravelRoute([], '', ['controller' => JsonController::class . "@{$method}"]);
            });
        }

        $this->resourceMock = Mockery::mock($resource);
        $this->resourceMock->shouldReceive('setRequestOperations')->with($request)->andReturnSelf();

        return new JsonController($this->resourceMock);
    }

    protected function arrayResponse(
        JsonResponse $response,
        int $code = JsonResponse::HTTP_OK
    ): array {
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($response->getStatusCode(), $code);

        /** @var JsonResponse $response */
        return json_decode($response->content(), true);
    }
}