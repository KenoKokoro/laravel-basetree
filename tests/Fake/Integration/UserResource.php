<?php


namespace BaseTree\Tests\Fake\Integration;


use BaseTree\Resources\BaseResource;
use BaseTree\Resources\Contracts\Validations\StoreValidation;
use Illuminate\Http\Request;

class UserResource extends BaseResource implements StoreValidation
{
    public function __construct(UserRepository $repository)
    {
        parent::__construct($repository);
    }

    public function storeRules(Request $request = null): array
    {
        return [
            'name' => 'required|max:255',
            'email' => 'required|unique:users,email|max:255',
            'password' => 'required|confirmed|max:40'
        ];
    }
}