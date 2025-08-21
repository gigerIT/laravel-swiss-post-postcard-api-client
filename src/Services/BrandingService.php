<?php

namespace Gigerit\PostcardApi\Services;

use Gigerit\PostcardApi\Connectors\SwissPostConnector;
use Gigerit\PostcardApi\DTOs\Branding\BrandingQRCode;
use Gigerit\PostcardApi\DTOs\Branding\BrandingText;
use Gigerit\PostcardApi\DTOs\Response\DefaultResponse;
use Gigerit\PostcardApi\Enums\ImageDimensions;
use Gigerit\PostcardApi\Requests\Branding\UploadBrandingImageRequest;
use Gigerit\PostcardApi\Requests\Branding\UploadBrandingQRCodeRequest;
use Gigerit\PostcardApi\Requests\Branding\UploadBrandingStampRequest;
use Gigerit\PostcardApi\Requests\Branding\UploadBrandingTextRequest;
use Gigerit\PostcardApi\Validation\PostcardValidator;

class BrandingService
{
    public function __construct(
        protected SwissPostConnector $connector
    ) {}

    /**
     * Upload branding text to a postcard
     */
    public function uploadText(string $cardKey, BrandingText $brandingText, bool $validateText = true): DefaultResponse
    {
        if ($validateText) {
            $errors = PostcardValidator::validateBrandingText($brandingText);
            if (! empty($errors)) {
                throw new \InvalidArgumentException('Branding text validation failed: '.implode(', ', $errors));
            }
        }

        $request = new UploadBrandingTextRequest($cardKey, $brandingText);
        $response = $this->connector->send($request);

        return DefaultResponse::fromArray($response->json());
    }

    /**
     * Upload branding image to a postcard
     */
    public function uploadImage(string $cardKey, string $imagePath, ?string $filename = null, bool $validateDimensions = true): DefaultResponse
    {
        if ($validateDimensions) {
            $errors = PostcardValidator::validateImageDimensions($imagePath, ImageDimensions::BRANDING_IMAGE);
            if (! empty($errors)) {
                throw new \InvalidArgumentException('Branding image validation failed: '.implode(', ', $errors));
            }
        }

        $request = new UploadBrandingImageRequest($cardKey, $imagePath, $filename);
        $response = $this->connector->send($request);

        return DefaultResponse::fromArray($response->json());
    }

    /**
     * Upload custom stamp to a postcard
     */
    public function uploadStamp(string $cardKey, string $stampPath, ?string $filename = null, bool $validateDimensions = true): DefaultResponse
    {
        if ($validateDimensions) {
            $errors = PostcardValidator::validateImageDimensions($stampPath, ImageDimensions::STAMP_IMAGE);
            if (! empty($errors)) {
                throw new \InvalidArgumentException('Stamp image validation failed: '.implode(', ', $errors));
            }
        }

        $request = new UploadBrandingStampRequest($cardKey, $stampPath, $filename);
        $response = $this->connector->send($request);

        return DefaultResponse::fromArray($response->json());
    }

    /**
     * Upload QR code branding to a postcard
     */
    public function uploadQRCode(string $cardKey, BrandingQRCode $brandingQRCode, bool $validateQRCode = true): DefaultResponse
    {
        if ($validateQRCode) {
            $errors = PostcardValidator::validateBrandingQRCode($brandingQRCode);
            if (! empty($errors)) {
                throw new \InvalidArgumentException('Branding QR code validation failed: '.implode(', ', $errors));
            }
        }

        $request = new UploadBrandingQRCodeRequest($cardKey, $brandingQRCode);
        $response = $this->connector->send($request);

        return DefaultResponse::fromArray($response->json());
    }

    /**
     * Add simple text branding
     */
    public function addSimpleText(
        string $cardKey,
        string $text,
        ?string $blockColor = null,
        ?string $textColor = null
    ): DefaultResponse {
        $brandingText = new BrandingText($text, $blockColor, $textColor);

        return $this->uploadText($cardKey, $brandingText);
    }

    /**
     * Add simple QR code branding
     */
    public function addSimpleQRCode(
        string $cardKey,
        string $encodedText,
        ?string $accompanyingText = null,
        ?string $blockColor = null,
        ?string $textColor = null
    ): DefaultResponse {
        $brandingQRCode = new BrandingQRCode($encodedText, $accompanyingText, $blockColor, $textColor);

        return $this->uploadQRCode($cardKey, $brandingQRCode);
    }
}
