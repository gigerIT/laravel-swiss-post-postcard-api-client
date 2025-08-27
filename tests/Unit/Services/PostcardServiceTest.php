<?php

namespace Gigerit\PostcardApi\Tests\Unit\Services;

use Gigerit\PostcardApi\Connectors\SwissPostConnector;
use Gigerit\PostcardApi\DTOs\Response\DefaultResponse;
use Gigerit\PostcardApi\DTOs\Response\PostcardStateResponse;
use Gigerit\PostcardApi\Requests\Postcards\ApprovePostcardRequest;
use Gigerit\PostcardApi\Requests\Postcards\CreatePostcardRequest;
use Gigerit\PostcardApi\Requests\Postcards\GetPostcardStateRequest;
use Gigerit\PostcardApi\Requests\Postcards\UploadImageRequest;
use Gigerit\PostcardApi\Requests\Postcards\UploadSenderTextRequest;
use Gigerit\PostcardApi\Services\PostcardService;
use Gigerit\PostcardApi\Tests\Fixtures\SampleResponses;
use Gigerit\PostcardApi\Tests\Fixtures\TestData;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

describe('PostcardService', function () {
    beforeEach(function () {
        $this->connector = new SwissPostConnector;
        $this->service = new PostcardService($this->connector);
        $this->mockClient = new MockClient;
    });

    describe('create', function () {
        it('creates a postcard successfully', function () {
            $this->connector->withMockClient($this->mockClient);

            $this->mockClient->addResponse(
                MockResponse::make(SampleResponses::successResponse())
            );

            $result = $this->service->create('test-campaign', TestData::validPostcard());

            expect($result)->toBeInstanceOf(DefaultResponse::class)
                ->and($result->cardKey)->toBe('test-card-key-123')
                ->and($result->hasErrors())->toBeFalse();

            $this->mockClient->assertSent(CreatePostcardRequest::class);
        });

        it('uses default campaign when none provided', function () {
            $this->connector->withMockClient($this->mockClient);

            $this->mockClient->addResponse(
                MockResponse::make(SampleResponses::successResponse())
            );

            $result = $this->service->create();

            expect($result)->toBeInstanceOf(DefaultResponse::class);
            $this->mockClient->assertSent(CreatePostcardRequest::class);
        });

        it('throws exception when no campaign provided and no default set', function () {
            config(['swiss-post-postcard-api-client.default_campaign' => null]);

            expect(fn () => $this->service->create())
                ->toThrow(\InvalidArgumentException::class, 'Campaign key is required');
        });
    });

    describe('uploadImage', function () {
        beforeEach(function () {
            // Create a temporary test image
            $this->tempImagePath = tempnam(sys_get_temp_dir(), 'test_image').'.jpg';
            $image = imagecreate(1819, 1311); // Correct dimensions
            imagecolorallocate($image, 255, 255, 255);
            imagejpeg($image, $this->tempImagePath);
            imagedestroy($image);
        });

        afterEach(function () {
            if (file_exists($this->tempImagePath)) {
                unlink($this->tempImagePath);
            }
        });

        it('uploads image successfully', function () {
            $this->connector->withMockClient($this->mockClient);

            $this->mockClient->addResponse(
                MockResponse::make(SampleResponses::successResponse())
            );

            $result = $this->service->uploadImage('test-card-key', $this->tempImagePath);

            expect($result)->toBeInstanceOf(DefaultResponse::class)
                ->and($result->cardKey)->toBe('test-card-key-123');

            $this->mockClient->assertSent(UploadImageRequest::class);
        });

        it('validates image dimensions by default', function () {
            // Create image with wrong dimensions
            $wrongImage = tempnam(sys_get_temp_dir(), 'wrong_image').'.jpg';
            $image = imagecreate(100, 100);
            imagecolorallocate($image, 255, 255, 255);
            imagejpeg($image, $wrongImage);
            imagedestroy($image);

            expect(fn () => $this->service->uploadImage('test-card-key', $wrongImage))
                ->toThrow(\InvalidArgumentException::class, 'Image validation failed');

            unlink($wrongImage);
        });

        it('can skip validation when requested', function () {
            // Create image with wrong dimensions
            $wrongImage = tempnam(sys_get_temp_dir(), 'wrong_image').'.jpg';
            $image = imagecreate(100, 100);
            imagecolorallocate($image, 255, 255, 255);
            imagejpeg($image, $wrongImage);
            imagedestroy($image);

            $this->connector->withMockClient($this->mockClient);
            $this->mockClient->addResponse(
                MockResponse::make(SampleResponses::successResponse())
            );

            $result = $this->service->uploadImage('test-card-key', $wrongImage, false);

            expect($result)->toBeInstanceOf(DefaultResponse::class);
            $this->mockClient->assertSent(UploadImageRequest::class);

            unlink($wrongImage);
        });
    });

    describe('uploadSenderText', function () {
        it('uploads sender text successfully', function () {
            $this->connector->withMockClient($this->mockClient);

            $this->mockClient->addResponse(
                MockResponse::make(SampleResponses::successResponse())
            );

            $result = $this->service->uploadSenderText('test-card-key', 'Hello World!');

            expect($result)->toBeInstanceOf(DefaultResponse::class);
            $this->mockClient->assertSent(UploadSenderTextRequest::class);
        });

        it('validates text length by default', function () {
            $longText = str_repeat('A', 1000); // Too long

            expect(fn () => $this->service->uploadSenderText('test-card-key', $longText))
                ->toThrow(\InvalidArgumentException::class, 'Sender text validation failed');
        });

        it('can skip validation when requested', function () {
            $this->connector->withMockClient($this->mockClient);
            $this->mockClient->addResponse(
                MockResponse::make(SampleResponses::successResponse())
            );

            $longText = str_repeat('A', 1000); // Too long
            $result = $this->service->uploadSenderText('test-card-key', $longText, false);

            expect($result)->toBeInstanceOf(DefaultResponse::class);
            $this->mockClient->assertSent(UploadSenderTextRequest::class);
        });
    });

    describe('uploadSenderAddress', function () {
        it('uploads sender address successfully', function () {
            $this->connector->withMockClient($this->mockClient);

            $this->mockClient->addResponse(
                MockResponse::make(SampleResponses::successResponse())
            );

            $address = TestData::validSenderAddress();
            $result = $this->service->uploadSenderAddress('test-card-key', $address);

            expect($result)->toBeInstanceOf(DefaultResponse::class);
        });

        it('validates address by default', function () {
            $invalidAddress = TestData::invalidSenderAddress();

            expect(fn () => $this->service->uploadSenderAddress('test-card-key', $invalidAddress))
                ->toThrow(\InvalidArgumentException::class);
        });
    });

    describe('approve', function () {
        it('approves postcard successfully', function () {
            $this->connector->withMockClient($this->mockClient);

            $this->mockClient->addResponse(
                MockResponse::make(SampleResponses::successResponse())
            );

            $result = $this->service->approve('test-card-key');

            expect($result)->toBeInstanceOf(DefaultResponse::class);
            $this->mockClient->assertSent(ApprovePostcardRequest::class);
        });
    });

    describe('getState', function () {
        it('gets postcard state successfully', function () {
            $this->connector->withMockClient($this->mockClient);

            $this->mockClient->addResponse(
                MockResponse::make(SampleResponses::postcardStateResponse())
            );

            $result = $this->service->getState('test-card-key');

            expect($result)->toBeInstanceOf(PostcardStateResponse::class)
                ->and($result->cardKey)->toBe('test-card-key-123')
                ->and($result->state->state)->toBe('CREATED');

            $this->mockClient->assertSent(GetPostcardStateRequest::class);
        });
    });

    describe('createComplete', function () {
        beforeEach(function () {
            // Create a temporary test image
            $this->tempImagePath = tempnam(sys_get_temp_dir(), 'test_image').'.jpg';
            $image = imagecreate(1819, 1311); // Correct dimensions
            imagecolorallocate($image, 255, 255, 255);
            imagejpeg($image, $this->tempImagePath);
            imagedestroy($image);
        });

        afterEach(function () {
            if (file_exists($this->tempImagePath)) {
                unlink($this->tempImagePath);
            }
        });

        it('creates complete postcard successfully', function () {
            $this->connector->withMockClient($this->mockClient);

            // Mock both create and upload image responses
            $this->mockClient->addResponses([
                MockResponse::make(SampleResponses::successResponse()),
                MockResponse::make(SampleResponses::successResponse()),
            ]);

            $recipient = TestData::validRecipientAddress();
            $sender = TestData::validSenderAddress();

            $result = $this->service->createComplete(
                recipientAddress: $recipient,
                imagePath: $this->tempImagePath,
                senderAddress: $sender,
                senderText: 'Hello World!'
            );

            expect($result)->toBeInstanceOf(DefaultResponse::class)
                ->and($result->cardKey)->toBe('test-card-key-123');

            $this->mockClient->assertSent(CreatePostcardRequest::class);
            $this->mockClient->assertSent(UploadImageRequest::class);
        });
    });
});
