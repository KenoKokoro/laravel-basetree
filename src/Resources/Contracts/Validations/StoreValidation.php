<?php


namespace BaseTree\Resources\Contracts\Validations;


use Illuminate\Http\Request;

interface StoreValidation
{
    /**
     * @param Request|null $request
     * @return array
     */
    public function storeRules(Request $request = null): array;
}