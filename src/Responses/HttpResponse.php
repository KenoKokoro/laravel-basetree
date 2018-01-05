<?php


namespace BaseTree\Responses;


use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;

class HttpResponse
{
    const SUCCESS = 'success';
    const DANGER = 'danger';

    private $status = self::SUCCESS;

    private $to = null;

    public function success($to = null, $message = null, $append = []): RedirectResponse
    {
        $this->status = self::SUCCESS;
        $this->to = $to;

        if (empty($message)) {
            $message = 'Successfully executed.';
        }

        $with = $this->generateFlashMessage($message, $append);

        return $this->redirect()->with($with);
    }

    public function danger($to = null, $message = null, $append = [], $input = [], $errors = []): RedirectResponse
    {
        $this->status = self::DANGER;
        $this->to = $to;

        if (empty($message)) {
            $message = 'Danger.';
        }

        $with = $this->generateFlashMessage($message, $append);

        return $this->redirect()->with($with)->withInput($input)->withErrors($errors);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirect()
    {
        if (empty($this->to)) {
            $this->to = URL::previous();
        }

        return response()->redirectTo($this->to);
    }

    private function generateFlashMessage($message, $append = []): array
    {
        $flash = [
            'status' => $this->status,
            'message' => $message
        ];

        if ( ! empty($append)) {
            $flash = array_merge($flash, $append);
        }

        return $flash;
    }
}