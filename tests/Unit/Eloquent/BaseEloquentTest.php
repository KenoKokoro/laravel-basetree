<?php

namespace BaseTree\Tests\Unit\Eloquent;

use BaseTree\Eloquent\BaseEloquent;
use BaseTree\Eloquent\InvalidArgumentException;
use BaseTree\Tests\Fake\Unit\DummyModel;
use BaseTree\Tests\Fake\Unit\EloquentDummy;
use BaseTree\Tests\Fake\Unit\EloquentDummyWithConstraints;
use BaseTree\Tests\Unit\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Expression;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Mockery;

/**
 * @property Mockery\MockInterface model
 * @property Mockery\MockInterface queryMock
 */
class BaseEloquentTest extends TestCase
{
    /**
     * @test
     * @throws InvalidArgumentException
     */
    public function find_by_constraints_should_throw_exception_if_constraints_are_empty(): void
    {
        self::expectException(InvalidArgumentException::class);
        $this->eloquentInstance()->findByConstraints([]);
    }

    /** @test */
    public function find_by_constraints_should_use_array_as_query_builder(): void
    {
        $instance = $this->eloquentInstance();
        $constraints = ['column1' => 'value1', 'column2' => 'value2'];

        $this->queryMock->shouldReceive('newQuery')->andReturnSelf();
        $this->queryMock->shouldReceive('where')->with('column1', 'value1')->andReturnSelf();
        $this->queryMock->shouldReceive('where')->with('column2', 'value2')->andReturnSelf();
        $this->queryMock->shouldReceive('first')->andReturn($this->model);

        $response = $instance->findByConstraints($constraints);
        $this->assertInstanceOf(get_class($this->model), $response);
    }

    /** @test */
    public function paginated_should_return_filtered_pagination_from_the_given_builder(): void
    {
        $instance = $this->eloquentInstance();
        $paginatorStub = new LengthAwarePaginator(collect([]), 0, 20);
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('paginate')->with(20, ['*'])->andReturn($paginatorStub);

        $instance->paginated(['*'], $builder);
    }

    /** @test */
    public function paginated_should_return_pagination_from_injected_query(): void
    {
        $instance = $this->eloquentInstance();
        $paginatorStub = new LengthAwarePaginator(collect([]), 0, 20);
        $this->queryMock->shouldReceive('with')->with(['Relation1'])->andReturnSelf();
        $this->queryMock->shouldReceive('paginate')->with(20, ['*'])->andReturn($paginatorStub);

        $instance->paginated();
    }

    /**
     * @test
     * @throws ModelNotFoundException
     */
    public function find_or_fail_should_throw_exception_if_there_are_not_matching_results(): void
    {
        self::expectException(ModelNotFoundException::class);
        $instance = $this->eloquentInstance();

        $this->model->shouldReceive('find')->with(1)->andReturn(null);

        $instance->findOrFail(1);
    }

    /** @test */
    public function get_by_ids_should_search_multiple_entries_by_same_key(): void
    {
        $instance = $this->eloquentInstance();

        $this->queryMock->shouldReceive('whereIn')->with('id', [1, 2, 3])->andReturnSelf();
        $this->queryMock->shouldReceive('get')->andReturn(new Collection());

        $instance->getByIds([1, 2, 3]);
    }

    /** @test */
    public function set_request_constraints_should_set_default_constraints_on_the_injected_query_builder(): void
    {
        $instance = $this->eloquentInstance();

        DB::shouldReceive('raw')->andReturn(new Expression('column1 = value1'));
        DB::shouldReceive('raw')->andReturn(new Expression('column2 = value2'));
        $this->queryMock->shouldReceive('whereRaw')->with('column1 = value1')->andReturnSelf();
        $this->queryMock->shouldReceive('whereRaw')->with('column2 = value2')->andReturnSelf();

        $instance->setRequestConstraints(['column1|=|value1', 'column2|=|value2']);
    }

    /** @test */
    public function set_request_constraints_should_set_default_constraints_from_the_predefined_constraints(): void
    {
        $instance = $this->eloquentInstance(EloquentDummyWithConstraints::class);

        DB::shouldReceive('raw')->andReturn(new Expression('column1 = value1'));
        DB::shouldReceive('raw')->andReturn(new Expression('column2 = value2'));
        $this->queryMock->shouldReceive('whereRaw')->with('column1 = value1')->andReturnSelf();
        $this->queryMock->shouldReceive('whereRaw')->with('column2 = value2')->andReturnSelf();
        $this->queryMock->shouldReceive('where')->with('column-default', '=', 'value-default')->andReturnSelf();

        $instance->setRequestConstraints(['column1|=|value1', 'column2|=|value2']);
    }

    /** @test */
    public function count_should_return_count_response_from_database(): void
    {
        $instance = $this->eloquentInstance();

        $this->queryMock->shouldReceive('count')->once()->andReturn(1);

        $response = $instance->count();
        $this->assertEquals(1, $response);
    }

    public function eloquentInstance(string $instance = EloquentDummy::class): BaseEloquent
    {
        $this->model = Mockery::mock(DummyModel::class);
        $this->queryMock = Mockery::mock(Builder::class);
        $this->model->shouldReceive('getFillable')->andReturn(['attr1', 'attr2']);
        $this->model->shouldReceive('newQuery')->andReturn($this->queryMock);

        return new $instance($this->model);
    }
}
