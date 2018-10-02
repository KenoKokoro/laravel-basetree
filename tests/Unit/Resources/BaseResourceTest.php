<?php


namespace BaseTree\Tests\Unit\Resources;


use BaseTree\Datatable\Creator;
use BaseTree\Eloquent\Paginator;
use BaseTree\Resources\BaseResource;
use BaseTree\Tests\Fake\DummyModel;
use BaseTree\Tests\Fake\DummyResource;
use BaseTree\Tests\Fake\DummyResourceWithCallback;
use BaseTree\Tests\Fake\DummyResourceWithValidationsRules;
use BaseTree\Tests\Fake\EloquentDummy;
use BaseTree\Tests\Unit\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Yajra\DataTables\DataTablesServiceProvider;

/**
 * @property Mockery\MockInterface model
 * @property Mockery\MockInterface queryMock
 */
class BaseResourceTest extends TestCase
{
    /**
     * @var DummyResource
     */
    protected $instance;

    public function setUp()
    {
        parent::setUp();
        $this->app->register(DataTablesServiceProvider::class);
    }

    /** @test */
    public function index_default_response(): void
    {
        $instance = $this->resourceInstance();
        $this->queryMock->shouldReceive('with')->andReturnSelf();
        $this->queryMock->shouldReceive('get')->andReturn(new Collection(['item1', 'item2']));

        $response = $instance->index();
        $this->assertInstanceOf(Collection::class, $response);
        $this->assertCount(2, $response);
    }

    /** @test */
    public function index_should_paginate(): void
    {
        $this->request->query->add(['paginate' => 1]);
        $instance = $this->resourceInstance($this->request);

        $this->queryMock->shouldReceive('with')->andReturnSelf();
        $items = new Collection(['item1', 'item2', 'item3']);
        $this->queryMock->shouldReceive('paginate')->andReturn(new LengthAwarePaginator($items, $items->count(), 15));

        $response = $instance->index();
        $this->assertInstanceOf(Paginator::class, $response);
        /** @var Paginator $response */
        $array = $response->toArray();
        $this->assertArrayHasKey('data', $array);
        $this->assertCount(3, $array['data']);
    }

    /** @test */
    public function index_should_return_datatable_response(): void
    {
        $this->request->query->add(['datatable' => 1]);
        $instance = $this->resourceInstance($this->request);

        $this->queryMock->shouldReceive('with')->andReturnSelf();
        $items = new Collection(['item1', 'item2', 'item3']);
        $this->queryMock->shouldReceive('get')->andReturn($items);

        $response = $instance->index();
        $this->assertInstanceOf(Creator::class, $response);
        /** @var Creator $response */
        $array = $response->toArray();
        $this->assertArrayHasKey('data', $array);
        $this->assertCount(3, $array['data']);
    }

    /**
     * @test
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function find_without_relationships_should_fail_if_entry_does_not_exists(): void
    {
        $instance = $this->resourceInstance();
        $this->model->shouldReceive('find')->with(1)->andReturn(null);
        $instance->findWithoutRelations(1);
    }

    /** @test */
    public function find_without_relationships_should_return_found_model(): void
    {
        $instance = $this->resourceInstance();
        $this->model->shouldReceive('find')->with(1)->andReturnSelf();

        $response = $instance->findWithoutRelations(1);
        $this->assertInstanceOf(DummyModel::class, $response);
    }

    /**
     * @test
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function find_with_relations_should_fail_if_entry_does_not_exist(): void
    {
        $instance = $this->resourceInstance();

        $this->model->shouldReceive('find')->with(1)->andReturn(null);
        $instance->findWithRelations(1, ['Relation1']);
    }

    /** @test */
    public function find_with_relations_should_return_found_model_with_the_wanted_relations(): void
    {
        $instance = $this->resourceInstance();

        $this->model->shouldReceive('find')->with(1)->andReturnSelf();
        $this->model->shouldReceive('load')->with(['Relation1'])->andReturnSelf();
        $instance->findWithRelations(1);
    }

    /** @test */
    public function store_should_write_the_given_attributes_to_database(): void
    {
        $instance = $this->resourceInstance();
        $attributes = ['attr1' => 'value1', 'attr2' => 'value2'];

        $this->model->shouldReceive('create')->with($attributes)->andReturnSelf();
        $this->model->shouldReceive('getKey')->andReturn(1);
        $this->model->shouldReceive('find')->with(1)->andReturnSelf();
        $this->model->shouldReceive('load')->with(['Relation1'])->andReturnSelf();

        $response = $instance->store($attributes);
        $this->assertInstanceOf(DummyModel::class, $response);
    }

    /** @test */
    public function store_should_call_the_callback_if_the_resource_has_implemented_the_callback_interface(): void
    {
        $instance = $this->resourceInstance($this->request, DummyResourceWithCallback::class);
        $attributes = ['attr1' => 'value1', 'attr2' => 'value2'];

        $this->model->shouldReceive('create')->with($attributes)->andReturnSelf();
        # Callbacks
        $this->model->shouldReceive('update')->with(['fake' => 'value'])->andReturnSelf();
        $this->model->shouldReceive('fresh')->andReturnSelf();
        $this->model->shouldReceive('getKey')->andReturn(1);
        $this->model->shouldReceive('find')->with(1)->andReturnSelf();
        $this->model->shouldReceive('load')->with(['Relation1'])->andReturnSelf();

        $response = $instance->store($attributes);
        $this->assertInstanceOf(DummyModel::class, $response);
    }

    /** @test */
    public function update_entity_should_write_the_given_attributes_for_the_given_entity(): void
    {
        $instance = $this->resourceInstance();
        $attributes = ['attr1' => 'value1', 'attr2' => 'value2'];

        $this->model->shouldReceive('update')->with($attributes)->andReturnSelf();
        $this->model->shouldReceive('fresh')->andReturnSelf();
        $this->model->shouldReceive('getKey')->andReturn(1);
        $this->model->shouldReceive('find')->with(1)->andReturnSelf();
        $this->model->shouldReceive('load')->with(['Relation1'])->andReturnSelf();

        $response = $instance->updateEntity($this->model, $attributes);
        $this->assertInstanceOf(DummyModel::class, $response);
    }

    /** @test */
    public function update_entity_should_write_the_given_attributes_for_the_given_entity_and_execute_the_callback(
    ): void
    {
        $instance = $this->resourceInstance($this->request, DummyResourceWithCallback::class);
        $attributes = ['attr1' => 'value1', 'attr2' => 'value2'];

        $this->model->shouldReceive('update')->with($attributes)->andReturnSelf();
        $this->model->shouldReceive('fresh')->twice()->andReturnSelf();
        # Callback
        $this->model->shouldReceive('update')->with(['fake' => 'value'])->andReturnSelf();
        $this->model->shouldReceive('getKey')->andReturn(1);
        $this->model->shouldReceive('find')->with(1)->andReturnSelf();
        $this->model->shouldReceive('load')->with(['Relation1'])->andReturnSelf();

        $response = $instance->updateEntity($this->model, $attributes);
        $this->assertInstanceOf(DummyModel::class, $response);
    }

    /** @test */
    public function update_entity_should_ignore_the_excluded_on_update_values(): void
    {
        $instance = $this->resourceInstance();
        $attributes = ['attr1' => 'value1', 'attr2' => 'value2', 'attr3' => 'value3'];

        $this->model->shouldReceive('update')->with(['attr1' => 'value1', 'attr2' => 'value2'])->andReturnSelf();
        $this->model->shouldReceive('fresh')->once()->andReturnSelf();
        $this->model->shouldReceive('getKey')->andReturn(1);
        $this->model->shouldReceive('find')->with(1)->andReturnSelf();
        $this->model->shouldReceive('load')->with(['Relation1'])->andReturnSelf();

        $response = $instance->updateEntity($this->model, $attributes);
        $this->assertInstanceOf(DummyModel::class, $response);
    }

    /** @test */
    public function update_entity_should_ignore_empty_values_if_provided(): void
    {
        $fillable = ['attr1', 'attr4', 'attr5', 'attr6', 'attr7'];
        $instance = $this->resourceInstance(null, DummyResource::class, $fillable);
        $attributes = ['attr1' => 'value1', 'attr4' => '', 'attr5' => null, 'attr6' => 0, 'attr7' => []];

        $this->model->shouldReceive('update')->with(['attr1' => 'value1', 'attr6' => 0, 'attr7' => []])
                    ->andReturnSelf();
        $this->model->shouldReceive('fresh')->once()->andReturnSelf();
        $this->model->shouldReceive('getKey')->andReturn(1);
        $this->model->shouldReceive('find')->with(1)->andReturnSelf();
        $this->model->shouldReceive('load')->with(['Relation1'])->andReturnSelf();

        $response = $instance->updateEntity($this->model, $attributes);
        $this->assertInstanceOf(DummyModel::class, $response);
    }

    /** @test */
    public function update_entity_should_not_ignore_empty_values_if_provided_with_value(): void
    {
        $fillable = ['attr1', 'attr4', 'attr5', 'attr6', 'attr7'];
        $instance = $this->resourceInstance(null, DummyResource::class, $fillable);
        $attributes = ['attr1' => 'value1', 'attr4' => 'value4', 'attr5' => 5, 'attr6' => ['full']];

        $this->model->shouldReceive('update')->with([
            'attr1' => 'value1',
            'attr4' => 'value4',
            'attr5' => 5,
            'attr6' => ['full']
        ])->andReturnSelf();
        $this->model->shouldReceive('fresh')->once()->andReturnSelf();
        $this->model->shouldReceive('getKey')->andReturn(1);
        $this->model->shouldReceive('find')->with(1)->andReturnSelf();
        $this->model->shouldReceive('load')->with(['Relation1'])->andReturnSelf();

        $response = $instance->updateEntity($this->model, $attributes);
        $this->assertInstanceOf(DummyModel::class, $response);
    }

    /** @test */
    public function destroy_should_delete_the_entity_from_database(): void
    {
        $instance = $this->resourceInstance();

        $this->model->shouldReceive('delete')->andReturn(true);

        $instance->destroy($this->model);
    }

    /** @test */
    public function destroy_entity_should_delete_the_given_entity_from_database_and_execute_the_callback(): void
    {
        $instance = $this->resourceInstance($this->request, DummyResourceWithCallback::class);

        $this->model->shouldReceive('delete')->andReturn(true);
        $this->model->shouldReceive('find')->with(1);

        $instance->destroy($this->model, ['not-fillable-id' => 1]);
    }

    /** @test */
    public function repository_should_return_the_injected_repository(): void
    {
        $instance = $this->resourceInstance();

        $this->assertInstanceOf(EloquentDummy::class, $instance->repository());
    }

    /** @test */
    public function model_should_return_the_injected_model_in_the_repository(): void
    {
        $instance = $this->resourceInstance();

        $this->assertInstanceOf(DummyModel::class, $instance->model());
    }

    /** @test */
    public function authorization_key_should_return_the_full_name_of_the_model_including_namespace(): void
    {
        $instance = $this->resourceInstance();

        $this->assertEquals(get_class($this->model), $instance->authorizationKey());
    }

    /** @test */
    public function resource_can_have_validation_rules(): void
    {
        /** @var DummyResourceWithValidationsRules $instance */
        $instance = $this->resourceInstance(null, DummyResourceWithValidationsRules::class);
        $this->assertTrue(is_array($instance->storeRules()));
        $this->assertTrue(is_array($instance->updateRules(1)));
        $this->assertTrue(is_array($instance->destroyRules(1)));
    }

    private function resourceInstance(
        Request $request = null,
        $resource = DummyResource::class,
        array $fillable = null
    ): BaseResource {
        if (is_null($request)) {
            $request = $this->request;
        }

        if (is_null($fillable)) {
            $fillable = ['attr1', 'attr2'];
        }

        $this->model = Mockery::mock(DummyModel::class);
        $this->queryMock = Mockery::mock(Builder::class);
        $this->model->shouldReceive('getFillable')->andReturn($fillable);
        $this->model->shouldReceive('newQuery')->andReturn($this->queryMock);
        $this->model->id = 1;

        $instance = new $resource(new EloquentDummy($this->model));
        $instance->setRequestOperations($request);

        return $instance;
    }
}
