<?php


namespace BaseTree\Eloquent;


use BaseTree\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface RepositoryInterface
{
    const PER_PAGE = 20;

    /**
     * @param $id
     * @return Model
     */
    public function find($id);

    /**
     * @param array $constraints
     * @return Model
     */
    public function findByConstraints(array $constraints);

    /**
     * @param $id
     * @param array $relations
     * @return Model
     */
    public function findWithRelations($id, $relations = []);

    /**
     * @param array $columns
     * @param array $relations
     * @return Collection
     */
    public function all(array $columns = ['*'], array $relations = []);

    /**
     * @param array $columns
     * @param null $builder
     * @return Paginator
     */
    public function paginated(array $columns = ['*'], $builder = null);

    /**
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes);

    /**
     * @param $id
     * @return Model
     */
    public function findOrFail($id);

    /**
     * @param array $ids
     * @param string $column
     * @return Collection
     */
    public function getByIds(array $ids, $column = 'id');

    /**
     * @param Model $model
     * @param array $attributes
     * @return Model
     */
    public function update(Model $model, array $attributes);

    /**
     * @param Model $model
     * @param array $attributes
     * @return Model
     */
    public function updateInMemory(Model $model, array $attributes);

    /**
     * @param Model $model
     * @return boolean
     */
    public function delete(Model $model);

    /**
     * @param $model
     * @throws ModelNotFoundException
     * @return Model
     */
    public function returnOrFail($model);

    /**
     * @param array $constraints
     * @return Builder
     */
    public function setConstraints(array $constraints);

    /**
     * @param int $perPage
     * @param array $relations
     * @param array $constraints
     * @return void
     */
    public function setRequestRequirements($perPage = self::PER_PAGE, array $relations = [], array $constraints = []);

    /**
     * @return integer
     */
    public function count();

    /**
     * @return array
     */
    public function getFillable();

    /**
     * @return Model
     */
    public function model();

    /**
     * @return array
     */
    public function relations();
}