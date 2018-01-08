<?php


namespace BaseTree\Resources\Contracts\Validations;


use Illuminate\Http\Request;

interface DestroyValidation
{

    /**
     * @param mixed $id
     * @param Request|null $request
     * @return array
     */
    public function destroyRules($id, Request $request = null): array;
}