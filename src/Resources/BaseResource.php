<?php


namespace BaseTree\Resources;


use BaseTree\Datatable\Creator as DatatableCreator;
use BaseTree\Eloquent\RepositoryInterface;
use BaseTree\Models\Model;
use BaseTree\Resources\Contracts\ResourceCallbacks;
use BaseTree\Resources\Contracts\ResourceScreen;
use Illuminate\Http\Request;

class BaseResource implements ResourceScreen
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * Used to dynamically fill the attributes on the given model
     * @var array
     */
    protected $fillable = [];

    /**
     * Used to dynamically get the relations on the given model
     * @var array
     */
    protected $relations = [];

    /**
     * Used to dynamically exclude non-updateable fields
     * @var array
     */
    protected $excludedOnUpdate = [];

    protected $shouldPaginate;

    protected $datatableResponse;

    /**
     * @var DatatableCreator
     */
    protected $datatable;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->fillable = $this->repository->getFillable();
        $this->datatable = app(DatatableCreator::class);
    }

    public function index()
    {
        if ($this->shouldPaginate) {
            return $this->repository->paginated();
        }

        $data = $this->repository->all(['*'], $this->relations);

        if ($this->datatableResponse) {
            return $this->datatable->create(true, $data);
        }

        return $data;
    }

    public function findWithoutRelations($id)
    {
        $result = $this->repository->find($id);

        return $this->returnOrFail($result);
    }

    public function findWithRelations($id, array $relations = [])
    {
        return $this->repository->findWithRelations($id, $relations);
    }

    public function returnOrFail(Model $model = null)
    {
        return $this->repository->returnOrFail($model);
    }

    public function store(array $attributes)
    {
        $model = $this->repository->create($attributes);

        if ($this instanceof ResourceCallbacks) {
            $this->created($model, array_except($attributes, $this->fillable), $attributes);
        }

        return $this->show($model->getKey(), $this->relations);
    }

    public function updateEntity(Model $model, array $attributes)
    {
        if ( ! empty($this->fillable)) {
            $attributes = array_except($attributes, $this->excludedOnUpdate);
            $updated = $this->repository->update($model, array_only($attributes, $this->fillable));
        }

        if ($this instanceof ResourceCallbacks) {
            $dependencies = array_except($attributes, $this->fillable) ?? [];
            $this->updated($model, $updated ?? null, $dependencies, $attributes);
        }

        return $this->show($model->getKey());
    }

    public function destroy(Model $model)
    {
        return $this->repository->delete($model);
    }

    public function show($id, array $relations = [])
    {
        return $this->findWithRelations($id, $relations);
    }

    public function setRequestOperations(Request $request): void
    {
        $this->relations = array_unique(array_merge($request->get('fields', []), $this->repository->relations()));
        $this->shouldPaginate = $request->has('paginate');
        $this->datatableResponse = $request->has('datatable');
        $this->repository->setRequestRequirements($request->get('perPage') ?? 15,
            $this->relations, $request->get('constraints') ?? []);
    }

    public function repository(): RepositoryInterface
    {
        return $this->repository;
    }

    public function model(): Model
    {
        return $this->repository->model();
    }

    public function authorizationKey()
    {
        $model = $this->model();
        if (is_string($model)) {
            return $model;
        }

        return get_class($model);
    }
}