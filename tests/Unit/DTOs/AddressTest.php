<?php

namespace Gigerit\PostcardApi\Tests\Unit\DTOs;

use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Gigerit\PostcardApi\DTOs\Address\SenderAddress;
use Gigerit\PostcardApi\Tests\Fixtures\TestData;

describe('SenderAddress', function () {
    it('can be created with required fields', function () {
        $address = new SenderAddress(
            street: 'Test Street',
            zip: '1234',
            city: 'Test City'
        );

        expect($address->street)->toBe('Test Street')
            ->and($address->zip)->toBe('1234')
            ->and($address->city)->toBe('Test City')
            ->and($address->firstname)->toBeNull()
            ->and($address->lastname)->toBeNull()
            ->and($address->company)->toBeNull()
            ->and($address->houseNr)->toBeNull();
    });

    it('can be created with all fields', function () {
        $address = TestData::validSenderAddress();

        expect($address->street)->toBe('Absenderstrasse')
            ->and($address->zip)->toBe('3000')
            ->and($address->city)->toBe('Bern')
            ->and($address->firstname)->toBe('Jane')
            ->and($address->lastname)->toBe('Smith')
            ->and($address->houseNr)->toBe('456');
    });

    it('can be converted to array', function () {
        $address = TestData::validSenderAddress();
        $array = $address->toArray();

        expect($array)->toHaveKeys([
            'street', 'zip', 'city', 'firstname', 'lastname', 'houseNr',
        ])->and($array['street'])->toBe('Absenderstrasse')
            ->and($array['zip'])->toBe('3000')
            ->and($array['city'])->toBe('Bern');
    });

    it('can be created from array', function () {
        $data = [
            'street' => 'Test Street',
            'zip' => '12345',
            'city' => 'Test City',
            'firstname' => 'John',
            'lastname' => 'Doe',
        ];

        $address = SenderAddress::fromArray($data);

        expect($address->street)->toBe('Test Street')
            ->and($address->firstname)->toBe('John')
            ->and($address->lastname)->toBe('Doe');
    });

    it('generates full name correctly', function () {
        $address = new SenderAddress(
            street: 'Test Street',
            zip: '12345',
            city: 'Test City',
            firstname: 'John',
            lastname: 'Doe'
        );

        expect($address->getFullName())->toBe('John Doe');
    });

    it('returns company name when no personal name provided', function () {
        $address = new SenderAddress(
            street: 'Test Street',
            zip: '12345',
            city: 'Test City',
            company: 'Test Company'
        );

        expect($address->getFullName())->toBe('Test Company');
    });

    it('generates full address correctly', function () {
        $address = TestData::validSenderAddress();

        expect($address->getFullAddress())->toBe('Absenderstrasse 456, 3000 Bern');
    });
});

describe('RecipientAddress', function () {
    it('can be created with required fields', function () {
        $address = new RecipientAddress(
            street: 'Test Street',
            zip: '12345',
            city: 'Test City',
            country: 'CH'
        );

        expect($address->street)->toBe('Test Street')
            ->and($address->zip)->toBe('12345')
            ->and($address->city)->toBe('Test City')
            ->and($address->country)->toBe('CH');
    });

    it('can be created with all fields', function () {
        $address = TestData::validRecipientAddress();

        expect($address->street)->toBe('Musterstrasse')
            ->and($address->zip)->toBe('8000')
            ->and($address->city)->toBe('Zürich')
            ->and($address->country)->toBe('CH')
            ->and($address->firstname)->toBe('John')
            ->and($address->lastname)->toBe('Doe')
            ->and($address->houseNr)->toBe('123');
    });

    it('generates full name with title correctly', function () {
        $address = new RecipientAddress(
            street: 'Test Street',
            zip: '12345',
            city: 'Test City',
            country: 'CH',
            title: 'Dr.',
            firstname: 'John',
            lastname: 'Doe'
        );

        expect($address->getFullName())->toBe('Dr. John Doe');
    });

    it('handles PO Box addresses correctly', function () {
        $address = new RecipientAddress(
            street: 'Main Street',
            zip: '12345',
            city: 'Test City',
            country: 'CH',
            poBox: '123'
        );

        $fullAddress = $address->getFullAddress();
        expect($fullAddress)->toContain('PO Box 123');
    });

    it('includes additional address info when provided', function () {
        $address = new RecipientAddress(
            street: 'Test Street',
            zip: '12345',
            city: 'Test City',
            country: 'CH',
            additionalAdrInfo: 'Building A, Floor 2'
        );

        $fullAddress = $address->getFullAddress();
        expect($fullAddress)->toContain('Building A, Floor 2');
    });
});
