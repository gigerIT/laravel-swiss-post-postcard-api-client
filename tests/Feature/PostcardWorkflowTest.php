<?php

namespace Gigerit\PostcardApi\Tests\Feature;

use Gigerit\PostcardApi\PostcardApi;
use Gigerit\PostcardApi\Tests\Fixtures\SampleResponses;
use Gigerit\PostcardApi\Tests\Fixtures\TestData;
use Gigerit\PostcardApi\Tests\TestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

class PostcardWorkflowTest extends TestCase
{
    private PostcardApi $api;

    private MockClient $mockClient;

    private string $tempImagePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->api = new PostcardApi('test-token');
        $this->mockClient = new MockClient;
        $this->api->connector()->withMockClient($this->mockClient);

        // Create test image
        $this->tempImagePath = tempnam(sys_get_temp_dir(), 'test_image').'.jpg';
        $image = imagecreate(1819, 1311);
        imagecolorallocate($image, 255, 255, 255);
        imagejpeg($image, $this->tempImagePath);
        imagedestroy($image);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempImagePath)) {
            unlink($this->tempImagePath);
        }
        parent::tearDown();
    }

    public function test_complete_postcard_creation_workflow()
    {
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
        $this->assertEquals(750, $stats->freeToSendPostcards);
        $this->assertTrue($stats->getRemainingQuota() > 0);

        // 2. Create postcard
        $recipient = TestData::validRecipientAddress();
        $postcard = TestData::validPostcard();

        $createResult = $this->api->postcards()->create('test-campaign', $postcard);
        $cardKey = $createResult->cardKey;

        $this->assertEquals('test-card-key-123', $cardKey);
        $this->assertFalse($createResult->hasErrors());

        // 3. Upload image
        $uploadResult = $this->api->postcards()->uploadImage($cardKey, $this->tempImagePath, 'test.jpg', false);
        $this->assertFalse($uploadResult->hasErrors());

        // 4. Upload sender text
        $textResult = $this->api->postcards()->uploadSenderText($cardKey, 'Hello from Switzerland!', false);
        $this->assertFalse($textResult->hasErrors());

        // 5. Add branding
        $brandingResult = $this->api->branding()->addSimpleText($cardKey, 'Your Company', '#FF0000', '#FFFFFF');
        $this->assertFalse($brandingResult->hasErrors());

        // 6. Check state
        $state = $this->api->postcards()->getState($cardKey);
        $this->assertEquals('CREATED', $state->state->state);

        // 7. Approve postcard
        $approveResult = $this->api->postcards()->approve($cardKey);
        $this->assertFalse($approveResult->hasErrors());

        // 8. Get preview
        $preview = $this->api->postcards()->getPreviewFront($cardKey);
        $this->assertEquals('image/jpeg', $preview->fileType);
        $this->assertEquals('front', $preview->side);

        // Verify all requests were made
        $this->mockClient->assertSentCount(8);
    }

    public function test_error_handling_in_workflow()
    {
        // Mock error response
        $this->mockClient->addResponse(
            MockResponse::make(SampleResponses::responseWithErrors(), 400)
        );

        $this->expectException(\Exception::class);

        // This should throw an exception due to the error response
        $this->api->postcards()->create('test-campaign');
    }

    public function test_warning_handling_in_workflow()
    {
        // Mock response with warnings
        $this->mockClient->addResponse(
            MockResponse::make(SampleResponses::responseWithWarnings())
        );

        $result = $this->api->postcards()->create('test-campaign');

        $this->assertTrue($result->hasWarnings());
        $this->assertFalse($result->hasErrors());
        $this->assertCount(1, $result->warnings);
        $this->assertEquals('Image: higher resolution recommended', $result->getWarningMessages()[0]);
    }

    public function test_validation_errors_are_caught_early()
    {
        // Test that validation catches errors before making API calls
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Sender text validation failed');

        // This should fail validation before any API call
        $longText = str_repeat('A', 1000); // Too long
        $this->api->postcards()->uploadSenderText('test-card-key', $longText);

        // No API calls should have been made
        $this->mockClient->assertSentCount(0);
    }

    public function test_image_validation_before_upload()
    {
        // Create wrong size image
        $wrongImage = tempnam(sys_get_temp_dir(), 'wrong_image').'.jpg';
        $image = imagecreate(100, 100); // Wrong dimensions
        imagecolorallocate($image, 255, 255, 255);
        imagejpeg($image, $wrongImage);
        imagedestroy($image);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Image validation failed');

        try {
            $this->api->postcards()->uploadImage('test-card-key', $wrongImage);
        } finally {
            unlink($wrongImage);
        }

        // No API calls should have been made
        $this->mockClient->assertSentCount(0);
    }

    public function test_campaign_quota_check()
    {
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

        $this->assertEquals(0, $stats->freeToSendPostcards);
        $this->assertEquals(100.0, $stats->getUsagePercentage());
        $this->assertFalse($this->api->campaigns()->hasRemainingQuota('test-campaign'));
    }
}
