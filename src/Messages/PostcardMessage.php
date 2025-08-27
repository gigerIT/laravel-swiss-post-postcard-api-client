<?php

namespace Gigerit\PostcardApi\Messages;

use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Gigerit\PostcardApi\DTOs\Address\SenderAddress;
use Gigerit\PostcardApi\DTOs\Branding\Branding;
use Gigerit\PostcardApi\DTOs\Branding\BrandingQRCode;
use Gigerit\PostcardApi\DTOs\Branding\BrandingText;

class PostcardMessage
{
    public function __construct(
        public string $imagePath,
        public ?RecipientAddress $recipientAddress = null,
        public ?SenderAddress $senderAddress = null,
        public ?string $senderText = null,
        public ?string $campaignKey = null,
        public bool $autoApprove = false,
        public ?Branding $branding = null,
        public ?string $brandingImagePath = null,
        public ?string $brandingStampPath = null
    ) {}

    /**
     * Set the recipient address for the postcard.
     */
    public function to(RecipientAddress $address): self
    {
        $this->recipientAddress = $address;

        return $this;
    }

    /**
     * Set the sender address for the postcard.
     */
    public function from(SenderAddress $address): self
    {
        $this->senderAddress = $address;

        return $this;
    }

    /**
     * Set the sender text (message on the back of the postcard).
     */
    public function text(string $text): self
    {
        $this->senderText = $text;

        return $this;
    }

    /**
     * Set the campaign key for the postcard.
     */
    public function campaign(string $campaignKey): self
    {
        $this->campaignKey = $campaignKey;

        return $this;
    }

    /**
     * Enable automatic approval of the postcard after creation.
     */
    public function autoApprove(bool $autoApprove = true): self
    {
        $this->autoApprove = $autoApprove;

        return $this;
    }

    /**
     * Set branding for the postcard.
     */
    public function withBranding(Branding $branding): self
    {
        $this->branding = $branding;

        return $this;
    }

    /**
     * Set branding text for the postcard.
     */
    public function withBrandingText(string $text, ?string $blockColor = null, ?string $textColor = null): self
    {
        $brandingText = new BrandingText($text, $blockColor, $textColor);

        $this->branding = new Branding(
            brandingText: $brandingText,
            brandingQRCode: $this->branding?->brandingQRCode
        );

        return $this;
    }

    /**
     * Set branding QR code for the postcard.
     */
    public function withBrandingQRCode(
        ?string $encodedText = null,
        ?string $accompanyingText = null,
        ?string $blockColor = null,
        ?string $textColor = null
    ): self {
        $brandingQRCode = new BrandingQRCode($encodedText, $accompanyingText, $blockColor, $textColor);

        $this->branding = new Branding(
            brandingText: $this->branding?->brandingText,
            brandingQRCode: $brandingQRCode
        );

        return $this;
    }

    /**
     * Set branding image path for the postcard.
     */
    public function withBrandingImage(string $brandingImagePath): self
    {
        $this->brandingImagePath = $brandingImagePath;

        return $this;
    }

    /**
     * Set custom stamp path for the postcard.
     */
    public function withCustomStamp(string $brandingStampPath): self
    {
        $this->brandingStampPath = $brandingStampPath;

        return $this;
    }

    /**
     * Create a postcard message with an image.
     */
    public static function create(string $imagePath): self
    {
        return new self(imagePath: $imagePath);
    }
}
