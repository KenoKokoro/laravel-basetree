<?php


namespace BaseTree\Tests\Fake\Unit;


use BaseTree\Resources\BaseResource;
use BaseTree\Resources\Contracts\ResourceValidations;
use Illuminate\Http\Request;

class DummyResourceWithValidationsRules extends BaseResource implements ResourceValidations
{
    public function storeRules(Request $request = null): array
    {
        return [];
    }

    public function updateRules($id, Request $request = null): array
    {
        return [];
    }

    public function destroyRules($id, Request $request = null): array
    {
        return [];
    }
}