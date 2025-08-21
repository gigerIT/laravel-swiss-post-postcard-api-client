<?php

namespace Gigerit\PostcardApi\Tests\Unit\DTOs;

use Gigerit\PostcardApi\DTOs\Branding\Branding;
use Gigerit\PostcardApi\DTOs\Branding\BrandingQRCode;
use Gigerit\PostcardApi\DTOs\Branding\BrandingText;
use Gigerit\PostcardApi\Tests\Fixtures\TestData;

describe('BrandingText', function () {
    it('can be created with text only', function () {
        $branding = new BrandingText('Test Company');

        expect($branding->text)->toBe('Test Company')
            ->and($branding->blockColor)->toBeNull()
            ->and($branding->textColor)->toBeNull();
    });

    it('can be created with all properties', function () {
        $branding = TestData::validBrandingText();

        expect($branding->text)->toBe('Your Company Name')
            ->and($branding->blockColor)->toBe('#FF0000')
            ->and($branding->textColor)->toBe('#FFFFFF');
    });

    it('can be converted to array', function () {
        $branding = TestData::validBrandingText();
        $array = $branding->toArray();

        expect($array)->toHaveKeys(['text', 'blockColor', 'textColor'])
            ->and($array['text'])->toBe('Your Company Name')
            ->and($array['blockColor'])->toBe('#FF0000')
            ->and($array['textColor'])->toBe('#FFFFFF');
    });

    it('filters null values in array conversion', function () {
        $branding = new BrandingText('Test');
        $array = $branding->toArray();

        expect($array)->toHaveKey('text')
            ->and($array)->not->toHaveKey('blockColor')
            ->and($array)->not->toHaveKey('textColor');
    });

    it('can be created from array', function () {
        $data = [
            'text' => 'Company Name',
            'blockColor' => '#FF0000',
            'textColor' => '#FFFFFF',
        ];

        $branding = BrandingText::fromArray($data);

        expect($branding->text)->toBe('Company Name')
            ->and($branding->blockColor)->toBe('#FF0000')
            ->and($branding->textColor)->toBe('#FFFFFF');
    });
});

describe('BrandingQRCode', function () {
    it('can be created with minimal data', function () {
        $qr = new BrandingQRCode;

        expect($qr->encodedText)->toBeNull()
            ->and($qr->accompanyingText)->toBeNull()
            ->and($qr->blockColor)->toBeNull()
            ->and($qr->textColor)->toBeNull();
    });

    it('can be created with all properties', function () {
        $qr = TestData::validBrandingQRCode();

        expect($qr->encodedText)->toBe('https://example.com')
            ->and($qr->accompanyingText)->toBe('Visit our website')
            ->and($qr->blockColor)->toBe('#000000')
            ->and($qr->textColor)->toBe('#FFFFFF');
    });

    it('can be converted to array', function () {
        $qr = TestData::validBrandingQRCode();
        $array = $qr->toArray();

        expect($array)->toHaveKeys(['encodedText', 'accompanyingText', 'blockColor', 'textColor'])
            ->and($array['encodedText'])->toBe('https://example.com')
            ->and($array['accompanyingText'])->toBe('Visit our website');
    });

    it('filters null values in array conversion', function () {
        $qr = new BrandingQRCode(encodedText: 'https://test.com');
        $array = $qr->toArray();

        expect($array)->toHaveKey('encodedText')
            ->and($array)->not->toHaveKey('accompanyingText')
            ->and($array)->not->toHaveKey('blockColor')
            ->and($array)->not->toHaveKey('textColor');
    });
});

describe('Branding', function () {
    it('can be created with no branding elements', function () {
        $branding = new Branding;

        expect($branding->brandingText)->toBeNull()
            ->and($branding->brandingQRCode)->toBeNull()
            ->and($branding->hasBrandingText())->toBeFalse()
            ->and($branding->hasBrandingQRCode())->toBeFalse();
    });

    it('can be created with text branding', function () {
        $brandingText = TestData::validBrandingText();
        $branding = new Branding(brandingText: $brandingText);

        expect($branding->brandingText)->toBe($brandingText)
            ->and($branding->brandingQRCode)->toBeNull()
            ->and($branding->hasBrandingText())->toBeTrue()
            ->and($branding->hasBrandingQRCode())->toBeFalse();
    });

    it('can be created with QR code branding', function () {
        $qrCode = TestData::validBrandingQRCode();
        $branding = new Branding(brandingQRCode: $qrCode);

        expect($branding->brandingText)->toBeNull()
            ->and($branding->brandingQRCode)->toBe($qrCode)
            ->and($branding->hasBrandingText())->toBeFalse()
            ->and($branding->hasBrandingQRCode())->toBeTrue();
    });

    it('can be created with both branding elements', function () {
        $brandingText = TestData::validBrandingText();
        $qrCode = TestData::validBrandingQRCode();
        $branding = new Branding(brandingText: $brandingText, brandingQRCode: $qrCode);

        expect($branding->brandingText)->toBe($brandingText)
            ->and($branding->brandingQRCode)->toBe($qrCode)
            ->and($branding->hasBrandingText())->toBeTrue()
            ->and($branding->hasBrandingQRCode())->toBeTrue();
    });

    it('can be converted to array', function () {
        $brandingText = TestData::validBrandingText();
        $qrCode = TestData::validBrandingQRCode();
        $branding = new Branding(brandingText: $brandingText, brandingQRCode: $qrCode);

        $array = $branding->toArray();

        expect($array)->toHaveKeys(['brandingText', 'brandingQRCode'])
            ->and($array['brandingText'])->toBeArray()
            ->and($array['brandingQRCode'])->toBeArray();
    });

    it('filters null values in array conversion', function () {
        $brandingText = TestData::validBrandingText();
        $branding = new Branding(brandingText: $brandingText);

        $array = $branding->toArray();

        expect($array)->toHaveKey('brandingText')
            ->and($array)->not->toHaveKey('brandingQRCode');
    });
});
