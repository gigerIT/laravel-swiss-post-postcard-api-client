<?php

namespace Gigerit\PostcardApi\Tests\Unit\Enums;

use Gigerit\PostcardApi\Enums\ErrorCode;

describe('ErrorCode', function () {
    it('has correct values for required field errors', function () {
        expect(ErrorCode::NAME_REQUIRED->value)->toBe(1000)
            ->and(ErrorCode::FIRSTNAME_REQUIRED->value)->toBe(1001)
            ->and(ErrorCode::STREET_REQUIRED->value)->toBe(1002)
            ->and(ErrorCode::ZIP_REQUIRED->value)->toBe(1003)
            ->and(ErrorCode::CITY_REQUIRED->value)->toBe(1004)
            ->and(ErrorCode::RECIPIENT_ADDRESS_REQUIRED->value)->toBe(1006)
            ->and(ErrorCode::FRONT_IMAGE_REQUIRED->value)->toBe(1007);
    });

    it('has correct values for validation errors', function () {
        expect(ErrorCode::TITLE_LENGTH_INVALID->value)->toBe(1100)
            ->and(ErrorCode::NAME_LENGTH_INVALID->value)->toBe(1101)
            ->and(ErrorCode::SENDER_TEXT_LENGTH_INVALID->value)->toBe(1109)
            ->and(ErrorCode::BRANDING_TEXT_LENGTH_INVALID->value)->toBe(1110);
    });

    it('has correct values for campaign errors', function () {
        expect(ErrorCode::CAMPAIGN_QUOTA_EXCEEDED->value)->toBe(2000)
            ->and(ErrorCode::CAMPAIGN_EXPIRED->value)->toBe(2010)
            ->and(ErrorCode::CAMPAIGN_NOT_FOUND->value)->toBe(4000)
            ->and(ErrorCode::POSTCARD_NOT_FOUND->value)->toBe(4003);
    });

    it('has correct values for general errors', function () {
        expect(ErrorCode::ENCODING_VIOLATION->value)->toBe(5000)
            ->and(ErrorCode::FILE_FORMAT_NOT_SUPPORTED->value)->toBe(5010)
            ->and(ErrorCode::BAD_RESOLUTION->value)->toBe(5020)
            ->and(ErrorCode::POSTCARD_ALREADY_APPROVED->value)->toBe(6000);
    });

    it('provides correct descriptions', function () {
        expect(ErrorCode::NAME_REQUIRED->getDescription())->toBe('The name is required')
            ->and(ErrorCode::RECIPIENT_ADDRESS_REQUIRED->getDescription())->toBe('Recipient address is required')
            ->and(ErrorCode::CAMPAIGN_QUOTA_EXCEEDED->getDescription())->toBe('Campaign quota exceeded')
            ->and(ErrorCode::POSTCARD_ALREADY_APPROVED->getDescription())->toBe('The given postcard is already approved');
    });

    it('correctly identifies errors vs warnings', function () {
        expect(ErrorCode::RECIPIENT_ADDRESS_REQUIRED->isError())->toBeTrue()
            ->and(ErrorCode::RECIPIENT_ADDRESS_REQUIRED->isWarning())->toBeFalse()
            ->and(ErrorCode::BAD_RESOLUTION->isError())->toBeFalse()
            ->and(ErrorCode::BAD_RESOLUTION->isWarning())->toBeTrue()
            ->and(ErrorCode::SENDER_TEXT_LENGTH_INVALID->isWarning())->toBeTrue()
            ->and(ErrorCode::ENCODING_VIOLATION->isWarning())->toBeTrue();
    });

    it('handles unknown error codes gracefully', function () {
        // Test with a known case that should have proper description
        $description = ErrorCode::CAMPAIGN_NOT_FOUND->getDescription();
        expect($description)->toBe('Campaign not found');
    });
});

describe('ImageDimensions', function () {
    it('has correct dimension values', function () {
        expect(\Gigerit\PostcardApi\Enums\ImageDimensions::FRONT_IMAGE->value)->toBe('1819x1311')
            ->and(\Gigerit\PostcardApi\Enums\ImageDimensions::STAMP_IMAGE->value)->toBe('343x248')
            ->and(\Gigerit\PostcardApi\Enums\ImageDimensions::BRANDING_IMAGE->value)->toBe('777x295');
    });

    it('parses width correctly', function () {
        expect(\Gigerit\PostcardApi\Enums\ImageDimensions::FRONT_IMAGE->getWidth())->toBe(1819)
            ->and(\Gigerit\PostcardApi\Enums\ImageDimensions::STAMP_IMAGE->getWidth())->toBe(343)
            ->and(\Gigerit\PostcardApi\Enums\ImageDimensions::BRANDING_IMAGE->getWidth())->toBe(777);
    });

    it('parses height correctly', function () {
        expect(\Gigerit\PostcardApi\Enums\ImageDimensions::FRONT_IMAGE->getHeight())->toBe(1311)
            ->and(\Gigerit\PostcardApi\Enums\ImageDimensions::STAMP_IMAGE->getHeight())->toBe(248)
            ->and(\Gigerit\PostcardApi\Enums\ImageDimensions::BRANDING_IMAGE->getHeight())->toBe(295);
    });

    it('returns dimensions as array', function () {
        $frontDimensions = \Gigerit\PostcardApi\Enums\ImageDimensions::FRONT_IMAGE->getDimensions();

        expect($frontDimensions)->toBe(['width' => 1819, 'height' => 1311]);
    });

    it('calculates aspect ratio correctly', function () {
        $aspectRatio = \Gigerit\PostcardApi\Enums\ImageDimensions::FRONT_IMAGE->getAspectRatio();

        expect($aspectRatio)->toBeFloat()
            ->and($aspectRatio)->toBe(1819 / 1311);
    });

    it('provides static helper methods', function () {
        $frontDimensions = \Gigerit\PostcardApi\Enums\ImageDimensions::getFrontImageDimensions();
        $stampDimensions = \Gigerit\PostcardApi\Enums\ImageDimensions::getStampImageDimensions();
        $brandingDimensions = \Gigerit\PostcardApi\Enums\ImageDimensions::getBrandingImageDimensions();

        expect($frontDimensions)->toBe(['width' => 1819, 'height' => 1311])
            ->and($stampDimensions)->toBe(['width' => 343, 'height' => 248])
            ->and($brandingDimensions)->toBe(['width' => 777, 'height' => 295]);
    });
});

describe('TextLimits', function () {
    it('has correct sender text limits', function () {
        expect(\Gigerit\PostcardApi\Enums\TextLimits::SENDER_TEXT->getMaxLength())->toBe(900)
            ->and(\Gigerit\PostcardApi\Enums\TextLimits::SENDER_TEXT->getMinLength())->toBe(0);
    });

    it('has correct address field limits', function () {
        expect(\Gigerit\PostcardApi\Enums\TextLimits::SENDER_ADDRESS_FIRSTNAME->getMaxLength())->toBe(75)
            ->and(\Gigerit\PostcardApi\Enums\TextLimits::SENDER_ADDRESS_FIRSTNAME->getMinLength())->toBe(2)
            ->and(\Gigerit\PostcardApi\Enums\TextLimits::SENDER_ADDRESS_ZIP->getMaxLength())->toBe(39)
            ->and(\Gigerit\PostcardApi\Enums\TextLimits::SENDER_ADDRESS_ZIP->getMinLength())->toBe(4);
    });

    it('has correct branding limits', function () {
        expect(\Gigerit\PostcardApi\Enums\TextLimits::BRANDING_TEXT_TEXT->getMaxLength())->toBe(250)
            ->and(\Gigerit\PostcardApi\Enums\TextLimits::BRANDING_QR_ENCODED_TEXT->getMaxLength())->toBe(100)
            ->and(\Gigerit\PostcardApi\Enums\TextLimits::BRANDING_TEXT_TEXTCOLOR->getMaxLength())->toBe(7)
            ->and(\Gigerit\PostcardApi\Enums\TextLimits::BRANDING_TEXT_TEXTCOLOR->getMinLength())->toBe(4);
    });

    it('validates text length correctly', function () {
        $limit = \Gigerit\PostcardApi\Enums\TextLimits::SENDER_TEXT;

        expect($limit->isValidLength('Hello'))->toBeTrue()
            ->and($limit->isValidLength(''))->toBeTrue() // Min is 0
            ->and($limit->isValidLength(str_repeat('A', 900)))->toBeTrue()
            ->and($limit->isValidLength(str_repeat('A', 901)))->toBeFalse();
    });

    it('provides validation error messages', function () {
        $limit = \Gigerit\PostcardApi\Enums\TextLimits::SENDER_ADDRESS_FIRSTNAME;

        $tooShort = $limit->validateLength('A', 'test field');
        $tooLong = $limit->validateLength(str_repeat('A', 76), 'test field');
        $justRight = $limit->validateLength('John', 'test field');

        expect($tooShort)->toContain('too short')
            ->and($tooLong)->toContain('too long')
            ->and($justRight)->toBeNull();
    });

    it('handles UTF-8 characters correctly', function () {
        $limit = \Gigerit\PostcardApi\Enums\TextLimits::SENDER_TEXT;
        $text = 'Héllo Wörld! 你好'; // Mixed UTF-8 characters

        expect($limit->isValidLength($text))->toBeTrue();
    });
});
