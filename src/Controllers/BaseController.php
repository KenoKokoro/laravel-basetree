<?php


namespace BaseTree\Controllers;


use BaseTree\Responses\HttpResponse;
use BaseTree\Responses\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $permission;

    /**
     * Used to exclude methods from being authorized on the restful resource
     * @var array
     */
    protected $excludedAuthorization = [];

    /**
     * @return JsonResponse|HttpResponse
     */
    protected function response()
    {
        if (request()->expectsJson()) {
            return new JsonResponse;
        }

        return new HttpResponse;
    }

    /**
     * @param string $ability
     * @param string $key
     * @param array  $extra
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function checkAccess(string $ability, string $key, ...$extra): void
    {
        $method = request()->route()->getActionMethod();
        if (config('base-tree.authorization') and ! in_array($method, $this->excludedAuthorization)) {
            $this->authorize($ability, array_merge([$key, $this->permission], $extra));
        }
    }
}