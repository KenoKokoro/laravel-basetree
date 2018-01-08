<?php


namespace BaseTree\Eloquent;


use BaseTree\Models\BaseTreeModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface RepositoryInterface
{
    const PER_PAGE = 20;

    /**
     * @param $id
     * @return BaseTreeModel
     */
    public function find($id);

    /**
     * @param array $constraints
     * @throws InvalidArgumentException
     * @throws ModelNotFoundException
     * @return BaseTreeModel
     */
    public function findByConstraints(array $constraints): BaseTreeModel;

    /**
     * @param $id
     * @param array $relations
     * @throws ModelNotFoundException
     * @return BaseTreeModel
     */
    public function findWithRelations($id, array $relations = []): BaseTreeModel;

    /**
     * @param array $columns
     * @param array $relations
     * @return Collection
     */
    public function all(array $columns = ['*'], array $relations = []): Collection;

    /**
     * @param array $columns
     * @param Builder|null $builder
     * @return Paginator
     */
    public function paginated(array $columns = ['*'], Builder $builder = null): Paginator;

    /**
     * @param array $attributes
     * @return BaseTreeModel
     */
    public function create(array $attributes): BaseTreeModel;

    /**
     * @param $id
     * @return BaseTreeModel
     */
    public function findOrFail($id): BaseTreeModel;

    /**
     * @param array $ids
     * @param string $column
     * @return Collection
     */
    public function getByIds(array $ids, string $column = 'id'): Collection;

    /**
     * @param BaseTreeModel $model
     * @param array $attributes
     * @return BaseTreeModel
     */
    public function update(BaseTreeModel $model, array $attributes): BaseTreeModel;

    /**
     * @param BaseTreeModel $model
     * @return bool
     */
    public function delete(BaseTreeModel $model): bool;

    /**
     * @param $model
     * @throws ModelNotFoundException
     * @return BaseTreeModel|Model
     */
    public function returnOrFail(BaseTreeModel $model = null): BaseTreeModel;

    /**
     * @param array $constraints
     * @return Builder
     */
    public function setRequestConstraints(array $constraints): void;

    /**
     * @param int $perPage
     * @param array $relations
     * @param array $constraints
     * @return void
     */
    public function setRequestRequirements(
        $perPage = self::PER_PAGE,
        array $relations = [],
        array $constraints = []
    ): void;

    /**
     * @return integer
     */
    public function count(): int;

    /**
     * @return array
     */
    public function getFillable(): array;

    /**
     * @return BaseTreeModel
     */
    public function model(): BaseTreeModel;

    /**
     * @return array
     */
    public function relations(): array;
}