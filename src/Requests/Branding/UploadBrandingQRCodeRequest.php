<?php

namespace Gigerit\PostcardApi\Requests\Branding;

use Gigerit\PostcardApi\DTOs\Branding\BrandingQRCode;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class UploadBrandingQRCodeRequest extends Request
{
    protected Method $method = Method::PUT;

    public function __construct(
        protected string $cardKey,
        protected BrandingQRCode $brandingQRCode
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/postcards/{$this->cardKey}/branding/qrtag";
    }

    protected function defaultBody(): array
    {
        return $this->brandingQRCode->toArray();
    }
}
