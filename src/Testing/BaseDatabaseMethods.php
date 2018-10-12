<?php


namespace BaseTree\Testing;


use BaseTree\Models\BaseTreeModel;
use BaseTree\Testing\Traits\Assertions\FieldValidationMessages;
use BaseTree\Testing\Traits\CreatesApplication;
use BaseTree\Testing\Traits\DatabaseMigrations;
use BaseTree\Testing\Traits\HelperMethods;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Mockery as m;

trait BaseDatabaseMethods
{
    use CreatesApplication, DatabaseMigrations, HelperMethods, FieldValidationMessages;

    /**
     * Assert all CRUD operations on the model
     * @param BaseTreeModel $model
     * @param array         $attrs
     * @param array         $updateAttrs
     */
    protected function assertCRUD(BaseTreeModel $model, array $attrs, array $updateAttrs): void
    {
        $this->assertCreated($model, $attrs);
        $this->assertUpdated($model, $attrs, $updateAttrs);
        $this->assertDeleted($model, array_merge($attrs, $updateAttrs));
    }

    /**
     * Assert that the model is created and exists in the db
     * @param BaseTreeModel|string $model table string or instance
     * @param array                $attrs
     */
    protected function assertCreated($model, array $attrs): void
    {
        if ( ! is_a($model, BaseTreeModel::class)) {
            $this->assertDatabaseHas($model, $attrs);

            return;
        }
        /** @var BaseTreeModel $model */
        $this->assertDatabaseHas($model->getTable(), $attrs);
    }

    /**
     * Assert that the model is deleted and missing from db
     * @param BaseTreeModel|string $model table string or instance
     * @param array                $attrs
     */
    protected function assertDeleted($model, array $attrs): void
    {
        if ( ! is_a($model, BaseTreeModel::class)) {
            $this->assertDatabaseMissing($model, $attrs);

            return;
        }
        /** @var BaseTreeModel $model */
        $model->delete();
        $this->assertDatabaseMissing($model->getTable(), $attrs);
    }

    /**
     * Assert that the model will change the attributes after the update
     * @param BaseTreeModel|string $model table string or instance
     * @param array                $attrs
     * @param array                $updateAttrs The attributes that should be asserted on
     */
    protected function assertUpdated(BaseTreeModel $model, array $attrs, array $updateAttrs): void
    {
        $model->update($updateAttrs);
        $this->assertCreated($model->getTable(), array_merge($attrs, $updateAttrs));
    }

    protected function assertEmptyCollection(Collection $collection): void
    {
        $this->assertTrue($collection->isEmpty());
        $this->assertEquals(0, $collection->count());
    }

    /**
     * Assert the the model and the given model are connected with belongs to relation
     * @param BaseTreeModel $model
     * @param BaseTreeModel $relationModel
     * @param string        $relationName
     * @param array         $attrsToCheck
     */
    protected function assertBelongsTo(
        BaseTreeModel $model,
        BaseTreeModel $relationModel,
        string $relationName,
        array $attrsToCheck
    ): void {
        $this->assertCreated($model, $attrsToCheck);
        $this->assertEquals($relationModel->id, $model->{ucfirst($relationName)}->id);
        $this->assertInstanceOf(BelongsTo::class, $model->{$relationName}());
    }

    /**
     * Assert the the model and the relation model are related with has one relation
     * @param BaseTreeModel $model
     * @param BaseTreeModel $relationModel
     * @param string        $relationName
     * @param array         $attrsToCheck
     */
    protected function assertHasOne(
        BaseTreeModel $model,
        BaseTreeModel $relationModel,
        string $relationName,
        array $attrsToCheck
    ): void {
        $this->assertCreated($relationModel, $attrsToCheck);
        $this->assertEquals($relationModel->id, $model->{ucfirst($relationName)}->id);
        $this->assertInstanceOf(HasOne::class, $model->{$relationName}());
    }

    /**
     * Assert that the model and the given collection instances are connected with belongs to many relation
     * @param BaseTreeModel $model
     * @param Collection    $relatedCollection
     * @param string        $relationName
     * @param string        $pivotTable
     * @param array         $attrsCheck
     * @param int           $limit
     */
    protected function assertBelongsToMany(
        BaseTreeModel $model,
        Collection $relatedCollection,
        string $relationName,
        string $pivotTable,
        array $attrsCheck,
        int $limit = 5
    ): void {
        # Assert Link
        $model->{$relationName}()->sync($relatedCollection->pluck('id')->toArray());
        $this->assertEquals($limit, $model->{$relationName}()->get()->count());
        $this->assertCreated($pivotTable, $attrsCheck);
        # Assert Unlink
        $model->{$relationName}()->sync([]);
        $this->assertEmptyCollection($model->{$relationName}()->get());
        $this->assertDeleted($pivotTable, $attrsCheck);
        # Assert Instance of relationship
        $this->assertInstanceOf(BelongsToMany::class, $model->{$relationName}());
    }

    /**
     * Assert that the model and the given collection instances are connected with has many relation
     * @param BaseTreeModel $model
     * @param Collection    $relatedCollection
     * @param string        $relationName
     * @param array         $attrsCheck
     * @param int           $limit
     */
    protected function assertHasMany(
        BaseTreeModel $model,
        Collection $relatedCollection,
        string $relationName,
        array $attrsCheck,
        int $limit = 5
    ): void {
        $this->assertEquals($limit, $model->{$relationName}()->get()->count());
        $this->assertEquals($limit, $relatedCollection->count());
        $this->assertCreated($relatedCollection->first(), $attrsCheck);
        $this->assertInstanceOf(HasMany::class, $model->{$relationName}());
    }

    /**
     * Assert that the model and the relation model are connected with morph to relation
     * @param BaseTreeModel $model
     * @param BaseTreeModel $relationModel
     * @param string        $key
     * @param array         $attrsCheck
     */
    protected function assertMorphTo(
        BaseTreeModel $model,
        BaseTreeModel $relationModel,
        string $key,
        array $attrsCheck = []
    ): void {
        $defaultAttrs = [
            "{$key}_id" => $relationModel->id,
            "{$key}_type" => $relationModel->getMorphClass(),
            'id' => $model->id
        ];
        if ( ! empty($attrsCheck)) {
            $defaultAttrs = array_merge($attrsCheck, $defaultAttrs);
        }
        $this->assertInstanceOf(MorphTo::class, $model->{$key}());
        $this->assertBelongsTo($model, $relationModel, $key, $defaultAttrs);
    }

    /**
     * Assert that the model and the related collection are connected with morph many connection
     * @param BaseTreeModel $model
     * @param Collection    $relatedCollection
     * @param string        $key
     * @param string        $relationName
     * @param int           $quantity
     */
    protected function assertMorphMany(
        BaseTreeModel $model,
        Collection $relatedCollection,
        string $key,
        string $relationName,
        int $quantity = 3
    ): void {
        $this->assertCreated($relatedCollection->first(), [
            "{$key}_id" => $model->id,
            "{$key}_type" => $model->getMorphClass(),
            'id' => $relatedCollection->first()->id
        ]);
        $this->assertCount($quantity, $model->{ucfirst($relationName)});
        $this->assertInstanceOf(MorphMany::class, $model->{$relationName}());
    }

    /**
     * @param $instance
     * @return m\MockInterface
     */
    protected function mock($instance): m\MockInterface
    {
        $mock = m::mock($instance);
        app()->instance($instance, $mock);

        return $mock;
    }
}