<?php


namespace BaseTree\Tests\Fake\Unit;


use BaseTree\Models\BaseTreeModel;

/**
 * @method  getTable()
 * @method  update(array $attributes = [], array $options = [])
 * @method  delete()
 * @method  getMorphClass()
 */
class DummyModel implements BaseTreeModel
{
}