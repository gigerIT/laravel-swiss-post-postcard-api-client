<?php

namespace Gigerit\PostcardApi\Requests\Postcards;

use Gigerit\PostcardApi\DTOs\Postcard\Postcard;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class CreatePostcardRequest extends Request
{
    protected Method $method = Method::POST;

    public function __construct(
        protected string $campaignKey,
        protected ?Postcard $postcard = null
    ) {}

    public function resolveEndpoint(): string
    {
        return '/api/v1/postcards';
    }

    protected function defaultQuery(): array
    {
        return [
            'campaignKey' => $this->campaignKey,
        ];
    }

    protected function defaultBody(): array
    {
        return $this->postcard?->toArray() ?? [];
    }
}
