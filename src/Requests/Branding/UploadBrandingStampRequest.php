<?php

namespace Gigerit\PostcardApi\Requests\Branding;

use Saloon\Data\MultipartValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class UploadBrandingStampRequest extends Request
{
    protected Method $method = Method::PUT;

    public function __construct(
        protected string $cardKey,
        protected string $stampPath,
        protected ?string $filename = null
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/postcards/{$this->cardKey}/branding/stamp";
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'multipart/form-data',
        ];
    }

    protected function defaultBody(): array
    {
        return [
            'stamp' => new MultipartValue(
                name: 'stamp',
                value: fopen($this->stampPath, 'r'),
                filename: $this->filename ?? basename($this->stampPath)
            ),
        ];
    }
}
