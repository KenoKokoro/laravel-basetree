<?php


namespace BaseTree\Resources\Contracts;


use BaseTree\Resources\Contracts\Callbacks\CreatedCallback;
use BaseTree\Resources\Contracts\Callbacks\DeletedCallback;
use BaseTree\Resources\Contracts\Callbacks\UpdatedCallback;

interface ResourceCallbacks extends CreatedCallback, UpdatedCallback, DeletedCallback
{
}