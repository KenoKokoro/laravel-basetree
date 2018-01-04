<?php


namespace BaseTree\Eloquent;


use BaseTree\Models\Model;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Expression;

class BaseEloquent implements RepositoryInterface
{
    /** @var Model|Builder */
    protected $query;

    protected $model;

    protected $perPage = self::PER_PAGE;

    protected $relations = [];

    protected $defaultConstraints = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->query = $model->newQuery();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function findByConstraints(array $constraints)
    {
        if (empty($constraints)) {
            throw new Exception("Constraint array shouldn't be empty.");
        }

        $builder = $this->query->newQuery();

        foreach ($constraints as $column => $value) {
            $builder->where($column, $value);
        }

        return $this->returnOrFail($builder->first());
    }

    public function findWithRelations($id, $relations = [])
    {
        if (empty($relations)) {
            $relations = $this->relations;
        }

        return $this->findOrFail($id)->load($relations);
    }

    public function all(array $columns = ['*'], array $relations = [])
    {
        return $this->query->with($relations)->get($columns);
    }

    public function paginated(array $columns = ['*'], $builder = null)
    {
        /** @var Builder $builder */
        if ($builder) {
            return new Paginator($builder->paginate($this->perPage, $columns));
        }

        return new Paginator($this->query->with($this->relations)->paginate($this->perPage, $columns));
    }

    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    public function findOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    public function getByIds(array $ids, $column = 'id')
    {
        return $this->query->whereIn($column, $ids)->get();
    }

    public function update(Model $model, array $attributes)
    {
        $model->update($attributes);

        return $model->fresh();
    }

    public function updateInMemory(Model $model, array $attributes)
    {
        foreach ($attributes as $attribute => $value) {
            $model->{$attribute} = $value;
        }
        $model->save();

        return $model;
    }

    public function delete(Model $model)
    {
        return $model->delete();
    }

    public function returnOrFail($model)
    {
        if ( ! $model) {
            throw (new ModelNotFoundException())->setModel(get_class($this->query));
        }

        return $model;
    }

    public function setConstraints(array $constraints)
    {
        $mutated = new ConstraintsMutator($constraints);

        foreach (array_merge($mutated->queries(), $this->defaultConstraints) as $query) {
            if ($query instanceof Expression) {
                $this->query->whereRaw($query);
                continue;
            }

            $this->query->where(...$query);
        }
    }

    public function setRequestRequirements($perPage = self::PER_PAGE, array $relations = [], array $constraints = [])
    {
        $this->perPage = $perPage;
        $this->relations = $relations;
        $this->setConstraints($constraints);
    }

    public function count()
    {
        return $this->query->count();
    }

    public function getFillable()
    {
        return $this->model->getFillable();
    }

    public function model()
    {
        return $this->model;
    }

    public function relations()
    {
        return $this->relations;
    }
}