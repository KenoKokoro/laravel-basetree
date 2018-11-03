<?php


namespace BaseTree\Controllers\Laravel;


use BaseTree\Controllers\BaseControllerMethods as BaseTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BaseController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;
    use BaseTrait;

    /**
     * Return the controller action method
     * @param Request $request
     * @return string
     */
    protected function getActionMethod(Request $request): string
    {
        return $request->route()->getActionMethod();
    }
}