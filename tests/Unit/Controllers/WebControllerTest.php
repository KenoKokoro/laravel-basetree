<?php


namespace BaseTree\Tests\Unit\Controllers;


use BaseTree\Controllers\Laravel\WebController;
use BaseTree\Tests\Fake\Unit\DummyModel;
use BaseTree\Tests\Fake\Unit\DummyResource;
use BaseTree\Tests\Fake\Unit\DummyResourceWithValidationsRules;
use BaseTree\Tests\Fake\Unit\DummyWebController;
use BaseTree\Tests\Unit\TestCase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Mockery;

/**
 * @property Mockery\MockInterface resourceMock
 */
class WebControllerTest extends TestCase
{
    /** @test */
    public function web_index_should_return_view_with_all_entries(): void
    {
        $instance = $this->controller('index');
        $data = collect(['value1', 'value2', 'value3']);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('model')->andReturn(new DummyModel);
        $this->resourceMock->shouldReceive('index')->andReturn($data);
        View::shouldReceive('make')->with('dummy.index', ['entities' => $data], [])->once();

        $instance->index($this->request);
    }

    /** @test */
    public function web_show_should_return_view_with_single_entity(): void
    {
        $instance = $this->controller('show');
        $data = ['name' => 'Test Name'];

        $this->resourceMock->shouldReceive('show')->with(1, [])->andReturn($data);
        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        View::shouldReceive('make')->with('dummy.show', ['entity' => $data], [])->once();

        $instance->show($this->request, 1);
    }

    /** @test */
    public function web_show_should_return_found_instance_with_fields_if_defined_on_request(): void
    {
        $this->request->query->set('fields', ['Field1', 'Field2']);
        $instance = $this->controller('show');
        $data = ['name' => 'Test Name'];

        $this->resourceMock->shouldReceive('show')->with(1, ['Field1', 'Field2'])->andReturn($data);
        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        View::shouldReceive('make')->with('dummy.show', ['entity' => $data], [])->once();

        $instance->show($this->request, 1);
    }

    /** @test */
    public function create_should_show_the_view_without_any_data(): void
    {
        $instance = $this->controller('create');

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('model')->andReturn(new DummyModel);
        View::shouldReceive('make')->with('dummy.create', [], [])->once();

        $instance->create($this->request);
    }

    /** @test */
    public function web_store_can_be_called_without_validation_rules(): void
    {
        $this->request->setMethod('POST');
        $this->request->request->set('name', 'Dummy');
        $instance = $this->controller('store');

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('model')->andReturn(new DummyModel);
        DB::shouldReceive('beginTransaction')->once();
        $this->resourceMock->shouldReceive('store')->with(['name' => 'Dummy'])->andReturn(['name' => 'Dummy']);
        DB::shouldReceive('commit')->once();

        $response = $instance->store($this->request);
        $this->assertSession('success', 'Successfully created.');
        $this->assertEquals('http://localhost/dummies/index', $response->getTargetUrl());
    }

    /**
     * @test
     * @expectedException \Illuminate\Validation\ValidationException
     */
    public function web_store_should_throw_validation_exception_response_if_rules_does_not_match(): void
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
    public function web_store_should_validate_the_request_input(): void
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

        $response = $instance->store($this->request);
        $this->assertSession('success', 'Successfully created.');
        $this->assertEquals('http://localhost/dummies/index', $response->getTargetUrl());
    }

    /** @test */
    public function web_edit_should_return_view_with_single_entity(): void
    {
        $instance = $this->controller('edit');
        $data = ['name' => 'Test Name'];

        $this->resourceMock->shouldReceive('show')->with(1, [])->andReturn($data);
        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        View::shouldReceive('make')->with('dummy.edit', ['entity' => $data], [])->once();

        $instance->edit($this->request, 1);
    }

    /** @test */
    public function web_edit_should_return_found_instance_with_fields_if_defined_on_request(): void
    {
        $this->request->query->set('fields', ['Field1', 'Field2']);
        $instance = $this->controller('edit');
        $data = ['name' => 'Test Name'];

        $this->resourceMock->shouldReceive('show')->with(1, ['Field1', 'Field2'])->andReturn($data);
        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        View::shouldReceive('make')->with('dummy.edit', ['entity' => $data], [])->once();

        $instance->edit($this->request, 1);
    }

    /** @test */
    public function web_update_can_be_called_without_rules(): void
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

        $response = $instance->update($this->request, 1);
        $this->assertSession('success', 'Successfully updated.');
        $this->assertEquals('http://localhost/dummies/index', $response->getTargetUrl());
    }

    /**
     * @test
     * @expectedException \Illuminate\Validation\ValidationException
     */
    public function web_update_should_throw_validation_exception_response_if_rules_does_not_match(): void
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
    public function web_update_should_validate_the_request_input(): void
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

        $response = $instance->update($this->request, 1);
        $this->assertSession('success', 'Successfully updated.');
        $this->assertEquals('http://localhost/dummies/index', $response->getTargetUrl());
    }

    /** @test */
    public function web_destroy_can_be_called_without_rules(): void
    {
        $this->request->setMethod('DELETE');
        $this->request->request->set('name', 'Dummy');
        $instance = $this->controller('destroy');
        $entityStub = Mockery::mock(DummyModel::class);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('findWithoutRelations')->with(1)->andReturn($entityStub);
        DB::shouldReceive('beginTransaction')->once();
        $this->resourceMock->shouldReceive('destroy')->with($entityStub, ['name' => 'Dummy'])
                           ->andReturn(true);
        DB::shouldReceive('commit')->once();

        $response = $instance->destroy($this->request, 1);
        $this->assertSession('success', 'Successfully deleted.');
        $this->assertEquals('http://localhost/dummies/index', $response->getTargetUrl());
    }

    /**
     * @test
     * @expectedException \Illuminate\Validation\ValidationException
     */
    public function web_destroy_should_throw_validation_exception_response_if_rules_does_not_match(): void
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
    public function web_destroy_should_validate_the_request_input(): void
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

        $response = $instance->destroy($this->request, 1);
        $this->assertSession('success', 'Successfully deleted.');
        $this->assertEquals('http://localhost/dummies/index', $response->getTargetUrl());
    }

    protected function controller(
        string $method,
        string $resource = DummyResource::class,
        Request $request = null
    ): DummyWebController {

        if (is_null($request)) {
            $request = $this->request;
            $request->setRouteResolver(function() use ($method) {
                return new LaravelRoute([], '', ['controller' => WebController::class . "@{$method}"]);
            });
        }

        $this->resourceMock = Mockery::mock($resource);
        $this->resourceMock->shouldReceive('setRequestOperations')->with($request)->andReturnSelf();

        return new DummyWebController($this->resourceMock);
    }

    protected function assertSession(string $status, string $message): void
    {
        $session = session()->all();
        $this->assertEquals($session['status'], $status);
        $this->assertEquals($session['message'], $message);
        $this->assertEquals(array_get($session, '_flash.new'), ['status', 'message']);
    }
}