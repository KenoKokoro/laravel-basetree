<?php


namespace BaseTree\Testing\Traits\Assertions;


trait FieldValidationMessages
{
    /**
     * Assert that a field is required in the json response
     * @param array       $messages
     * @param string      $key
     * @param string|null $fieldName
     * @param int         $index
     */
    public function assertFieldRequired(
        array $messages,
        string $key,
        string $fieldName = null,
        int $index = 0
    ): void {
        if (is_null($fieldName)) {
            $fieldName = $key;
        }
        $this->assertEquals(str_replace(':attribute', $fieldName, trans('validation.required')),
            $messages[$key][$index]);
    }

    /**
     * Assert that the field is an email
     * @param array  $messages
     * @param string $key
     * @param int    $index
     */
    public function assertEmailField(array $messages, string $key = 'email', int $index = 0): void
    {
        $this->assertEquals(str_replace(':attribute', $key, trans('validation.email')), $messages[$key][$index]);
    }

    /**
     * Assert a password confirmation validation rule
     * @param array  $messages
     * @param string $key
     * @param int    $index
     */
    public function assertPasswordIsConfirmed(array $messages, string $key = 'password', int $index = 0): void
    {
        $this->assertEquals(str_replace(':attribute', $key, trans('validation.confirmed')), $messages[$key][$index]);
    }

    /**
     * Assert a in validation rule
     * @param array       $messages
     * @param string      $key
     * @param string|null $fieldName
     * @param int         $index
     */
    public function assertValueIn(array $messages, string $key, string $fieldName = null, int $index = 0): void
    {
        if (is_null($fieldName)) {
            $fieldName = $key;
        }
        $this->assertEquals(str_replace(':attribute', $fieldName, trans('validation.in')), $messages[$key][$index]);
    }

    /**
     * Assert a exists validation rule
     * @param array       $messages
     * @param string      $key
     * @param string|null $fieldName
     * @param int         $index
     */
    public function assertFieldExist(array $messages, string $key, string $fieldName = null, int $index = 0): void
    {
        if (is_null($fieldName)) {
            $fieldName = $key;
        }
        $this->assertEquals(str_replace(':attribute', $fieldName, trans('validation.exists')), $messages[$key][$index]);
    }

    /**
     * Assert array validation rule
     * @param array       $messages
     * @param string      $key
     * @param string|null $fieldName
     * @param int         $index
     */
    public function assertFieldIsArray(array $messages, string $key, string $fieldName = null, int $index = 0): void
    {
        if (is_null($fieldName)) {
            $fieldName = $key;
        }
        $this->assertEquals(str_replace(':attribute', $fieldName, trans('validation.array')), $messages[$key][$index]);
    }

    /**
     * Assert unique validation rule
     * @param array       $messages
     * @param string      $key
     * @param string|null $fieldName
     * @param int         $index
     */
    public function assertValueIsUnique(array $messages, string $key, string $fieldName = null, int $index = 0): void
    {
        if (is_null($fieldName)) {
            $fieldName = $key;
        }
        $this->assertEquals(str_replace(':attribute', $fieldName, trans('validation.unique')), $messages[$key][$index]);
    }
}