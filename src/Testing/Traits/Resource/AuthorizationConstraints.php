<?php


namespace BaseTree\Testing\Traits\Resource;


use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\JsonResponse;

trait AuthorizationConstraints
{
    /** @test */
    public function regular_user_can_not_access_index_route()
    {
        $uri = route("{$this->routeKey}.index");
        /** @var User $auth */
        $auth = create(config('base-tree.auth-model'));

        /** @var TestResponse $response */
        $response = $this->getJson($uri, $this->jwtAuthHeader($auth));
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN)->assertJsonStructure(['status', 'message']);
    }

    /** @test */
    public function regular_user_can_not_access_store_route()
    {
        $uri = route("{$this->routeKey}.store");
        /** @var User $auth */
        $auth = create(config('base-tree.auth-model'));

        /** @var TestResponse $response */
        $response = $this->postJson($uri, [], $this->jwtAuthHeader($auth));
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN)->assertJsonStructure(['status', 'message']);
    }

    /** @test */
    public function regular_user_can_not_access_destroy_route()
    {
        $uri = route("{$this->routeKey}.destroy", [1]);
        /** @var User $auth */
        $auth = create(config('base-tree.auth-model'));

        /** @var TestResponse $response */
        $response = $this->jsonDestroy($uri, [], $this->jwtAuthHeader($auth));
        $response->assertStatus(JsonResponse::HTTP_FORBIDDEN)->assertJsonStructure(['status', 'message']);
    }
}