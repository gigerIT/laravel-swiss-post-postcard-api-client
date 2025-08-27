<?php

use Gigerit\PostcardApi\PostcardApi;
use Gigerit\PostcardApi\Tests\Fixtures\SampleResponses;
use Gigerit\PostcardApi\Tests\Fixtures\TestData;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

beforeEach(function () {
    $this->api = new PostcardApi('test-token');
    $this->mockClient = new MockClient;
    $this->api->connector()->withMockClient($this->mockClient);

    // Create test image
    $this->tempImagePath = tempnam(sys_get_temp_dir(), 'test_image').'.jpg';
    $image = imagecreate(1819, 1311);
    imagecolorallocate($image, 255, 255, 255);
    imagejpeg($image, $this->tempImagePath);
    imagedestroy($image);
});

afterEach(function () {
    if (file_exists($this->tempImagePath)) {
        unlink($this->tempImagePath);
    }
});

describe('PostcardWorkflow', function () {
    it('can complete a full postcard creation workflow', function () {
        // Mock all API responses for the workflow
        $this->mockClient->addResponses([
            // Check campaign statistics
            MockResponse::make(SampleResponses::campaignStatisticsResponse()),
            // Create postcard
            MockResponse::make(SampleResponses::successResponse()),
            // Upload image
            MockResponse::make(SampleResponses::successResponse()),
            // Upload sender text
            MockResponse::make(SampleResponses::successResponse()),
            // Add branding
            MockResponse::make(SampleResponses::successResponse()),
            // Get state
            MockResponse::make(SampleResponses::postcardStateResponse()),
            // Approve postcard
            MockResponse::make(SampleResponses::successResponse()),
            // Get preview
            MockResponse::make(SampleResponses::previewResponse()),
        ]);

        // 1. Check campaign quota
        $stats = $this->api->campaigns()->getDefaultCampaignStatistics();
        expect($stats->freeToSendPostcards)->toBe(750)
            ->and($stats->getRemainingQuota())->toBeGreaterThan(0);

        // 2. Create postcard
        $recipient = TestData::validRecipientAddress();
        $postcard = TestData::validPostcard();

        $createResult = $this->api->postcards()->create('test-campaign', $postcard);
        $cardKey = $createResult->cardKey;

        expect($cardKey)->toBe('test-card-key-123')
            ->and($createResult->hasErrors())->toBeFalse();

        // 3. Upload image
        $uploadResult = $this->api->postcards()->uploadImage($cardKey, $this->tempImagePath, false);
        expect($uploadResult->hasErrors())->toBeFalse();

        // 4. Upload sender text
        $textResult = $this->api->postcards()->uploadSenderText($cardKey, 'Hello from Switzerland!', false);
        expect($textResult->hasErrors())->toBeFalse();

        // 5. Add branding
        $brandingResult = $this->api->branding()->addSimpleText($cardKey, 'Your Company', '#FF0000', '#FFFFFF');
        expect($brandingResult->hasErrors())->toBeFalse();

        // 6. Check state
        $state = $this->api->postcards()->getState($cardKey);
        expect($state->state->state)->toBe('CREATED');

        // 7. Approve postcard
        $approveResult = $this->api->postcards()->approve($cardKey);
        expect($approveResult->hasErrors())->toBeFalse();

        // 8. Get preview
        $preview = $this->api->postcards()->getPreviewFront($cardKey);
        expect($preview->fileType)->toBe('image/jpeg')
            ->and($preview->side)->toBe('front');

        // Verify all requests were made
        $this->mockClient->assertSentCount(8);
    });

    it('handles errors in workflow correctly', function () {
        // Mock error response
        $this->mockClient->addResponse(
            MockResponse::make(SampleResponses::responseWithErrors(), 400)
        );

        expect(fn () => $this->api->postcards()->create('test-campaign'))
            ->toThrow(\Exception::class);
    });

    it('handles warnings in workflow correctly', function () {
        // Mock response with warnings
        $this->mockClient->addResponse(
            MockResponse::make(SampleResponses::responseWithWarnings())
        );

        $result = $this->api->postcards()->create('test-campaign');

        expect($result->hasWarnings())->toBeTrue()
            ->and($result->hasErrors())->toBeFalse()
            ->and($result->warnings)->toHaveCount(1)
            ->and($result->getWarningMessages()[0])->toBe('Image: higher resolution recommended');
    });

    it('catches validation errors early', function () {
        // Test that validation catches errors before making API calls
        $longText = str_repeat('A', 1000); // Too long

        expect(fn () => $this->api->postcards()->uploadSenderText('test-card-key', $longText))
            ->toThrow(\InvalidArgumentException::class, 'Sender text validation failed');

        // No API calls should have been made
        $this->mockClient->assertSentCount(0);
    });

    it('validates images before upload', function () {
        // Create wrong size image
        $wrongImage = tempnam(sys_get_temp_dir(), 'wrong_image').'.jpg';
        $image = imagecreate(100, 100); // Wrong dimensions
        imagecolorallocate($image, 255, 255, 255);
        imagejpeg($image, $wrongImage);
        imagedestroy($image);

        expect(function () use ($wrongImage) {
            try {
                $this->api->postcards()->uploadImage('test-card-key', $wrongImage);
            } finally {
                unlink($wrongImage);
            }
        })->toThrow(\InvalidArgumentException::class, 'Image validation failed');

        // No API calls should have been made
        $this->mockClient->assertSentCount(0);
    });

    it('checks campaign quota correctly', function () {
        $statisticsResponse = [
            'campaignKey' => 'test-campaign',
            'quota' => 100,
            'sendPostcards' => 100,
            'freeToSendPostcards' => 0,
        ];

        // Add two identical responses - one for getStatistics() and one for hasRemainingQuota()
        $this->mockClient->addResponses([
            MockResponse::make($statisticsResponse),
            MockResponse::make($statisticsResponse),
        ]);

        $stats = $this->api->campaigns()->getStatistics('test-campaign');

        expect($stats->freeToSendPostcards)->toBe(0)
            ->and($stats->getUsagePercentage())->toBe(100.0)
            ->and($this->api->campaigns()->hasRemainingQuota('test-campaign'))->toBeFalse();
    });
});
