<?php


namespace BaseTree\Tests\Integration\Lumen\API;


use BaseTree\Responses\JsonResponse;
use BaseTree\Tests\Integration\LumenTestCase;

class UsersControllerTest extends LumenTestCase
{
    /** @test */
    public function it_should_return_all_records_for_the_users_endpoint(): void
    {
        $response = $this->jsonGet('users');
        $response->assertResponseStatus(JsonResponse::HTTP_OK);

        $this->assertCount(15, $response->jsonResponse('data'));
    }

    /** @test */
    public function it_should_return_paginated_results_if_requested(): void
    {
        $response = $this->jsonGet('users?paginate=1&perPage=4&page=2');
        $response->assertResponseStatus(JsonResponse::HTTP_OK);

        $data = $response->jsonResponse('data');
        $this->assertCount(4, $data['data']);
        $this->assertEquals(2, $data['current_page']);
        $this->assertEquals(5, $data['from']);
        $this->assertEquals(8, $data['to']);
        $this->assertEquals(15, $data['total']);
    }

    /** @test */
    public function it_should_return_data_tables_response_if_requested(): void
    {
        $response = $this->jsonGet('users?datatable=1');
        $response->assertResponseStatus(JsonResponse::HTTP_OK);

        $data = $response->jsonResponse('data');
        $this->assertCount(15, $data['data']);
    }
}