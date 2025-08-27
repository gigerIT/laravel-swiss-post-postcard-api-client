<?php

namespace Gigerit\PostcardApi\Requests\Branding;

use Saloon\Contracts\Body\HasBody;
use Saloon\Data\MultipartValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasMultipartBody;

class UploadBrandingStampRequest extends Request implements HasBody
{
    use HasMultipartBody;

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
            'Content-Type' => 'multipart/form-data; boundary='.$this->body()->getBoundary(),
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
