<?php


namespace BaseTree\Tests\Unit\Datatable;


use BaseTree\Datatable\Creator;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class CreatorTest extends TestCase
{
    /** @test */
    public function creator_json_serialize_should_return_same_as_to_array()
    {
        $creator = new Creator;

        $creator->create(true, new Collection(['item1']));

        $this->assertEquals($creator->toArray(), $creator->jsonSerialize());
    }
}