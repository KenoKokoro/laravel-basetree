<?php


namespace BaseTree\Resources\Contracts\Validations;


use Illuminate\Http\Request;

interface UpdateValidation
{
    /**
     * @param mixed $id
     * @param Request|null $request
     * @return array
     */
    public function updateRules($id, Request $request = null): array;
}