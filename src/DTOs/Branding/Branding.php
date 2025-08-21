<?php

namespace Gigerit\PostcardApi\DTOs\Branding;

class Branding
{
    public function __construct(
        public readonly ?BrandingText $brandingText = null,
        public readonly ?BrandingQRCode $brandingQRCode = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            brandingText: isset($data['brandingText'])
                ? BrandingText::fromArray($data['brandingText'])
                : null,
            brandingQRCode: isset($data['brandingQRCode'])
                ? BrandingQRCode::fromArray($data['brandingQRCode'])
                : null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'brandingText' => $this->brandingText?->toArray(),
            'brandingQRCode' => $this->brandingQRCode?->toArray(),
        ], fn ($value) => $value !== null);
    }

    public function hasBrandingText(): bool
    {
        return $this->brandingText !== null;
    }

    public function hasBrandingQRCode(): bool
    {
        return $this->brandingQRCode !== null;
    }
}
