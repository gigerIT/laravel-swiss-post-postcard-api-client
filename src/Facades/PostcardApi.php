<?php

namespace Gigerit\PostcardApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Gigerit\PostcardApi\PostcardApi
 */
class PostcardApi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Gigerit\PostcardApi\PostcardApi::class;
    }
}
