<?php


namespace BaseTree\Tests\Unit\Controllers;


use BaseTree\Responses\Facades\HttpResponse;
use BaseTree\Tests\Fake\DummyModel;
use BaseTree\Tests\Fake\DummyResource;
use BaseTree\Tests\Fake\DummyResourceWithValidationsRules;
use BaseTree\Tests\Fake\DummyWebController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Mockery;
use Tests\TestCase;

/**
 * @property Mockery\MockInterface resourceMock
 */
class WebControllerTest extends TestCase
{
    /** @test */
    public function web_index_should_return_view_with_all_entries()
    {
        $instance = $this->controller();
        $data = collect(['value1', 'value2', 'value3']);

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('model')->andReturn(new DummyModel);
        $this->resourceMock->shouldReceive('index')->andReturn($data);
        View::shouldReceive('make')->with('dummy.index', ['entities' => $data], [])->once();

        $instance->index(request());
    }

    /** @test */
    public function web_show_should_return_view_with_single_entity()
    {
        $instance = $this->controller();
        $data = ['name' => 'Test Name'];

        $this->resourceMock->shouldReceive('show')->with(1, [])->andReturn($data);
        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        View::shouldReceive('make')->with('dummy.show', ['entity' => $data], [])->once();

        $instance->show(request(), 1);
    }

    /** @test */
    public function web_show_should_return_found_instance_with_fields_if_defined_on_request()
    {
        request()->query->set('fields', ['Field1', 'Field2']);
        $instance = $this->controller();
        $data = ['name' => 'Test Name'];

        $this->resourceMock->shouldReceive('show')->with(1, ['Field1', 'Field2'])->andReturn($data);
        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        View::shouldReceive('make')->with('dummy.show', ['entity' => $data], [])->once();

        $instance->show(request(), 1);
    }

    /** @test */
    public function create_should_show_the_view_without_any_data()
    {
        $instance = $this->controller();

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('model')->andReturn(new DummyModel);
        View::shouldReceive('make')->with('dummy.create', [], [])->once();

        $instance->create(request());
    }

    /** @test */
    public function web_store_can_be_called_without_validation_rules()
    {
        request()->setMethod('POST');
        request()->request->set('name', 'Dummy');
        $instance = $this->controller();

        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        $this->resourceMock->shouldReceive('model')->andReturn(new DummyModel);
        DB::shouldReceive('beginTransaction')->once();
        $this->resourceMock->shouldReceive('store')->with(['name' => 'Dummy'])->andReturn(['name' => 'Dummy']);
        DB::shouldReceive('commit')->once();

        $response = $instance->store(request());
        $this->assertSession('success', 'Successfully created.');
        $this->assertEquals('http://localhost/dummies/index', $response->getTargetUrl());
    }

    /**
     * @test
     * @expectedException \Illuminate\Validation\ValidationException
     */
    public function web_store_should_throw_validation_exception_response_if_rules_does_not_match()
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
    public function web_store_should_validate_the_request_input()
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

        $response = $instance->store(request());
        $this->assertSession('success', 'Successfully created.');
        $this->assertEquals('http://localhost/dummies/index', $response->getTargetUrl());
    }

    /** @test */
    public function web_edit_should_return_view_with_single_entity()
    {
        $instance = $this->controller();
        $data = ['name' => 'Test Name'];

        $this->resourceMock->shouldReceive('show')->with(1, [])->andReturn($data);
        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        View::shouldReceive('make')->with('dummy.edit', ['entity' => $data], [])->once();

        $instance->edit(request(), 1);
    }

    /** @test */
    public function web_edit_should_return_found_instance_with_fields_if_defined_on_request()
    {
        request()->query->set('fields', ['Field1', 'Field2']);
        $instance = $this->controller();
        $data = ['name' => 'Test Name'];

        $this->resourceMock->shouldReceive('show')->with(1, ['Field1', 'Field2'])->andReturn($data);
        $this->resourceMock->shouldReceive('authorizationKey')->andReturn('Model');
        View::shouldReceive('make')->with('dummy.edit', ['entity' => $data], [])->once();

        $instance->edit(request(), 1);
    }

    /** @test */
    public function web_update_can_be_called_without_rules()
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

        $response = $instance->update(request(), 1);
        $this->assertSession('success', 'Successfully updated.');
        $this->assertEquals('http://localhost/dummies/index', $response->getTargetUrl());
    }

    /**
     * @test
     * @expectedException \Illuminate\Validation\ValidationException
     */
    public function web_update_should_throw_validation_exception_response_if_rules_does_not_match()
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
    public function web_update_should_validate_the_request_input()
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

        $response = $instance->update(request(), 1);
        $this->assertSession('success', 'Successfully updated.');
        $this->assertEquals('http://localhost/dummies/index', $response->getTargetUrl());
    }

    /** @test */
    public function web_destroy_can_be_called_without_rules()
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

        $response = $instance->destroy(request(), 1);
        $this->assertSession('success', 'Successfully deleted.');
        $this->assertEquals('http://localhost/dummies/index', $response->getTargetUrl());
    }

    /**
     * @test
     * @expectedException \Illuminate\Validation\ValidationException
     */
    public function web_destroy_should_throw_validation_exception_response_if_rules_does_not_match()
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
    public function web_destroy_should_validate_the_request_input()
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

        $response = $instance->destroy(request(), 1);
        $this->assertSession('success', 'Successfully deleted.');
        $this->assertEquals('http://localhost/dummies/index', $response->getTargetUrl());
    }

    protected function controller(
        string $resource = DummyResource::class,
        Request $request = null
    ): DummyWebController {

        if (is_null($request)) {
            $request = request();
        }

        $this->resourceMock = Mockery::mock($resource);
        $this->resourceMock->shouldReceive('setRequestOperations')->with($request)->andReturnSelf();

        return new DummyWebController($this->resourceMock);
    }

    protected function assertSession(string $status, string $message)
    {
        $session = session()->all();
        $this->assertEquals($session['status'], $status);
        $this->assertEquals($session['message'], $message);
        $this->assertEquals(array_get($session, '_flash.new'), ['status', 'message']);
    }
}