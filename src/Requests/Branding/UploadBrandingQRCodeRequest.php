<?php

namespace Gigerit\PostcardApi\Requests\Branding;

use Gigerit\PostcardApi\DTOs\Branding\BrandingQRCode;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class UploadBrandingQRCodeRequest extends Request implements HasBody
{
    use HasJsonBody;

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
