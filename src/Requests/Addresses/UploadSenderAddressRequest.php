<?php

namespace Gigerit\PostcardApi\Requests\Addresses;

use Gigerit\PostcardApi\DTOs\Address\SenderAddress;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class UploadSenderAddressRequest extends Request implements HasBody
{
    use HasJsonBody;

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
