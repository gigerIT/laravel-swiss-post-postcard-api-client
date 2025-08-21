<?php

namespace Gigerit\PostcardApi\Requests\Branding;

use Gigerit\PostcardApi\DTOs\Branding\BrandingText;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class UploadBrandingTextRequest extends Request
{
    protected Method $method = Method::PUT;

    public function __construct(
        protected string $cardKey,
        protected BrandingText $brandingText
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/postcards/{$this->cardKey}/branding/text";
    }

    protected function defaultBody(): array
    {
        return $this->brandingText->toArray();
    }
}
