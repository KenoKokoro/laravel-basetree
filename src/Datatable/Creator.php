<?php


namespace BaseTree\Datatable;

use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\Facades\DataTables;

class Creator
{
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
        return $this->datatable->make($mDataSupport);
    }

    protected function callback()
    {
        return $this;
    }
}