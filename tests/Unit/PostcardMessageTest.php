<?php

use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Gigerit\PostcardApi\DTOs\Address\SenderAddress;
use Gigerit\PostcardApi\Messages\PostcardMessage;

it('creates postcard message with image', function () {
    $message = PostcardMessage::create('/path/to/image.jpg');

    expect($message->imagePath)->toBe('/path/to/image.jpg');
    expect($message->recipientAddress)->toBeNull();
    expect($message->senderAddress)->toBeNull();
    expect($message->senderText)->toBeNull();
    expect($message->campaignKey)->toBeNull();
    expect($message->autoApprove)->toBeFalse();
});

it('sets recipient address', function () {
    $recipientAddress = new RecipientAddress(
        street: 'Test Street 1',
        zip: '8000',
        city: 'Zurich',
        country: 'Switzerland'
    );

    $message = PostcardMessage::create('/path/to/image.jpg')
        ->to($recipientAddress);

    expect($message->recipientAddress)->toBe($recipientAddress);
});

it('sets sender address', function () {
    $senderAddress = new SenderAddress(
        street: 'Sender Street 1',
        zip: '3000',
        city: 'Bern'
    );

    $message = PostcardMessage::create('/path/to/image.jpg')
        ->from($senderAddress);

    expect($message->senderAddress)->toBe($senderAddress);
});

it('sets sender text', function () {
    $message = PostcardMessage::create('/path/to/image.jpg')
        ->text('Hello from Laravel!');

    expect($message->senderText)->toBe('Hello from Laravel!');
});

it('sets campaign key', function () {
    $message = PostcardMessage::create('/path/to/image.jpg')
        ->campaign('test-campaign');

    expect($message->campaignKey)->toBe('test-campaign');
});

it('enables auto approve', function () {
    $message = PostcardMessage::create('/path/to/image.jpg')
        ->autoApprove();

    expect($message->autoApprove)->toBeTrue();
});

it('disables auto approve', function () {
    $message = PostcardMessage::create('/path/to/image.jpg')
        ->autoApprove(false);

    expect($message->autoApprove)->toBeFalse();
});

it('chains methods fluently', function () {
    $recipientAddress = new RecipientAddress(
        street: 'Test Street 1',
        zip: '8000',
        city: 'Zurich',
        country: 'Switzerland'
    );

    $senderAddress = new SenderAddress(
        street: 'Sender Street 1',
        zip: '3000',
        city: 'Bern'
    );

    $message = PostcardMessage::create('/path/to/image.jpg')
        ->to($recipientAddress)
        ->from($senderAddress)
        ->text('Hello from Laravel!')
        ->campaign('test-campaign')
        ->autoApprove();

    expect($message->imagePath)->toBe('/path/to/image.jpg');
    expect($message->recipientAddress)->toBe($recipientAddress);
    expect($message->senderAddress)->toBe($senderAddress);
    expect($message->senderText)->toBe('Hello from Laravel!');
    expect($message->campaignKey)->toBe('test-campaign');
    expect($message->autoApprove)->toBeTrue();
});

it('ensures immutability by creating new instances', function () {
    $originalMessage = PostcardMessage::create('/path/to/image.jpg');

    $recipientAddress = new RecipientAddress(
        street: 'Test Street 1',
        zip: '8000',
        city: 'Zurich',
        country: 'Switzerland'
    );

    $newMessage = $originalMessage->to($recipientAddress);

    expect($originalMessage)->not->toBe($newMessage);
    expect($originalMessage->recipientAddress)->toBeNull();
    expect($newMessage->recipientAddress)->toBe($recipientAddress);
});

describe('PostcardMessage validation', function () {
    it('preserves original values when chaining methods', function () {
        $message = PostcardMessage::create('/path/to/image.jpg')
            ->text('First text')
            ->text('Second text');

        expect($message->senderText)->toBe('Second text');
    });

    it('handles empty strings correctly', function () {
        $message = PostcardMessage::create('/path/to/image.jpg')
            ->text('')
            ->campaign('');

        expect($message->senderText)->toBe('');
        expect($message->campaignKey)->toBe('');
    });

    it('handles null values correctly when chaining', function () {
        $recipientAddress = new RecipientAddress(
            street: 'Test Street 1',
            zip: '8000',
            city: 'Zurich',
            country: 'Switzerland'
        );

        $message = PostcardMessage::create('/path/to/image.jpg')
            ->to($recipientAddress)
            ->text('Some text');

        $newMessage = new PostcardMessage(
            imagePath: $message->imagePath,
            recipientAddress: null, // Reset to null
            senderAddress: $message->senderAddress,
            senderText: $message->senderText,
            campaignKey: $message->campaignKey,
            autoApprove: $message->autoApprove
        );

        expect($newMessage->recipientAddress)->toBeNull();
        expect($newMessage->senderText)->toBe('Some text');
    });
});
