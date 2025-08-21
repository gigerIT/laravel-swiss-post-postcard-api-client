<?php

namespace Gigerit\PostcardApi\DTOs\Branding;

class BrandingQRCode
{
    public function __construct(
        public readonly ?string $encodedText = null,
        public readonly ?string $accompanyingText = null,
        public readonly ?string $blockColor = null,
        public readonly ?string $textColor = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            encodedText: $data['encodedText'] ?? null,
            accompanyingText: $data['accompanyingText'] ?? null,
            blockColor: $data['blockColor'] ?? null,
            textColor: $data['textColor'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'encodedText' => $this->encodedText,
            'accompanyingText' => $this->accompanyingText,
            'blockColor' => $this->blockColor,
            'textColor' => $this->textColor,
        ], fn ($value) => $value !== null);
    }
}
