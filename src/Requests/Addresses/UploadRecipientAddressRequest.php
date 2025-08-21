<?php

namespace Gigerit\PostcardApi\Requests\Addresses;

use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class UploadRecipientAddressRequest extends Request
{
    protected Method $method = Method::PUT;

    public function __construct(
        protected string $cardKey,
        protected RecipientAddress $recipientAddress
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/postcards/{$this->cardKey}/addresses/recipient";
    }

    protected function defaultBody(): array
    {
        return $this->recipientAddress->toArray();
    }
}
