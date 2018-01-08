<?php


namespace BaseTree\Eloquent;


use BaseTree\Models\BaseTreeModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Expression;

class BaseEloquent implements RepositoryInterface
{
    /** @var EloquentModel|Builder */
    protected $query;

    /** @var EloquentModel|Builder */
    protected $model;

    protected $perPage = self::PER_PAGE;

    protected $relations = [];

    protected $defaultConstraints = [];

    /**
     * @param BaseTreeModel|EloquentModel $model
     */
    public function __construct(BaseTreeModel $model)
    {
        $this->model = $model;
        $this->query = $model->newQuery();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function findByConstraints(array $constraints): BaseTreeModel
    {
        if (empty($constraints)) {
            throw new InvalidArgumentException('Constraint array shouldn`t be empty.');
        }

        $builder = $this->query->newQuery();

        foreach ($constraints as $column => $value) {
            $builder->where($column, $value);
        }

        return $this->returnOrFail($builder->first());
    }

    public function findWithRelations($id, array $relations = []): BaseTreeModel
    {
        /** @var Model|Builder $model */
        if (empty($relations)) {
            $relations = $this->relations;
        }

        return $this->returnOrFail($this->find($id))->load($relations);
    }

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->query->with($relations)->get($columns);
    }

    public function paginated(array $columns = ['*'], Builder $builder = null): Paginator
    {
        /** @var Builder $builder */
        if ($builder) {
            return new Paginator($builder->paginate($this->perPage, $columns));
        }

        return new Paginator($this->query->with($this->relations)->paginate($this->perPage, $columns));
    }

    public function create(array $attributes): BaseTreeModel
    {
        return $this->model->create($attributes);
    }

    public function findOrFail($id): BaseTreeModel
    {
        return $this->returnOrFail($this->find($id));
    }

    public function getByIds(array $ids, string $column = 'id'): Collection
    {
        return $this->query->whereIn($column, $ids)->get();
    }

    public function update(BaseTreeModel $model, array $attributes): BaseTreeModel
    {
        /** @var Model|Builder $model */
        $model->update($attributes);

        return $model->fresh();
    }

    public function delete(BaseTreeModel $model): bool
    {
        /** @var Model|Builder $model */
        return (bool)$model->delete();
    }

    public function returnOrFail(BaseTreeModel $model = null): BaseTreeModel
    {
        if ( ! $model) {
            throw (new ModelNotFoundException())->setModel(get_class($this->query));
        }

        return $model;
    }

    public function setRequestConstraints(array $constraints): void
    {
        $mutated = new ConstraintsMutator($constraints);

        foreach (array_merge($mutated->queries(), $this->defaultConstraints) as $query) {
            if ($query instanceof Expression) {
                $this->query->whereRaw($query->getValue());
                continue;
            }

            $this->query->where(...$query);
        }
    }

    public function setRequestRequirements(
        $perPage = self::PER_PAGE,
        array $relations = [],
        array $constraints = []
    ): void {
        $this->perPage = $perPage;
        $this->relations = $relations;
        $this->setRequestConstraints($constraints);
    }

    public function count(): int
    {
        return $this->query->count();
    }

    public function getFillable(): array
    {
        return $this->model->getFillable();
    }

    public function model(): BaseTreeModel
    {
        return $this->model;
    }

    public function relations(): array
    {
        return $this->relations;
    }
}