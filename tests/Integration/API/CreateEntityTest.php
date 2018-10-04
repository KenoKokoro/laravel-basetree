<?php


namespace BaseTree\Tests\Integration\API;


use BaseTree\Responses\JsonResponse;
use BaseTree\Tests\Integration\IntegrationTestCase;

class CreateEntityTest extends IntegrationTestCase
{
    /** @test */
    public function not_defined_method_should_return_not_found_response(): void
    {
        $response = $this->jsonGet('missing-route-');
        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND)->assertJsonStructure(['message']);
        $this->assertEquals('Route does not exist.', $response->json('message'));
    }
}