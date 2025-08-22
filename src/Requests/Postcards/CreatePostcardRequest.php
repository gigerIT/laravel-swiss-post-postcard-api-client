<?php

namespace Gigerit\PostcardApi\Requests\Postcards;

use Gigerit\PostcardApi\DTOs\Postcard\Postcard;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreatePostcardRequest extends Request implements HasBody
{
    use HasJsonBody;

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
