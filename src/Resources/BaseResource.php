<?php


namespace BaseTree\Resources;


use BaseTree\Datatable\Creator as DatatableCreator;
use BaseTree\Eloquent\RepositoryInterface;
use BaseTree\Models\BaseTreeModel;
use BaseTree\Resources\Contracts\Callbacks\CreatedCallback;
use BaseTree\Resources\Contracts\Callbacks\DeletedCallback;
use BaseTree\Resources\Contracts\Callbacks\UpdatedCallback;
use BaseTree\Resources\Contracts\ResourceScreen;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Model as EloquentModel;
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
     * Called on update
     * @var array
     */
    protected $excludedOnUpdate = [];

    /**
     * Ignore this keys if they do not have value.
     * For instance if you send empty value from client, but you already have some default value for it
     * Called on update
     * @var array
     */
    protected $excludeOnUpdateIfEmpty = [];

    /**
     * Determine if the response should be paginated
     * @var bool
     */
    protected $shouldPaginate;

    /**
     * Determine if the response should be with data table structure
     * @var bool
     */
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

    public function index(): Arrayable
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

    public function findWithoutRelations($id): BaseTreeModel
    {
        $result = $this->repository->find($id);

        return $this->returnOrFail($result);
    }

    public function findWithRelations($id, array $relations = []): BaseTreeModel
    {
        return $this->repository->findWithRelations($id, $relations);
    }

    public function returnOrFail(BaseTreeModel $model = null): BaseTreeModel
    {
        return $this->repository->returnOrFail($model);
    }

    public function store(array $attributes)
    {
        $model = $this->repository->create(array_only($attributes, $this->fillable));

        if ($this instanceof CreatedCallback) {
            $this->created($model, array_except($attributes, $this->fillable), $attributes);
        }

        /** @var EloquentModel $model */
        return $this->show($model->getKey(), $this->relations);
    }

    public function updateEntity(BaseTreeModel $model, array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if ((is_null($value) or $value === '') and in_array($key, $this->excludeOnUpdateIfEmpty)) {
                unset($attributes[$key]);
            }
        }

        if ( ! empty($this->fillable)) {
            $attributes = array_except($attributes, $this->excludedOnUpdate);
            $updated = $this->repository->update($model, array_only($attributes, $this->fillable));
        }

        if ($this instanceof UpdatedCallback) {
            $dependencies = array_except($attributes, $this->fillable) ?? [];
            $this->updated($model, $updated ?? null, $dependencies, $attributes);
        }

        /** @var EloquentModel $model */
        return $this->show($model->getKey());
    }

    public function destroy(BaseTreeModel $model, array $attributes = []): bool
    {
        /** @var EloquentModel|BaseTreeModel $model */
        $deleted = $this->repository->delete($model);

        if ($this instanceof DeletedCallback) {
            $this->deleted($model, $attributes);
        }

        return $deleted;
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

    /**
     * @return BaseTreeModel|Model|Builder
     */
    public function model(): BaseTreeModel
    {
        return $this->repository->model();
    }

    public function authorizationKey(): string
    {
        return get_class($this->model());
    }
}