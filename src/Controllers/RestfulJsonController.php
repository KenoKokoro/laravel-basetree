<?php


namespace BaseTree\Controllers;


use BaseTree\Resources\Contracts\ResourceCallbacks;
use BaseTree\Resources\Contracts\ResourceScreen;
use BaseTree\Resources\Contracts\ResourceValidations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RestfulJsonController extends BaseController
{
    /**
     * @var ResourceScreen|ResourceValidations|ResourceCallbacks
     */
    protected $resource;

    public function __construct(ResourceScreen $resource)
    {
        $this->resource = $resource;
        $this->resource->setRequestOperations(request());
    }

    public function index()
    {
        $entities = $this->resource->index();

        return $this->response()->success('', ['data' => $entities]);
    }

    public function show(Request $request, $id)
    {
        $entity = $this->resource->show($id, $request->get('fields') ?? []);

        return $this->response()->success('', ['data' => $entity]);
    }

    public function store(Request $request)
    {
        if ($this->resource instanceof ResourceValidations) {
            $this->validate($request, $this->resource->storeRules($request));
        }

        DB::beginTransaction();
        $entity = $this->resource->store($request->all());
        DB::commit();

        return $this->response()->created('', ['data' => $entity]);
    }

    public function update(Request $request, $id)
    {
        $entity = $this->resource->findWithoutRelations($id);

        if ($this->resource instanceof ResourceValidations) {
            $this->validate($request, $this->resource->updateRules($id, $request));
        }

        DB::beginTransaction();
        $updated = $this->resource->updateEntity($entity, $request->all());
        DB::commit();

        return $this->response()->success('', ['data' => $updated]);
    }

    public function destroy(Request $request, $id)
    {
        $entity = $this->resource->findWithoutRelations($id);

        if ($this->resource instanceof ResourceValidations) {
            $this->validate($request, $this->resource->destroyRules($id, $request));
        }

        DB::beginTransaction();
        $this->resource->destroy($entity, $request->all());
        DB::commit();

        return $this->response()->success();
    }
}