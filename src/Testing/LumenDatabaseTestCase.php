<?php


namespace BaseTree\Testing;


use Laravel\Lumen\Testing\TestCase;
use PHPUnit\Framework\Assert as PHPUnit;

class LumenDatabaseTestCase extends TestCase
{
    use BaseDatabaseMethods;

    /**
     * Return array from json lumen response
     * @param string|null $key
     * @return array
     */
    protected function jsonResponse(string $key = null): array
    {
        $decodedResponse = json_decode($this->response->getContent(), true);

        if (is_null($decodedResponse) or $decodedResponse === false) {
            if ($this->response->exception) {
                throw $this->response->exception;
            } else {
                PHPUnit::fail('Invalid JSON was returned from the route.');
            }
        }

        return data_get($decodedResponse, $key);
    }
}