<?php


namespace BaseTree\Controllers;

use BaseTree\Resources\Contracts\ResourceScreen;
use BaseTree\Resources\Contracts\Validations\DestroyValidation;
use BaseTree\Resources\Contracts\Validations\StoreValidation;
use BaseTree\Resources\Contracts\Validations\UpdateValidation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait BaseJsonMethods
{
    public function __construct(ResourceScreen $resource)
    {
        $this->resource = $resource;
        $this->resource->setRequestOperations(request());
    }

    /**
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws AuthorizationException
     */
    public function index(): JsonResponse
    {
        $this->checkAccess('view', $this->resource->authorizationKey(), $this->resource->model());
        $entities = $this->resource->index();

        return $this->response()->success('', ['data' => $entities]);
    }

    /**
     * @param Request $request
     * @param         $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws AuthorizationException
     */
    public function show(Request $request, $id): JsonResponse
    {
        $entity = $this->resource->show($id, $request->get('fields') ?? []);
        $this->checkAccess('view', $this->resource->authorizationKey(), $entity);

        return $this->response()->success('', ['data' => $entity]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $this->checkAccess('manage', $this->resource->authorizationKey(), $this->resource->model());
        if ($this->resource instanceof StoreValidation) {
            $this->validate($request, $this->resource->storeRules($request));
        }

        app('db')->beginTransaction();
        $entity = $this->resource->store($request->all());
        app('db')->commit();

        return $this->response()->created('', ['data' => $entity]);
    }

    /**
     * @param Request $request
     * @param         $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id): JsonResponse
    {
        $entity = $this->resource->findWithoutRelations($id);
        $this->checkAccess('manage', $this->resource->authorizationKey(), $entity);

        if ($this->resource instanceof UpdateValidation) {
            $this->validate($request, $this->resource->updateRules($id, $request));
        }

        app('db')->beginTransaction();
        $updated = $this->resource->updateEntity($entity, $request->all());
        app('db')->commit();

        return $this->response()->success('', ['data' => $updated]);
    }

    /**
     * @param Request $request
     * @param         $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $entity = $this->resource->findWithoutRelations($id);
        $this->checkAccess('manage', $this->resource->authorizationKey(), $entity);

        if ($this->resource instanceof DestroyValidation) {
            $this->validate($request, $this->resource->destroyRules($id, $request));
        }

        app('db')->beginTransaction();
        $this->resource->destroy($entity, $request->all());
        app('db')->commit();

        return $this->response()->success();
    }

    protected function response(): \BaseTree\Responses\JsonResponse
    {
        return new \BaseTree\Responses\JsonResponse();
    }
}