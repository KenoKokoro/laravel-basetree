<?php

namespace BaseTree\Exception;

use BaseTree\Responses\JsonResponse;
use Laravel\Lumen\Exceptions\Handler;
use Throwable;

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
     * @param Throwable $e
     * @return
     * @throws Throwable
     */
    public function report(Throwable $e)
    {
        return parent::report($e);
    }

    protected function jsonResponse(): JsonResponse
    {
        return app(JsonResponse::class);
    }
}