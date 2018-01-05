<?php


namespace BaseTree\Datatable;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Http\JsonResponse;
use JsonSerializable;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Facades\DataTables;

class Creator implements Arrayable, JsonSerializable, Jsonable
{
    /**
     * @var JsonResponse
     */
    protected $response;

    protected $query;

    /**
     * @var DataTables|DataTableAbstract
     */
    protected $datatable;

    public function create($mDataSupport = true, $query = null)
    {
        if ( ! is_null($query)) {
            $this->query = $query;
        }

        $this->datatable = $this->of($this->query);

        $this->callback();

        return $this->make($mDataSupport);
    }

    /**
     * @param $query
     * @return DataTableAbstract
     */
    public function of($query)
    {
        return DataTables::of($query);
    }

    public function make($mDataSupport = true)
    {
        $this->response = $this->datatable->make($mDataSupport);

        return $this;
    }

    protected function callback()
    {
        return $this;
    }

    public function toArray()
    {
        return json_decode($this->toJson(), true);
    }

    public function toJson($options = 0)
    {
        return $this->response->content();
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}