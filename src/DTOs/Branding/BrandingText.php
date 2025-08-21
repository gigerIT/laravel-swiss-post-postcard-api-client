<?php

namespace Gigerit\PostcardApi\DTOs\Branding;

class BrandingText
{
    public function __construct(
        public readonly string $text,
        public readonly ?string $blockColor = null,
        public readonly ?string $textColor = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'],
            blockColor: $data['blockColor'] ?? null,
            textColor: $data['textColor'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'text' => $this->text,
            'blockColor' => $this->blockColor,
            'textColor' => $this->textColor,
        ], fn ($value) => $value !== null);
    }
}
