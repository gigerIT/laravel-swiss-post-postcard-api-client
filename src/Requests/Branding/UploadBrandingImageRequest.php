<?php

namespace Gigerit\PostcardApi\Requests\Branding;

use Saloon\Data\MultipartValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class UploadBrandingImageRequest extends Request
{
    protected Method $method = Method::PUT;

    public function __construct(
        protected string $cardKey,
        protected string $imagePath,
        protected ?string $filename = null
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/postcards/{$this->cardKey}/branding/image";
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
            'image' => new MultipartValue(
                name: 'image',
                value: fopen($this->imagePath, 'r'),
                filename: $this->filename ?? basename($this->imagePath)
            ),
        ];
    }
}
