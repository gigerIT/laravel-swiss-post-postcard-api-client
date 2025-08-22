<?php

namespace Gigerit\PostcardApi\Requests\Addresses;

use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class UploadRecipientAddressRequest extends Request implements HasBody
{
    use HasJsonBody;

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
