<?php


namespace BaseTree\Exception;


use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Handler
{
    /**
     * A list of the exception types that should not be reported.
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     * @param  \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * @param Request $request
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

    /**
     * Convert an authentication exception into an unauthenticated response.
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Auth\AuthenticationException $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }

    protected function jsonException(Exception $exception)
    {
        if ($exception instanceof ValidationException) {
            /** @var $exception ValidationException */
            return \Json::unprocessableEntity($exception->getMessage(), [
                'validator' => $exception->validator->getMessageBag()
            ]);
        } elseif ($exception instanceof ModelNotFoundException) {
            return \Json::notFound($exception->getMessage());
        } elseif ($exception instanceof AuthorizationException
                  or $exception instanceof UnauthorizedHttpException
                  or $exception instanceof AuthenticationException) {
            return \Json::unauthorized($exception->getMessage());
        } elseif ($exception instanceof NotFoundHttpException) {
            return \Json::internalError('Route does not exist');
        } elseif ($exception instanceof MethodNotAllowedHttpException) {
            return \Json::internalError('Method not allowed on this route');
        }

        return \Json::internalError($exception->getMessage());
    }
}