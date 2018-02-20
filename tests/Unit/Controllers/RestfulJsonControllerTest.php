<?php


namespace BaseTree\Tests\Unit\Controllers;


use BaseTree\Controllers\RestfulJsonController;
use BaseTree\Tests\Fake\DummyModel;
use BaseTree\Tests\Fake\DummyResource;
use BaseTree\Tests\Fake\DummyResourceWithValidationsRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

/**
 * @property Mockery\MockInterface resourceMock
 */
class RestfulJsonControllerTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        request()->headers->set('accept', ['application/json']);
    }

    /** @test */
    public function index_should_return_all_info()
    {
        $instance = $this->controller();
        $data = collect(['value1', 'value2', 'value3']);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('model')->andReturn(new DummyModel);
        $this->resourceMock->shouldReceive('index')->andReturn($data);

        $array = $this->arrayResponse($instance->index());
        $this->assertEquals($data->toArray(), $array['data']);
    }

    /** @test */
    public function show_should_return_instance_without_fields_if_not_defined()
    {
        $instance = $this->controller();
        $data = ['name' => 'Test Name'];

        $this->resourceMock->shouldReceive('show')->with(1, [])->andReturn($data);
        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');

        $array = $this->arrayResponse($instance->show(request(), 1));
        $this->assertEquals($data, $array['data']);
    }

    /** @test */
    public function show_should_return_found_instance_with_fields_if_defined_on_request()
    {
        request()->query->set('fields', ['Field1', 'Field2']);
        $instance = $this->controller();
        $data = ['name' => 'Test Name'];

        $this->resourceMock->shouldReceive('show')->with(1, ['Field1', 'Field2'])->andReturn($data);
        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');

        $array = $this->arrayResponse($instance->show(request(), 1));
        $this->assertEquals($data, $array['data']);
    }

    /** @test */
    public function store_can_be_called_without_rules()
    {
        request()->setMethod('POST');
        request()->request->set('name', 'Dummy');
        $instance = $this->controller();

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('model')->andReturn(new DummyModel);
        DB::shouldReceive('beginTransaction')->once();
        $this->resourceMock->shouldReceive('store')->with(['name' => 'Dummy'])->andReturn(['name' => 'Dummy']);
        DB::shouldReceive('commit')->once();

        $array = $this->arrayResponse($instance->store(request()), JsonResponse::HTTP_CREATED);
        $this->assertEquals(['name' => 'Dummy'], $array['data']);
    }

    /**
     * @test
     * @expectedException \Illuminate\Validation\ValidationException
     */
    public function store_should_throw_validation_exception_response_if_rules_does_not_match()
    {
        request()->setMethod('POST');
        request()->request->set('name', 'Dummy');
        $instance = $this->controller(DummyResourceWithValidationsRules::class);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('model')->andReturn(new DummyModel);
        $this->resourceMock->shouldReceive('storeRules')->andReturn(['missing' => 'required']);
        $instance->store(request());
    }

    /** @test */
    public function store_should_validate_the_request_input()
    {
        request()->setMethod('POST');
        request()->request->set('name', 'Dummy');
        $instance = $this->controller(DummyResourceWithValidationsRules::class);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('model')->andReturn(new DummyModel);
        $this->resourceMock->shouldReceive('storeRules')->andReturn(['name' => 'required']);
        DB::shouldReceive('beginTransaction')->once();
        $this->resourceMock->shouldReceive('store')->with(['name' => 'Dummy'])->andReturn(['name' => 'Dummy']);
        DB::shouldReceive('commit')->once();

        $array = $this->arrayResponse($instance->store(request()), JsonResponse::HTTP_CREATED);
        $this->assertEquals(['name' => 'Dummy'], $array['data']);
    }

    /** @test */
    public function update_can_be_called_without_rules()
    {
        request()->setMethod('POST');
        request()->request->set('name', 'Dummy');
        $instance = $this->controller();
        $entityStub = Mockery::mock(DummyModel::class);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('findWithoutRelations')->with(1)->andReturn($entityStub);
        DB::shouldReceive('beginTransaction')->once();
        $this->resourceMock->shouldReceive('updateEntity')->with($entityStub, ['name' => 'Dummy'])
                           ->andReturn(['name' => 'Dummy']);
        DB::shouldReceive('commit')->once();
        $array = $this->arrayResponse($instance->update(request(), 1), JsonResponse::HTTP_OK);
        $this->assertEquals(['name' => 'Dummy'], $array['data']);
    }

    /**
     * @test
     * @expectedException \Illuminate\Validation\ValidationException
     */
    public function update_should_throw_validation_exception_response_if_rules_does_not_match()
    {
        request()->setMethod('POST');
        request()->request->set('name', 'Dummy');
        $instance = $this->controller(DummyResourceWithValidationsRules::class);
        $entityStub = Mockery::mock(DummyModel::class);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('findWithoutRelations')->with(1)->andReturn($entityStub);
        $this->resourceMock->shouldReceive('updateRules')->with(1, request())->andReturn(['missing' => 'required']);
        $instance->update(request(), 1);
    }

    /** @test */
    public function update_should_validate_the_request_input()
    {
        request()->setMethod('POST');
        request()->request->set('name', 'Dummy');
        $instance = $this->controller(DummyResourceWithValidationsRules::class);
        $entityStub = Mockery::mock(DummyModel::class);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('findWithoutRelations')->with(1)->andReturn($entityStub);
        $this->resourceMock->shouldReceive('updateRules')->andReturn(['name' => 'required']);
        DB::shouldReceive('beginTransaction')->once();
        $this->resourceMock->shouldReceive('updateEntity')->with($entityStub, ['name' => 'Dummy'])
                           ->andReturn(['name' => 'Dummy']);
        DB::shouldReceive('commit')->once();
        $array = $this->arrayResponse($instance->update(request(), 1), JsonResponse::HTTP_OK);
        $this->assertEquals(['name' => 'Dummy'], $array['data']);
    }

    /** @test */
    public function destroy_can_be_called_without_rules()
    {
        request()->setMethod('DELETE');
        request()->request->set('name', 'Dummy');
        $instance = $this->controller();
        $entityStub = Mockery::mock(DummyModel::class);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('findWithoutRelations')->with(1)->andReturn($entityStub);
        DB::shouldReceive('beginTransaction')->once();
        $this->resourceMock->shouldReceive('destroy')->with($entityStub, ['name' => 'Dummy'])
                           ->andReturn(true);
        DB::shouldReceive('commit')->once();
        $this->arrayResponse($instance->destroy(request(), 1), JsonResponse::HTTP_OK);
    }

    /**
     * @test
     * @expectedException \Illuminate\Validation\ValidationException
     */
    public function destroy_should_throw_validation_exception_response_if_rules_does_not_match()
    {
        request()->setMethod('DELETE');
        request()->request->set('name', 'Dummy');
        $instance = $this->controller(DummyResourceWithValidationsRules::class);
        $entityStub = Mockery::mock(DummyModel::class);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('findWithoutRelations')->with(1)->andReturn($entityStub);
        $this->resourceMock->shouldReceive('destroyRules')->with(1, request())->andReturn(['missing' => 'required']);
        $instance->destroy(request(), 1);
    }

    /** @test */
    public function destroy_should_validate_the_request_input()
    {
        request()->setMethod('POST');
        request()->request->set('name', 'Dummy');
        $instance = $this->controller(DummyResourceWithValidationsRules::class);
        $entityStub = Mockery::mock(DummyModel::class);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('findWithoutRelations')->with(1)->andReturn($entityStub);
        $this->resourceMock->shouldReceive('destroyRules')->with(1, request())->andReturn(['name' => 'required']);
        DB::shouldReceive('beginTransaction')->once();
        $this->resourceMock->shouldReceive('destroy')->with($entityStub, ['name' => 'Dummy'])
                           ->andReturn(true);
        DB::shouldReceive('commit')->once();
        $this->arrayResponse($instance->destroy(request(), 1), JsonResponse::HTTP_OK);
    }

    protected function controller(
        string $resource = DummyResource::class,
        Request $request = null
    ): RestfulJsonController {

        if (is_null($request)) {
            $request = request();
        }

        $this->resourceMock = Mockery::mock($resource);
        $this->resourceMock->shouldReceive('setRequestOperations')->with($request)->andReturnSelf();

        return new RestfulJsonController($this->resourceMock);
    }

    protected function arrayResponse(JsonResponse $response, int $code = JsonResponse::HTTP_OK)
    {
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($response->getStatusCode(), $code);

        /** @var JsonResponse $response */
        return json_decode($response->content(), true);
    }
}