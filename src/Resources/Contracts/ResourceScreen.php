<?php


namespace BaseTree\Resources\Contracts;


use BaseTree\Eloquent\RepositoryInterface;
use BaseTree\Models\BaseTreeModel;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

interface ResourceScreen
{
    /**
     * @return mixed
     */
    public function index(): Arrayable;

    /**
     * @param integer $id
     * @throws ModelNotFoundException
     * @return BaseTreeModel
     */
    public function findWithoutRelations($id): BaseTreeModel;

    /**
     * @param integer $id
     * @param array $relations
     * @throws ModelNotFoundException
     * @return BaseTreeModel
     */
    public function findWithRelations($id, array $relations = []): BaseTreeModel;

    /**
     * @param BaseTreeModel $model
     * @throws ModelNotFoundException
     * @return BaseTreeModel|Model
     */
    public function returnOrFail(BaseTreeModel $model = null): BaseTreeModel;

    /**
     * @param array $attributes
     * @return Builder|BaseTreeModel
     */
    public function store(array $attributes);

    /**
     * @param BaseTreeModel $model
     * @param array $attributes
     * @return BaseTreeModel|Builder
     */
    public function updateEntity(BaseTreeModel $model, array $attributes);

    /**
     * @param BaseTreeModel $model
     * @param array $attributes
     * @return boolean
     */
    public function destroy(BaseTreeModel $model, array $attributes = []): bool;

    /**
     * @param $id
     * @param array $relations
     * @return Builder|BaseTreeModel
     */
    public function show($id, array $relations = []);

    /**
     * @param Request $request
     * @return void
     */
    public function setRequestOperations(Request $request): void;

    /**
     * @return RepositoryInterface
     */
    public function repository(): RepositoryInterface;

    /**
     * @return BaseTreeModel
     */
    public function model(): BaseTreeModel;

    /**
     * @return string
     */
    public function authorizationKey(): string;
}