<?php


namespace BaseTree\Tests;


use BaseTree\Models\Model;
use BaseTree\Tests\Traits\Assertions\FieldValidationMessages;
use BaseTree\Tests\Traits\CreatesApplication;
use BaseTree\Tests\Traits\DatabaseMigrations;
use BaseTree\Tests\Traits\HelperMethods;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\TestCase;
use Mockery as m;

class DatabaseTestCase extends TestCase
{
    use CreatesApplication, DatabaseMigrations, HelperMethods, FieldValidationMessages;

    protected function assertCRUD(Model $model, array $attrs, array $updateAttrs)
    {
        $this->assertCreated($model, $attrs);
        $this->assertUpdated($model, $attrs, $updateAttrs);
        $this->assertDeleted($model, array_merge($attrs, $updateAttrs));
    }

    protected function assertCreated($model, array $attrs)
    {
        if ( ! is_a($model, Model::class)) {
            $this->assertDatabaseHas($model, $attrs);

            return;
        }
        /** @var Model $model */
        $this->assertDatabaseHas($model->getTable(), $attrs);
    }

    protected function assertDeleted($model, array $attrs)
    {
        if ( ! is_a($model, Model::class)) {
            $this->assertDatabaseMissing($model, $attrs);

            return;
        }
        /** @var Model $model */
        $model->delete();
        $this->assertDatabaseMissing($model->getTable(), $attrs);
    }

    protected function assertUpdated(Model $model, array $attrs, array $updateAttrs)
    {
        $model->update($updateAttrs);
        $this->assertCreated($model->getTable(), array_merge($attrs, $updateAttrs));
    }

    protected function assertEmptyCollection(Collection $collection)
    {
        $this->assertTrue($collection->isEmpty());
        $this->assertEquals(0, $collection->count());
    }

    protected function assertBelongsTo(Model $model, Model $relationModel, $relationName, array $attrsToCheck)
    {
        $this->assertCreated($model, $attrsToCheck);
        $this->assertEquals($relationModel->id, $model->{ucfirst($relationName)}->id);
        $this->assertInstanceOf(BelongsTo::class, $model->{$relationName}());
    }

    protected function assertHasOne(Model $model, Model $relationModel, $relationName, array $attrsToCheck)
    {
        $this->assertCreated($relationModel, $attrsToCheck);
        $this->assertEquals($relationModel->id, $model->{ucfirst($relationName)}->id);
        $this->assertInstanceOf(HasOne::class, $model->{$relationName}());
    }

    protected function assertBelongsToMany(
        Model $model,
        Collection $relatedCollection,
        $relationName,
        $pivotTable,
        array $attrsCheck,
        $limit = 5
    ) {
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

    protected function assertHasMany(
        Model $model,
        Collection $relatedCollection,
        $relationName,
        $attrsCheck,
        $limit = 5
    ) {
        $this->assertEquals($limit, $model->{$relationName}()->get()->count());
        $this->assertEquals($limit, $relatedCollection->count());
        $this->assertCreated($relatedCollection->first(), $attrsCheck);
        $this->assertInstanceOf(HasMany::class, $model->{$relationName}());
    }

    protected function assertMorphTo(Model $model, Model $relationModel, $key, array $attrsCheck = [])
    {
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

    protected function assertMorphMany(
        Model $model,
        Collection $relatedCollection,
        $key,
        $relationName,
        int $quantity = 3
    ) {
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
    public function mock($instance)
    {
        $mock = m::mock($instance);
        app()->instance($instance, $mock);

        return $mock;
    }
}