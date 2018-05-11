<?php


namespace BaseTree\Testing\Traits\Assertions;


trait FieldValidationMessages
{
    public function assertFieldRequired(
        array $messages,
        string $key,
        string $fieldName = null,
        int $index = 0
    ) {
        if (is_null($fieldName)) {
            $fieldName = $key;
        }
        $this->assertEquals(str_replace(':attribute', $fieldName, trans('validation.required')), $messages[$key][$index]);
    }

    public function assertEmailField(array $messages, string $key = 'email', int $index = 0)
    {
        $this->assertEquals(str_replace(':attribute', $key, trans('validation.email')), $messages[$key][$index]);
    }

    public function assertPasswordIsConfirmed(array $messages, string $key = 'password', int $index = 0)
    {
        $this->assertEquals(str_replace(':attribute', $key, trans('validation.confirmed')), $messages[$key][$index]);
    }

    public function assertValueIn(array $messages, string $key, string $fieldName = null, int $index = 0)
    {
        if (is_null($fieldName)) {
            $fieldName = $key;
        }
        $this->assertEquals(str_replace(':attribute', $fieldName, trans('validation.in')), $messages[$key][$index]);
    }

    public function assertFieldExist(array $messages, string $key, string $fieldName = null, int $index = 0)
    {
        if (is_null($fieldName)) {
            $fieldName = $key;
        }
        $this->assertEquals(str_replace(':attribute', $fieldName, trans('validation.exists')), $messages[$key][$index]);
    }

    public function assertFieldIsArray(array $messages, string $key, string $fieldName = null, int $index = 0)
    {
        if (is_null($fieldName)) {
            $fieldName = $key;
        }
        $this->assertEquals(str_replace(':attribute', $fieldName, trans('validation.array')), $messages[$key][$index]);
    }

    public function assertValueIsUnique(array $messages, string $key, string $fieldName = null, int $index = 0)
    {
        if (is_null($fieldName)) {
            $fieldName = $key;
        }
        $this->assertEquals(str_replace(':attribute', $fieldName, trans('validation.unique')), $messages[$key][$index]);
    }

    public function assertStartDate(array $messages)
    {
        $this->assertEquals(str_replace(':attribute', 'start date', trans('validation.date')),
            $messages['startDate'][0]);
        $this->assertEquals(str_replace([':attribute', ':format'], ['start date', 'Y-m-d H:i:s'],
            trans('validation.date_format')), $messages['startDate'][1]);
        $this->assertEquals(str_replace([':attribute', ':date'], ['start date', 'end date'],
            trans('validation.before')), $messages['startDate'][2]);
    }

    public function assertEndDate(array $messages)
    {
        $this->assertEquals(str_replace(':attribute', 'end date', trans('validation.date')),
            $messages['endDate'][0]);
        $this->assertEquals(str_replace([':attribute', ':format'], ['end date', 'Y-m-d H:i:s'],
            trans('validation.date_format')), $messages['endDate'][1]);
        $this->assertEquals(str_replace([':attribute', ':date'], ['end date', 'start date'],
            trans('validation.after')), $messages['endDate'][2]);
    }
}