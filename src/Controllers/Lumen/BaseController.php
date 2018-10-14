<?php


namespace BaseTree\Controllers\Lumen;


use BaseTree\Controllers\BaseControllerMethods as BaseTrait;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class BaseController extends Controller
{
    use BaseTrait;

    /**
     * Return the controller action method
     * @param Request $request
     * @return string
     */
    protected function getActionMethod(Request $request): string
    {
        $controller = $request->route()[1]['uses'];

        return explode('@', $controller)[1];
    }
}