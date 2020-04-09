<?php

namespace BaseTree\Exception;

use BaseTree\Responses\JsonResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

trait JsonException
{
    /**
     * @param Request   $request
     * @param Throwable $exception
     * @return mixed
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            return $this->jsonException($exception);
        }

        return parent::render($request, $exception);
    }

    protected function jsonException(Throwable $exception)
    {
        /** @var JsonResponse $json */
        $json = $this->jsonResponse();

        if ($exception instanceof ValidationException) {
            /** @var $exception ValidationException */
            return $json->unprocessableEntity($exception->getMessage(), [
                'validator' => $exception->validator->getMessageBag(),
            ]);
        } elseif ($exception instanceof ModelNotFoundException) {
            return $json->notFound($exception->getMessage());
        } elseif ($exception instanceof AuthorizationException
                  or $exception instanceof UnauthorizedHttpException) {
            return $json->forbidden($exception->getMessage());
        } elseif ($exception instanceof AuthenticationException) {
            return $json->unauthorized($exception->getMessage());
        } elseif ($exception instanceof NotFoundHttpException) {
            return $json->notFound('Route does not exist.');
        } elseif ($exception instanceof MethodNotAllowedHttpException) {
            return $json->notAllowed('Method not allowed on this route.');
        }

        return $json->internalError($exception->getMessage());
    }
}