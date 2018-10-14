<?php


namespace BaseTree\Controllers\Laravel;


use BaseTree\Controllers\BaseJsonMethods;
use BaseTree\Resources\Contracts\ResourceCallbacks;
use BaseTree\Resources\Contracts\ResourceScreen;
use BaseTree\Resources\Contracts\ResourceValidations;
use Illuminate\Foundation\Bus\DispatchesJobs;

class JsonController extends BaseController
{
    use BaseJsonMethods, DispatchesJobs;

    /**
     * @var ResourceScreen|ResourceValidations|ResourceCallbacks
     */
    protected $resource;

    public function __construct(ResourceScreen $resource)
    {
        $this->resource = $resource;
        $this->resource->setRequestOperations(request());
    }
}