<?php


namespace BaseTree\Responses;


use Illuminate\Http\JsonResponse as LaravelJsonResponse;

class JsonResponse extends LaravelJsonResponse
{
    const STATUS_TRUE = true;
    const STATUS_FALSE = false;

    private $status = self::STATUS_TRUE;

    public function success(string $message = '', array $append = []): parent
    {
        $this->status = self::STATUS_TRUE;

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
        $this->status = self::STATUS_TRUE;
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
        $this->status = self::STATUS_FALSE;
        if (empty($message)) {
            $message = 'Forbidden.';
        }
        $response = $this->getResponse($message);

        return $this->parentInstance($response, parent::HTTP_FORBIDDEN);
    }

    public function unauthorized(string $message = ''): parent
    {
        $this->status = self::STATUS_FALSE;
        if (empty($message)) {
            $message = 'Unauthorized.';
        }
        $response = $this->getResponse($message);

        return $this->parentInstance($response, parent::HTTP_UNAUTHORIZED);
    }

    public function unprocessableEntity(string $message = '', array $append = []): parent
    {
        $this->status = self::STATUS_FALSE;
        if (empty($message)) {
            $message = 'Unprocessable entity.';
        }
        $response = $this->getResponse($message);

        if ( ! empty($append)) {
            $response = array_merge($response, $append);
        }

        return $this->parentInstance($response, parent::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function notFound(string $message = ''): parent
    {
        $this->status = self::STATUS_FALSE;
        if (empty($message)) {
            $message = 'Not found.';
        }

        return $this->parentInstance($this->getResponse($message), parent::HTTP_NOT_FOUND);
    }

    public function internalError(string $message = ''): parent
    {
        $this->status = self::STATUS_FALSE;
        if (empty($message)) {
            $message = 'Internal error.';
        }

        return $this->parentInstance($this->getResponse($message), parent::HTTP_INTERNAL_SERVER_ERROR);
    }

    private function getResponse(string $message): array
    {
        return [
            'status' => $this->status,
            'message' => $message
        ];
    }

    private function parentInstance(array $data, int $code): parent
    {
        return new parent($data, $code);
    }
}