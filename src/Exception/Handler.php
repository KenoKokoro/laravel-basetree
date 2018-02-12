<?php


namespace BaseTree\Exception;


use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as LaravelHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Json;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Handler extends LaravelHandler
{
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

    /**
     * @param Request   $request
     * @param Exception $exception
     * @return mixed
     */
    public function render($request, Exception $exception)
    {
        if ($request->expectsJson()) {
            return $this->jsonException($exception);
        }

        return parent::render($request, $exception);
    }

    protected function jsonException(Exception $exception)
    {
        if ($exception instanceof ValidationException) {
            /** @var $exception ValidationException */
            return Json::unprocessableEntity($exception->getMessage(), [
                'validator' => $exception->validator->getMessageBag()
            ]);
        } elseif ($exception instanceof ModelNotFoundException) {
            return Json::notFound($exception->getMessage());
        } elseif ($exception instanceof AuthorizationException
                  or $exception instanceof UnauthorizedHttpException) {
            return Json::forbidden($exception->getMessage());
        } elseif ($exception instanceof AuthenticationException) {
            return Json::unauthorized($exception->getMessage());
        } elseif ($exception instanceof NotFoundHttpException) {
            return Json::internalError('Route does not exist.');
        } elseif ($exception instanceof MethodNotAllowedHttpException) {
            return Json::internalError('Method not allowed on this route.');
        }

        return Json::internalError($exception->getMessage());
    }
}