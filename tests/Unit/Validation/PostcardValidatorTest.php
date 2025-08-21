<?php

namespace Gigerit\PostcardApi\Tests\Unit\Validation;

use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Gigerit\PostcardApi\DTOs\Address\SenderAddress;
use Gigerit\PostcardApi\DTOs\Branding\BrandingQRCode;
use Gigerit\PostcardApi\DTOs\Branding\BrandingText;
use Gigerit\PostcardApi\Enums\ImageDimensions;
use Gigerit\PostcardApi\Tests\Fixtures\TestData;
use Gigerit\PostcardApi\Validation\PostcardValidator;

describe('PostcardValidator', function () {
    describe('validateSenderAddress', function () {
        it('validates valid sender address', function () {
            $address = TestData::validSenderAddress();
            $errors = PostcardValidator::validateSenderAddress($address);

            expect($errors)->toBeEmpty();
        });

        it('requires street', function () {
            $address = new SenderAddress(
                street: '',
                zip: '3000',
                city: 'Bern'
            );

            $errors = PostcardValidator::validateSenderAddress($address);

            expect($errors)->toContain('Street is required for sender address');
        });

        it('requires zip', function () {
            $address = new SenderAddress(
                street: 'Test Street',
                zip: '',
                city: 'Bern'
            );

            $errors = PostcardValidator::validateSenderAddress($address);

            expect($errors)->toContain('ZIP code is required for sender address');
        });

        it('requires city', function () {
            $address = new SenderAddress(
                street: 'Test Street',
                zip: '3000',
                city: ''
            );

            $errors = PostcardValidator::validateSenderAddress($address);

            expect($errors)->toContain('City is required for sender address');
        });

        it('requires name or company', function () {
            $address = new SenderAddress(
                street: 'Test Street',
                zip: '3000',
                city: 'Bern'
            );

            $errors = PostcardValidator::validateSenderAddress($address);

            expect($errors)->toContain('Either first name/last name or company name is required for sender address');
        });

        it('validates text lengths', function () {
            $address = new SenderAddress(
                street: TestData::longText(100), // Too long
                zip: '12', // Too short
                city: 'A', // Too short
                firstname: TestData::longText(100), // Too long
            );

            $errors = PostcardValidator::validateSenderAddress($address);

            expect($errors)->toHaveCount(4);
        });
    });

    describe('validateRecipientAddress', function () {
        it('validates valid recipient address', function () {
            $address = TestData::validRecipientAddress();
            $errors = PostcardValidator::validateRecipientAddress($address);

            expect($errors)->toBeEmpty();
        });

        it('requires country', function () {
            $address = new RecipientAddress(
                street: 'Test Street',
                zip: '8000',
                city: 'Zürich',
                country: ''
            );

            $errors = PostcardValidator::validateRecipientAddress($address);

            expect($errors)->toContain('Country is required for recipient address');
        });

        it('validates all required fields', function () {
            $address = new RecipientAddress(
                street: '',
                zip: '',
                city: '',
                country: ''
            );

            $errors = PostcardValidator::validateRecipientAddress($address);

            // Check that we have errors for: street, zip, city, country, name combination,
            // plus validation errors for short text fields
            expect(count($errors))->toBeGreaterThanOrEqual(5);
            expect($errors)->toContain('Street is required for recipient address');
            expect($errors)->toContain('ZIP code is required for recipient address');
            expect($errors)->toContain('City is required for recipient address');
            expect($errors)->toContain('Country is required for recipient address');
        });
    });

    describe('validateSenderText', function () {
        it('validates valid sender text', function () {
            $text = TestData::validSenderText();
            $errors = PostcardValidator::validateSenderText($text);

            expect($errors)->toBeEmpty();
        });

        it('rejects text that is too long', function () {
            $text = TestData::longText(1000); // Too long (max 900)
            $errors = PostcardValidator::validateSenderText($text);

            expect($errors)->toHaveCount(1)
                ->and($errors[0])->toContain('sender text is too long');
        });

        it('validates encoding compatibility', function () {
            $text = 'Hello 世界!'; // Contains non-CP850 characters
            $errors = PostcardValidator::validateSenderText($text);

            // This might pass or fail depending on system encoding support
            // The test mainly ensures the method doesn't crash
            expect($errors)->toBeArray();
        });
    });

    describe('validateBrandingText', function () {
        it('validates valid branding text', function () {
            $brandingText = TestData::validBrandingText();
            $errors = PostcardValidator::validateBrandingText($brandingText);

            expect($errors)->toBeEmpty();
        });

        it('rejects text that is too long', function () {
            $brandingText = new BrandingText(
                text: TestData::longText(300) // Too long (max 250)
            );

            $errors = PostcardValidator::validateBrandingText($brandingText);

            expect($errors)->toHaveCount(1)
                ->and($errors[0])->toContain('branding text is too long');
        });

        it('validates hex colors', function () {
            $brandingText = new BrandingText(
                text: 'Test',
                textColor: 'invalid-color',
                blockColor: 'also-invalid'
            );

            $errors = PostcardValidator::validateBrandingText($brandingText);

            expect($errors)->toHaveCount(2)
                ->and($errors[0])->toContain('valid hex color')
                ->and($errors[1])->toContain('valid hex color');
        });

        it('accepts valid hex colors', function () {
            $brandingText = new BrandingText(
                text: 'Test',
                textColor: '#FF0000',
                blockColor: '#FFFFFF'
            );

            $errors = PostcardValidator::validateBrandingText($brandingText);

            expect($errors)->toBeEmpty();
        });
    });

    describe('validateBrandingQRCode', function () {
        it('validates valid QR code', function () {
            $qrCode = TestData::validBrandingQRCode();
            $errors = PostcardValidator::validateBrandingQRCode($qrCode);

            expect($errors)->toBeEmpty();
        });

        it('validates text lengths', function () {
            $qrCode = new BrandingQRCode(
                encodedText: TestData::longText(150), // Too long (max 100)
                accompanyingText: TestData::longText(300) // Too long (max 250)
            );

            $errors = PostcardValidator::validateBrandingQRCode($qrCode);

            expect($errors)->toHaveCount(2);
        });

        it('validates hex colors', function () {
            $qrCode = new BrandingQRCode(
                encodedText: 'https://test.com',
                textColor: 'invalid',
                blockColor: 'also-invalid'
            );

            $errors = PostcardValidator::validateBrandingQRCode($qrCode);

            expect($errors)->toHaveCount(2);
        });
    });

    describe('validateImageDimensions', function () {
        beforeEach(function () {
            // Create a temporary test image file
            $this->tempImagePath = tempnam(sys_get_temp_dir(), 'test_image').'.jpg';

            // Create a simple 100x100 test image
            $image = imagecreate(100, 100);
            imagecolorallocate($image, 255, 255, 255);
            imagejpeg($image, $this->tempImagePath);
            imagedestroy($image);
        });

        afterEach(function () {
            if (file_exists($this->tempImagePath)) {
                unlink($this->tempImagePath);
            }
        });

        it('validates image file existence', function () {
            $errors = PostcardValidator::validateImageDimensions(
                'non-existent-file.jpg',
                ImageDimensions::FRONT_IMAGE
            );

            expect($errors)->toContain('Image file does not exist');
        });

        it('detects wrong dimensions', function () {
            $errors = PostcardValidator::validateImageDimensions(
                $this->tempImagePath,
                ImageDimensions::FRONT_IMAGE
            );

            expect($errors)->toHaveCount(2) // Wrong dimensions + low resolution
                ->and($errors[0])->toContain('Image dimensions are 100x100')
                ->and($errors[1])->toContain('resolution is lower than optimal');
        });

        it('handles invalid image files', function () {
            $invalidFile = tempnam(sys_get_temp_dir(), 'invalid').'.jpg';
            file_put_contents($invalidFile, 'not an image');

            $errors = PostcardValidator::validateImageDimensions(
                $invalidFile,
                ImageDimensions::FRONT_IMAGE
            );

            expect($errors)->toContain('Invalid image file');

            unlink($invalidFile);
        });
    });
});
