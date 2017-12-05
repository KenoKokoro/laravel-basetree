<?php


namespace BaseTree\Responses;


use Illuminate\Http\JsonResponse as LaravelJsonResponse;

class JsonResponse extends LaravelJsonResponse
{
    const STATUS_TRUE = true;
    const STATUS_FALSE = false;

    private $status = self::STATUS_TRUE;

    public function success($message = '', $append = [])
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

    public function created($message = '', $append = [])
    {
        $this->status = self::STATUS_TRUE;
        if (empty($message)) {
            $message = 'Successfully created.';
        }
        $data = $this->getResponse($message);

        if ( ! empty($append)) {
            $data = array_merge($data, $append);
        }

        return $this->parentInstance($data, parent::HTTP_CREATED);
    }

    public function forbidden($message = '')
    {
        $this->status = self::STATUS_FALSE;
        if (empty($message)) {
            $message = 'Forbidden.';
        }
        $data = $this->getResponse($message);

        return $this->parentInstance($data, parent::HTTP_FORBIDDEN);
    }

    public function unauthorized($message = '')
    {
        $this->status = self::STATUS_FALSE;
        if (empty($message)) {
            $message = 'Unauthorized.';
        }
        $data = $this->getResponse($message);

        return $this->parentInstance($data, parent::HTTP_UNAUTHORIZED);
    }

    public function unprocessableEntity($message, $append = [])
    {
        $this->status = self::STATUS_FALSE;
        if (empty($message)) {
            $message = 'Unprocessable entity.';
        }
        $data = $this->getResponse($message);

        if ( ! empty($append)) {
            $data = array_merge($data, $append);
        }

        return $this->parentInstance($data, parent::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function notFound($message)
    {
        $this->status = self::STATUS_FALSE;
        if (empty($message)) {
            $message = 'Not found.';
        }

        return $this->parentInstance($this->getResponse($message), parent::HTTP_NOT_FOUND);
    }

    public function internalError($message)
    {
        $this->status = self::STATUS_FALSE;
        if (empty($message)) {
            $message = 'Internal error.';
        }

        return $this->parentInstance($this->getResponse($message), parent::HTTP_INTERNAL_SERVER_ERROR);
    }

    private function getResponse($message)
    {
        return [
            'status' => $this->status,
            'message' => $message
        ];
    }

    private function parentInstance($data, $code)
    {
        return new parent($data, $code);
    }
}