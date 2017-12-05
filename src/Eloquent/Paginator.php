<?php


namespace BaseTree\Eloquent;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use JsonSerializable;

class Paginator implements Arrayable, JsonSerializable, Jsonable
{
    /**
     * @var LengthAwarePaginator
     */
    private $paginator;

    /**
     * @var Request
     */
    private $request;

    public function __construct(LengthAwarePaginatorContract $lengthAwarePaginator, $request = null)
    {
        $this->paginator = $lengthAwarePaginator;
        $this->request = $request ?? request();
    }

    public function toArray()
    {
        return [
            'current_page' => $this->paginator->currentPage(),
            'data' => $this->paginator->items(),
            'from' => $this->paginator->firstItem(),
            'last_page' => $this->paginator->lastPage(),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->path(),
            'per_page' => $this->paginator->perPage(),
            'prev_page_url' => $this->previousPageUrl(),
            'to' => $this->paginator->lastItem(),
            'total' => $this->paginator->total(),
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    protected function nextPageUrl()
    {
        if ($this->paginator->hasMorePages()) {
            return $this->url($this->paginator->currentPage() + 1);
        }

        return null;
    }

    protected function path()
    {
        return $this->request->url();
    }

    protected function previousPageUrl()
    {
        if ($this->paginator->currentPage() > 1) {
            return $this->url($this->paginator->currentPage() - 1);
        }

        return null;
    }

    protected function url($page)
    {
        if ($page <= 0) {
            $page = 1;
        }

        $query = array_merge($this->request->query->all(), [$this->paginator->getPageName() => $page]);

        return $this->path() . "?" . http_build_query($query);
    }
}