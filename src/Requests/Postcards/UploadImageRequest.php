<?php

namespace Gigerit\PostcardApi\Requests\Postcards;

use Saloon\Contracts\Body\HasBody;
use Saloon\Data\MultipartValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasMultipartBody;

class UploadImageRequest extends Request implements HasBody
{
    use HasMultipartBody;

    protected Method $method = Method::PUT;

    public function __construct(
        protected string $cardKey,
        protected string $imagePath,
        protected ?string $filename = null
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/postcards/{$this->cardKey}/image";
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
            'image' => new MultipartValue(
                name: 'image',
                value: fopen($this->imagePath, 'r'),
                filename: $this->filename ?? basename($this->imagePath)
            ),
        ];
    }
}
