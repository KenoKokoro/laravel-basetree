<?php


namespace BaseTree\Responses;


use BaseTree\Modules\Log\ClientLogger;
use Illuminate\Http\JsonResponse as LaravelJsonResponse;
use Illuminate\Support\Facades\App;

class JsonResponse extends LaravelJsonResponse
{
    const STATUS_TRUE = true;
    const STATUS_FALSE = false;

    public function success(string $message = '', array $append = []): parent
    {
        if (empty($message)) {
            $message = 'Successfully executed.';
        }
        $response = $this->getResponse($message);

        if ( ! empty($append)) {
            $response = array_merge($response, $append);
        }

        return $this->parentInstance($response, parent::HTTP_OK);
    }

    public function created(string $message = '', array $append = []): parent
    {
        if (empty($message)) {
            $message = 'Successfully created.';
        }
        $response = $this->getResponse($message);

        if ( ! empty($append)) {
            $response = array_merge($response, $append);
        }

        return $this->parentInstance($response, parent::HTTP_CREATED);
    }

    public function forbidden(string $message = ''): parent
    {
        if (empty($message)) {
            $message = 'Forbidden.';
        }
        $response = $this->getResponse($message);

        return $this->parentInstance($response, parent::HTTP_FORBIDDEN);
    }

    public function unauthorized(string $message = ''): parent
    {
        if (empty($message)) {
            $message = 'Unauthorized.';
        }
        $response = $this->getResponse($message);

        return $this->parentInstance($response, parent::HTTP_UNAUTHORIZED);
    }

    public function unprocessableEntity(string $message = '', array $append = []): parent
    {
        if (empty($message)) {
            $message = 'Unprocessable entity.';
        }
        $response = $this->getResponse($message);

        if ( ! empty($append)) {
            $response = array_merge($response, $append);
        }

        return $this->parentInstance($response, parent::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function notFound(string $message = '', array $append = []): parent
    {
        if (empty($message)) {
            $message = 'Not found.';
        }
        $response = $this->getResponse($message);

        if ( ! empty($append)) {
            $response = array_merge($response, $append);
        }

        return $this->parentInstance($response, parent::HTTP_NOT_FOUND);
    }

    public function internalError(string $message = '', array $append = []): parent
    {
        if (empty($message)) {
            $message = 'Internal error.';
        }

        $response = $this->getResponse($message);

        if ( ! empty($append)) {
            $response = array_merge($response, $append);
        }

        return $this->parentInstance($response, parent::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function notAllowed(string $message)
    {
        if (empty($message)) {
            $message = 'Method not allowed.';
        }

        $response = $this->getResponse($message);

        if ( ! empty($append)) {
            $response = array_merge($response, $append);
        }

        return $this->parentInstance($response, parent::HTTP_METHOD_NOT_ALLOWED);
    }

    private function getResponse(string $message): array
    {
        return [
            'message' => $message
        ];
    }

    private function parentInstance(array $data, int $code): parent
    {
        $response = new parent($data, $code);

        if (config('base-tree.log')) {
            /** @var ClientLogger $logger */
            $logger = App::make('BaseTree\Modules\Log\ClientLogger');
            $logger->writeIncoming($response, request());
        }

        return $response;
    }
}