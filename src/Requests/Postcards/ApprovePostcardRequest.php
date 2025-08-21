<?php

namespace Gigerit\PostcardApi\Requests\Postcards;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ApprovePostcardRequest extends Request
{
    protected Method $method = Method::POST;

    public function __construct(
        protected string $cardKey
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/postcards/{$this->cardKey}/approval";
    }
}
