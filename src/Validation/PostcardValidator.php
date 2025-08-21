<?php

namespace Gigerit\PostcardApi\Validation;

use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Gigerit\PostcardApi\DTOs\Address\SenderAddress;
use Gigerit\PostcardApi\DTOs\Branding\BrandingQRCode;
use Gigerit\PostcardApi\DTOs\Branding\BrandingText;
use Gigerit\PostcardApi\Enums\ImageDimensions;
use Gigerit\PostcardApi\Enums\TextLimits;

class PostcardValidator
{
    /**
     * Validate sender address
     */
    public static function validateSenderAddress(SenderAddress $address): array
    {
        $errors = [];

        // Required fields
        if (empty($address->street)) {
            $errors[] = 'Street is required for sender address';
        }

        if (empty($address->zip)) {
            $errors[] = 'ZIP code is required for sender address';
        }

        if (empty($address->city)) {
            $errors[] = 'City is required for sender address';
        }

        // Name combination validation
        if (empty($address->firstname) && empty($address->lastname) && empty($address->company)) {
            $errors[] = 'Either first name/last name or company name is required for sender address';
        }

        // Length validation
        if ($address->firstname && ($error = TextLimits::SENDER_ADDRESS_FIRSTNAME->validateLength($address->firstname, 'sender first name'))) {
            $errors[] = $error;
        }

        if ($address->lastname && ($error = TextLimits::SENDER_ADDRESS_LASTNAME->validateLength($address->lastname, 'sender last name'))) {
            $errors[] = $error;
        }

        if ($address->company && ($error = TextLimits::SENDER_ADDRESS_COMPANY->validateLength($address->company, 'sender company'))) {
            $errors[] = $error;
        }

        if ($error = TextLimits::SENDER_ADDRESS_STREET->validateLength($address->street, 'sender street')) {
            $errors[] = $error;
        }

        if ($address->houseNr && ($error = TextLimits::SENDER_ADDRESS_HOUSENR->validateLength($address->houseNr, 'sender house number'))) {
            $errors[] = $error;
        }

        if ($error = TextLimits::SENDER_ADDRESS_ZIP->validateLength($address->zip, 'sender ZIP')) {
            $errors[] = $error;
        }

        if ($error = TextLimits::SENDER_ADDRESS_CITY->validateLength($address->city, 'sender city')) {
            $errors[] = $error;
        }

        return $errors;
    }

    /**
     * Validate recipient address
     */
    public static function validateRecipientAddress(RecipientAddress $address): array
    {
        $errors = [];

        // Required fields
        if (empty($address->street)) {
            $errors[] = 'Street is required for recipient address';
        }

        if (empty($address->zip)) {
            $errors[] = 'ZIP code is required for recipient address';
        }

        if (empty($address->city)) {
            $errors[] = 'City is required for recipient address';
        }

        if (empty($address->country)) {
            $errors[] = 'Country is required for recipient address';
        }

        // Name combination validation
        if (empty($address->firstname) && empty($address->lastname) && empty($address->company)) {
            $errors[] = 'Either first name/last name or company name is required for recipient address';
        }

        // Length validation
        if ($address->title && ($error = TextLimits::RECIPIENT_ADDRESS_TITLE->validateLength($address->title, 'recipient title'))) {
            $errors[] = $error;
        }

        if ($address->firstname && ($error = TextLimits::RECIPIENT_ADDRESS_FIRSTNAME->validateLength($address->firstname, 'recipient first name'))) {
            $errors[] = $error;
        }

        if ($address->lastname && ($error = TextLimits::RECIPIENT_ADDRESS_LASTNAME->validateLength($address->lastname, 'recipient last name'))) {
            $errors[] = $error;
        }

        if ($address->company && ($error = TextLimits::RECIPIENT_ADDRESS_COMPANY->validateLength($address->company, 'recipient company'))) {
            $errors[] = $error;
        }

        if ($error = TextLimits::RECIPIENT_ADDRESS_STREET->validateLength($address->street, 'recipient street')) {
            $errors[] = $error;
        }

        if ($address->houseNr && ($error = TextLimits::RECIPIENT_ADDRESS_HOUSENR->validateLength($address->houseNr, 'recipient house number'))) {
            $errors[] = $error;
        }

        if ($error = TextLimits::RECIPIENT_ADDRESS_ZIP->validateLength($address->zip, 'recipient ZIP')) {
            $errors[] = $error;
        }

        if ($error = TextLimits::RECIPIENT_ADDRESS_CITY->validateLength($address->city, 'recipient city')) {
            $errors[] = $error;
        }

        if ($address->poBox && ($error = TextLimits::RECIPIENT_ADDRESS_POBOX->validateLength($address->poBox, 'recipient PO Box'))) {
            $errors[] = $error;
        }

        if ($address->additionalAdrInfo && ($error = TextLimits::RECIPIENT_ADDRESS_ADDITIONAL_INFO->validateLength($address->additionalAdrInfo, 'recipient additional info'))) {
            $errors[] = $error;
        }

        return $errors;
    }

    /**
     * Validate sender text
     */
    public static function validateSenderText(string $text): array
    {
        $errors = [];

        if ($error = TextLimits::SENDER_TEXT->validateLength($text, 'sender text')) {
            $errors[] = $error;
        }

        // Check encoding (CP850 compatibility)
        if (! self::isCP850Compatible($text)) {
            $errors[] = 'Sender text contains characters not compatible with CP850 encoding';
        }

        return $errors;
    }

    /**
     * Validate branding text
     */
    public static function validateBrandingText(BrandingText $brandingText): array
    {
        $errors = [];

        if ($error = TextLimits::BRANDING_TEXT_TEXT->validateLength($brandingText->text, 'branding text')) {
            $errors[] = $error;
        }

        if ($brandingText->textColor && ! self::isValidHexColor($brandingText->textColor)) {
            $errors[] = 'Branding text color must be a valid hex color (e.g., #FFFFFF)';
        }

        if ($brandingText->blockColor && ! self::isValidHexColor($brandingText->blockColor)) {
            $errors[] = 'Branding block color must be a valid hex color (e.g., #FFFFFF)';
        }

        // Check encoding
        if (! self::isCP850Compatible($brandingText->text)) {
            $errors[] = 'Branding text contains characters not compatible with CP850 encoding';
        }

        return $errors;
    }

    /**
     * Validate branding QR code
     */
    public static function validateBrandingQRCode(BrandingQRCode $qrCode): array
    {
        $errors = [];

        if ($qrCode->encodedText && ($error = TextLimits::BRANDING_QR_ENCODED_TEXT->validateLength($qrCode->encodedText, 'QR encoded text'))) {
            $errors[] = $error;
        }

        if ($qrCode->accompanyingText && ($error = TextLimits::BRANDING_QR_ACCOMPANYING_TEXT->validateLength($qrCode->accompanyingText, 'QR accompanying text'))) {
            $errors[] = $error;
        }

        if ($qrCode->textColor && ! self::isValidHexColor($qrCode->textColor)) {
            $errors[] = 'QR text color must be a valid hex color (e.g., #FFFFFF)';
        }

        if ($qrCode->blockColor && ! self::isValidHexColor($qrCode->blockColor)) {
            $errors[] = 'QR block color must be a valid hex color (e.g., #FFFFFF)';
        }

        return $errors;
    }

    /**
     * Validate image dimensions
     */
    public static function validateImageDimensions(string $imagePath, ImageDimensions $expectedDimensions): array
    {
        $errors = [];

        if (! file_exists($imagePath)) {
            $errors[] = 'Image file does not exist';

            return $errors;
        }

        $imageInfo = getimagesize($imagePath);
        if ($imageInfo === false) {
            $errors[] = 'Invalid image file';

            return $errors;
        }

        [$width, $height] = $imageInfo;
        $expectedWidth = $expectedDimensions->getWidth();
        $expectedHeight = $expectedDimensions->getHeight();

        if ($width !== $expectedWidth || $height !== $expectedHeight) {
            $errors[] = "Image dimensions are {$width}x{$height}, but {$expectedWidth}x{$expectedHeight} is required for optimal quality";
        }

        // Check if resolution is too low (warning)
        if ($width < $expectedWidth || $height < $expectedHeight) {
            $errors[] = 'Image resolution is lower than optimal. Higher resolution recommended for best print quality.';
        }

        return $errors;
    }

    /**
     * Check if text is compatible with CP850 encoding
     */
    private static function isCP850Compatible(string $text): bool
    {
        // Convert to CP850 and back to check if characters are preserved
        $converted = mb_convert_encoding($text, 'CP850', 'UTF-8');
        $backConverted = mb_convert_encoding($converted, 'UTF-8', 'CP850');

        return $text === $backConverted;
    }

    /**
     * Validate hex color format
     */
    private static function isValidHexColor(string $color): bool
    {
        return preg_match('/^#[0-9A-Fa-f]{6}$/', $color) === 1;
    }
}
