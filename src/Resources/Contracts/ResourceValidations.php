<?php


namespace BaseTree\Resources\Contracts;


use BaseTree\Resources\Contracts\Validations\DestroyValidation;
use BaseTree\Resources\Contracts\Validations\StoreValidation;
use BaseTree\Resources\Contracts\Validations\UpdateValidation;

interface ResourceValidations extends StoreValidation, UpdateValidation, DestroyValidation
{
}