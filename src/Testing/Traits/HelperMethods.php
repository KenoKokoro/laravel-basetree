<?php


namespace BaseTree\Testing\Traits;


trait HelperMethods
{
    /**
     * Call the given URI with a JSON POST request.
     * @param  string $uri
     * @param  array $data
     * @param  array $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function jsonPost($uri, array $data = [], array $headers = [])
    {
        return $this->json('POST', $uri, $data, $headers);
    }

    /**
     * Call the given URI with a JSON PUT request.
     * @param  string $uri
     * @param  array $data
     * @param  array $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function jsonPut($uri, array $data = [], array $headers = [])
    {
        return $this->json('PUT', $uri, $data, $headers);
    }

    /**
     * Call the given URI with a JSON DELETE request.
     * @param  string $uri
     * @param  array $data
     * @param  array $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function jsonDestroy($uri, array $data = [], array $headers = [])
    {
        return $this->json('DELETE', $uri, $data, $headers);
    }

    /**
     * Call the given URI with a JSON GET request.
     * @param  string $uri
     * @param  array $data
     * @param  array $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function jsonGet($uri, array $data = [], array $headers = [])
    {
        return $this->json('GET', $uri, $data, $headers);
    }
}