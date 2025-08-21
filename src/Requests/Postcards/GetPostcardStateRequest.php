<?php

namespace Gigerit\PostcardApi\Requests\Postcards;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetPostcardStateRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $cardKey
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/postcards/{$this->cardKey}/state";
    }
}
