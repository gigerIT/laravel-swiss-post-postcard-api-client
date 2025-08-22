<?php

namespace Gigerit\PostcardApi\Requests\Branding;

use Gigerit\PostcardApi\DTOs\Branding\BrandingText;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class UploadBrandingTextRequest extends Request implements HasBody
{
    use HasJsonBody;

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
