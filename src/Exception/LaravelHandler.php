<?php


namespace BaseTree\Exception;


use BaseTree\Responses\JsonResponse;
use Exception;
use Illuminate\Foundation\Exceptions\Handler;

class LaravelHandler extends Handler
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
     * A list of the inputs that are never flashed for validation exceptions.
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     * @param  \Exception $exception
     * @return void
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    protected function jsonResponse(): JsonResponse
    {
        return $this->container->make(JsonResponse::class);
    }
}