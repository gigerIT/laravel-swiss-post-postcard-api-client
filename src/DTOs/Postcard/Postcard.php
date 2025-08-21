<?php

namespace Gigerit\PostcardApi\DTOs\Postcard;

use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Gigerit\PostcardApi\DTOs\Address\SenderAddress;
use Gigerit\PostcardApi\DTOs\Branding\Branding;

class Postcard
{
    public function __construct(
        public readonly ?SenderAddress $senderAddress = null,
        public readonly ?RecipientAddress $recipientAddress = null,
        public readonly ?string $senderText = null,
        public readonly ?Branding $branding = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            senderAddress: isset($data['senderAddress'])
                ? SenderAddress::fromArray($data['senderAddress'])
                : null,
            recipientAddress: isset($data['recipientAddress'])
                ? RecipientAddress::fromArray($data['recipientAddress'])
                : null,
            senderText: $data['senderText'] ?? null,
            branding: isset($data['branding'])
                ? Branding::fromArray($data['branding'])
                : null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'senderAddress' => $this->senderAddress?->toArray(),
            'recipientAddress' => $this->recipientAddress?->toArray(),
            'senderText' => $this->senderText,
            'branding' => $this->branding?->toArray(),
        ], fn ($value) => $value !== null);
    }

    public function hasSenderAddress(): bool
    {
        return $this->senderAddress !== null;
    }

    public function hasRecipientAddress(): bool
    {
        return $this->recipientAddress !== null;
    }

    public function hasSenderText(): bool
    {
        return $this->senderText !== null && $this->senderText !== '';
    }

    public function hasBranding(): bool
    {
        return $this->branding !== null;
    }
}
