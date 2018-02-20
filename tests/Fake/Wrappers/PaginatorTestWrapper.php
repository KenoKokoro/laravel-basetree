<?php


namespace BaseTree\Tests\Fake\Wrappers;


use BaseTree\Eloquent\Paginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;

class PaginatorTestWrapper extends Paginator
{
    public function __construct(LengthAwarePaginatorContract $lengthAwarePaginator, $request = null)
    {
        parent::__construct($lengthAwarePaginator, $request);
    }

    public function testNextPageUrl()
    {
        return parent::nextPageUrl();
    }

    public function testPath()
    {
        return parent::path();
    }

    public function testPreviousPageUrl()
    {
        return parent::previousPageUrl();
    }

    public function testUrl(int $page)
    {
        return parent::url($page);
    }
}