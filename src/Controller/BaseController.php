<?php


namespace BaseTree\Controller;


use BaseTree\Responses\HttpResponse;
use BaseTree\Responses\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $response;

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
}