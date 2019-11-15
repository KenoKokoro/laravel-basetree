<?php


namespace BaseTree\Tests\Unit\Datatable;


use BaseTree\Datatable\Creator;
use BaseTree\Tests\Unit\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Yajra\DataTables\DataTablesServiceProvider;

class CreatorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->app->register(DataTablesServiceProvider::class);
    }

    /** @test */
    public function creator_json_serialize_should_return_same_as_to_array(): void
    {
        $creator = new Creator;

        $creator->create(true, new Collection(['item1']));

        $this->assertEquals($creator->toArray(), $creator->jsonSerialize());
    }
}
