<?php


namespace BaseTree\Exception;


use BaseTree\Responses\JsonResponse;
use Exception;
use Laravel\Lumen\Exceptions\Handler;

class LumenHandler extends Handler
{
    use JsonException;

    /**
     * A list of the exception types that are not reported.
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * @param Exception $e
     * @throws Exception
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    protected function jsonResponse(): JsonResponse
    {
        return app(JsonResponse::class);
    }
}