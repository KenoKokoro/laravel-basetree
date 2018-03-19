<?php


namespace BaseTree\Controllers;


use BaseTree\Resources\Contracts\ResourceCallbacks;
use BaseTree\Resources\Contracts\ResourceScreen;
use BaseTree\Resources\Contracts\ResourceValidations;
use BaseTree\Resources\Contracts\Validations\DestroyValidation;
use BaseTree\Resources\Contracts\Validations\StoreValidation;
use BaseTree\Resources\Contracts\Validations\UpdateValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use URL;

class WebController extends BaseController
{
    /**
     * @var ResourceScreen|ResourceValidations|ResourceCallbacks
     */
    protected $resource;

    protected $viewPath;

    protected $redirectTo;

    public function __construct(ResourceScreen $resource)
    {
        $this->resource = $resource;
        $this->resource->setRequestOperations(request());
    }

    public function index(Request $request)
    {
        $this->checkAccess('view', $this->resource->authorizationKey(), $this->resource->model());
        $entities = $this->resource->index();

        return view("{$this->viewPath}.index", compact('entities'));
    }

    public function show(Request $request, $id)
    {
        $entity = $this->resource->show($id, $request->get('fields') ?? []);
        $this->checkAccess('manage', $this->resource->authorizationKey(), $entity);

        return view("{$this->viewPath}.show", compact('entity'));
    }

    public function create(Request $request)
    {
        return view("{$this->viewPath}.create");
    }

    public function store(Request $request)
    {
        $this->checkAccess('manage', $this->resource->authorizationKey(), $this->resource->model());
        if ($this->resource instanceof StoreValidation) {
            $this->validate($request, $this->resource->storeRules($request));
        }

        DB::beginTransaction();
        $this->resource->store($request->all());
        DB::commit();

        return $this->response()->success($this->redirectTo, 'Successfully created.');
    }

    public function edit(Request $request, $id)
    {
        $entity = $this->resource->show($id, $request->get('fields') ?? []);
        $this->checkAccess('manage', $this->resource->authorizationKey(), $entity);

        return view("{$this->viewPath}.edit", compact('entity'));
    }

    public function update(Request $request, $id)
    {
        $entity = $this->resource->findWithoutRelations($id);
        $this->checkAccess('manage', $this->resource->authorizationKey(), $entity);

        if ($this->resource instanceof UpdateValidation) {
            $this->validate($request, $this->resource->updateRules($id, $request));
        }

        DB::beginTransaction();
        $this->resource->updateEntity($entity, $request->all());
        DB::commit();

        return $this->response()->success($this->redirectTo, 'Successfully updated.');
    }

    public function destroy(Request $request, $id)
    {
        $entity = $this->resource->findWithoutRelations($id);
        $this->checkAccess('manage', $this->resource->authorizationKey(), $entity);

        if ($this->resource instanceof DestroyValidation) {
            $this->validate($request, $this->resource->destroyRules($id, $request));
        }

        DB::beginTransaction();
        $this->resource->destroy($entity, $request->all());
        DB::commit();

        return $this->response()->success($this->redirectTo, 'Successfully updated.');
    }
}