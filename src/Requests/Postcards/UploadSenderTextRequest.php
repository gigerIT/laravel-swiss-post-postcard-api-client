<?php

namespace Gigerit\PostcardApi\Requests\Postcards;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class UploadSenderTextRequest extends Request
{
    protected Method $method = Method::PUT;

    public function __construct(
        protected string $cardKey,
        protected string $senderText
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/postcards/{$this->cardKey}/sendertext";
    }

    protected function defaultQuery(): array
    {
        return [
            'senderText' => $this->senderText,
        ];
    }
}
