<?php


namespace BaseTree\Resources\Contracts;


use Illuminate\Http\Request;

interface ResourceValidation
{
    /**
     * @param Request|null $request
     * @return array
     */
    public function storeRules($request = null): array;

    /**
     * @param mixed $id
     * @param Request|null $request
     * @return array
     */
    public function updateRules($id, $request = null): array;

    /**
     * @param mixed $id
     * @param null $request
     * @return array
     */
    public function destroyRules($id, $request = null): array;
}