<?php

namespace Gigerit\PostcardApi\Tests\Fixtures;

use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Gigerit\PostcardApi\DTOs\Address\SenderAddress;
use Gigerit\PostcardApi\DTOs\Branding\BrandingQRCode;
use Gigerit\PostcardApi\DTOs\Branding\BrandingText;
use Gigerit\PostcardApi\DTOs\Postcard\Postcard;

class TestData
{
    public static function validRecipientAddress(): RecipientAddress
    {
        return new RecipientAddress(
            street: 'Musterstrasse',
            zip: '8000',
            city: 'Zürich',
            country: 'CH',
            firstname: 'John',
            lastname: 'Doe',
            houseNr: '123'
        );
    }

    public static function validSenderAddress(): SenderAddress
    {
        return new SenderAddress(
            street: 'Absenderstrasse',
            zip: '3000',
            city: 'Bern',
            firstname: 'Jane',
            lastname: 'Smith',
            houseNr: '456'
        );
    }

    public static function validBrandingText(): BrandingText
    {
        return new BrandingText(
            text: 'Your Company Name',
            blockColor: '#FF0000',
            textColor: '#FFFFFF'
        );
    }

    public static function validBrandingQRCode(): BrandingQRCode
    {
        return new BrandingQRCode(
            encodedText: 'https://example.com',
            accompanyingText: 'Visit our website',
            blockColor: '#000000',
            textColor: '#FFFFFF'
        );
    }

    public static function validPostcard(): Postcard
    {
        return new Postcard(
            senderAddress: self::validSenderAddress(),
            recipientAddress: self::validRecipientAddress(),
            senderText: 'Hello from Switzerland!'
        );
    }

    public static function invalidRecipientAddress(): RecipientAddress
    {
        return new RecipientAddress(
            street: '', // Invalid: empty street
            zip: '123', // Invalid: too short
            city: 'A', // Invalid: too short
            country: 'CH'
        );
    }

    public static function invalidSenderAddress(): SenderAddress
    {
        return new SenderAddress(
            street: '', // Invalid: empty street
            zip: '123', // Invalid: too short
            city: 'A', // Invalid: too short
        );
    }

    public static function longText(int $length): string
    {
        return str_repeat('A', $length);
    }

    public static function validSenderText(): string
    {
        return 'Hello from Switzerland! This is a test postcard sent via the API.';
    }
}
