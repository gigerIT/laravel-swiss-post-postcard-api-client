<?php

namespace Gigerit\PostcardApi\Messages;

use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Gigerit\PostcardApi\DTOs\Address\SenderAddress;

class PostcardMessage
{
    public function __construct(
        public readonly string $imagePath,
        public readonly ?RecipientAddress $recipientAddress = null,
        public readonly ?SenderAddress $senderAddress = null,
        public readonly ?string $senderText = null,
        public readonly ?string $campaignKey = null,
        public readonly bool $autoApprove = false
    ) {}

    /**
     * Set the recipient address for the postcard.
     */
    public function to(RecipientAddress $address): self
    {
        return new self(
            imagePath: $this->imagePath,
            recipientAddress: $address,
            senderAddress: $this->senderAddress,
            senderText: $this->senderText,
            campaignKey: $this->campaignKey,
            autoApprove: $this->autoApprove
        );
    }

    /**
     * Set the sender address for the postcard.
     */
    public function from(SenderAddress $address): self
    {
        return new self(
            imagePath: $this->imagePath,
            recipientAddress: $this->recipientAddress,
            senderAddress: $address,
            senderText: $this->senderText,
            campaignKey: $this->campaignKey,
            autoApprove: $this->autoApprove
        );
    }

    /**
     * Set the sender text (message on the back of the postcard).
     */
    public function text(string $text): self
    {
        return new self(
            imagePath: $this->imagePath,
            recipientAddress: $this->recipientAddress,
            senderAddress: $this->senderAddress,
            senderText: $text,
            campaignKey: $this->campaignKey,
            autoApprove: $this->autoApprove
        );
    }

    /**
     * Set the campaign key for the postcard.
     */
    public function campaign(string $campaignKey): self
    {
        return new self(
            imagePath: $this->imagePath,
            recipientAddress: $this->recipientAddress,
            senderAddress: $this->senderAddress,
            senderText: $this->senderText,
            campaignKey: $campaignKey,
            autoApprove: $this->autoApprove
        );
    }

    /**
     * Enable automatic approval of the postcard after creation.
     */
    public function autoApprove(bool $autoApprove = true): self
    {
        return new self(
            imagePath: $this->imagePath,
            recipientAddress: $this->recipientAddress,
            senderAddress: $this->senderAddress,
            senderText: $this->senderText,
            campaignKey: $this->campaignKey,
            autoApprove: $autoApprove
        );
    }

    /**
     * Create a postcard message with an image.
     */
    public static function create(string $imagePath): self
    {
        return new self(imagePath: $imagePath);
    }
}
