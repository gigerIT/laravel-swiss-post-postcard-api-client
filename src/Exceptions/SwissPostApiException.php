<?php

namespace Gigerit\PostcardApi\Exceptions;

use Exception;

class SwissPostApiException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
