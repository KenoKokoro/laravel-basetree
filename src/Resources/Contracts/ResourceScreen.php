<?php


namespace BaseTree\Resources\Contracts;


use BaseTree\Eloquent\RepositoryInterface;
use BaseTree\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

interface ResourceScreen
{
    /**
     * @return mixed
     */
    public function index();

    /**
     * @param integer $id
     * @return Model
     */
    public function findWithoutRelations($id);

    /**
     * @param integer $id
     * @param array $relations
     * @return Model
     */
    public function findWithRelations($id, array $relations = []);

    /**
     * @param Model $model
     * @return Model
     * @throws ModelNotFoundException
     */
    public function returnOrFail(Model $model = null);

    /**
     * @param array $attributes
     * @return Builder|Model
     */
    public function store(array $attributes);

    /**
     * @param Model $model
     * @param array $attributes
     * @return Model|Builder
     */
    public function updateEntity(Model $model, array $attributes);

    /**
     * @param Model $model
     * @return boolean
     */
    public function destroy(Model $model);

    /**
     * @param $id
     * @param array $relations
     * @return Builder|Model
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
     * @return Model
     */
    public function model(): Model;

    /**
     * @return string
     */
    public function authorizationKey(): string;
}