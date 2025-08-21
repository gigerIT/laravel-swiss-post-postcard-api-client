<?php

namespace Gigerit\PostcardApi\Requests\Addresses;

use Gigerit\PostcardApi\DTOs\Address\SenderAddress;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class UploadSenderAddressRequest extends Request
{
    protected Method $method = Method::PUT;

    public function __construct(
        protected string $cardKey,
        protected SenderAddress $senderAddress
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/postcards/{$this->cardKey}/addresses/sender";
    }

    protected function defaultBody(): array
    {
        return $this->senderAddress->toArray();
    }
}
