<?php


namespace BaseTree\Tests\Unit\Eloquent;


use BaseTree\Tests\Fake\Wrappers\PaginatorTestWrapper;
use BaseTree\Tests\Unit\TestCase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PaginatorTest extends TestCase
{
    /** @test */
    public function next_page_url_with_no_further_pages(): void
    {
        $instance = $this->paginator($this->collection());

        $this->assertNull($instance->testNextPageUrl());
    }

    /** @test */
    public function next_page_url_with_further_pages(): void
    {
        $instance = $this->paginator($this->collection(), 5);

        $this->assertEquals('http://localhost?page=2', $instance->testNextPageUrl());
    }

    /** @test */
    public function path_should_return_the_current_url(): void
    {
        $instance = $this->paginator();

        $this->assertEquals($this->request->url(), $instance->testPath());
    }

    /** @test */
    public function previous_page_url_with_no_further_pages(): void
    {
        $instance = $this->paginator();

        $this->assertNull($instance->testPreviousPageUrl());
    }

    /** @test */
    public function previous_page_url_with_further_pages(): void
    {
        $instance = $this->paginator($this->collection(), 5, 2);

        $this->assertEquals('http://localhost?page=1', $instance->testPreviousPageUrl());
    }

    /** @test */
    public function url_should_not_have_negative_number_or_zero(): void
    {
        $instance = $this->paginator();

        $this->assertEquals('http://localhost?page=1', $instance->testUrl(-1));
    }

    /** @test */
    public function url_should_reuse_the_already_existing_query_parameters(): void
    {
        $this->request->query->set('key', 'value');
        $instance = $this->paginator();

        $this->assertEquals('http://localhost?key=value&page=1', $instance->testUrl(1));
    }

    /** @test */
    public function to_array_should_return_array_response(): void
    {
        $instance = $this->paginator();
        $array = $instance->toArray();

        $this->assertTrue(is_array($array));
        $this->assertArrayHasKey('current_page', $array);
        $this->assertArrayHasKey('data', $array);
        $this->assertArrayHasKey('from', $array);
        $this->assertArrayHasKey('last_page', $array);
        $this->assertArrayHasKey('next_page_url', $array);
        $this->assertArrayHasKey('path', $array);
        $this->assertArrayHasKey('per_page', $array);
        $this->assertArrayHasKey('prev_page_url', $array);
        $this->assertArrayHasKey('to', $array);
        $this->assertArrayHasKey('total', $array);
    }

    /** @test */
    public function json_serialize_is_just_wrapper_for_to_array(): void
    {
        $instance = $this->paginator();

        $this->assertEquals($instance->toArray(), $instance->jsonSerialize());
    }

    /** @test */
    public function to_json_should_make_json_string(): void
    {
        $instance = $this->paginator();
        $json = $instance->toJson();

        $this->assertEquals($instance->toArray(), json_decode($json, true));
    }

    protected function collection(int $count = 10): Collection
    {
        $array = [];

        for ($i = 1; $i <= $count; $i++) {
            $array[] = "item{$i}";
        }

        return Collection::make($array);
    }

    protected function paginator(
        Collection $collection = null,
        int $perPage = 15,
        int $currentPage = null
    ): PaginatorTestWrapper {
        if (is_null($collection)) {
            $collection = $this->collection();
        }

        return new PaginatorTestWrapper(
            new LengthAwarePaginator($collection, $collection->count(), $perPage, $currentPage), $this->request
        );
    }
}