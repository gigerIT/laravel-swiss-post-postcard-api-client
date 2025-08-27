<?php

namespace Gigerit\PostcardApi\Tests\Feature;

use Gigerit\PostcardApi\Connectors\SwissPostConnector;
use Gigerit\PostcardApi\Facades\PostcardApi as PostcardApiFacade;
use Gigerit\PostcardApi\PostcardApi;
use Gigerit\PostcardApi\Services\BrandingService;
use Gigerit\PostcardApi\Services\CampaignService;
use Gigerit\PostcardApi\Services\PostcardService;
use Gigerit\PostcardApi\Tests\Fixtures\SampleResponses;
use Gigerit\PostcardApi\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class PostcardApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mock OAuth2 HTTP requests for all tests
        Http::fake([
            'test.auth.example.com/token' => Http::response(SampleResponses::oauthTokenResponse(), 200),
        ]);
    }

    public function test_can_instantiate_postcard_api()
    {
        $api = new PostcardApi;

        $this->assertInstanceOf(PostcardApi::class, $api);
    }

    public function test_can_instantiate_with_access_token()
    {
        $api = new PostcardApi('test-access-token');

        $this->assertInstanceOf(PostcardApi::class, $api);
    }

    public function test_provides_connector_access()
    {
        $api = new PostcardApi;
        $connector = $api->connector();

        $this->assertInstanceOf(SwissPostConnector::class, $connector);
    }

    public function test_provides_postcard_service()
    {
        $api = new PostcardApi;
        $service = $api->postcards();

        $this->assertInstanceOf(PostcardService::class, $service);
    }

    public function test_provides_branding_service()
    {
        $api = new PostcardApi;
        $service = $api->branding();

        $this->assertInstanceOf(BrandingService::class, $service);
    }

    public function test_provides_campaign_service()
    {
        $api = new PostcardApi;
        $service = $api->campaigns();

        $this->assertInstanceOf(CampaignService::class, $service);
    }

    public function test_can_set_access_token_fluently()
    {
        $api = new PostcardApi;
        $result = $api->withAccessToken('new-token');

        $this->assertInstanceOf(PostcardApi::class, $result);
        $this->assertSame($api, $result); // Should return same instance
    }

    public function test_facade_resolves_correctly()
    {
        $postcards = PostcardApiFacade::postcards();
        $branding = PostcardApiFacade::branding();
        $campaigns = PostcardApiFacade::campaigns();

        $this->assertInstanceOf(PostcardService::class, $postcards);
        $this->assertInstanceOf(BrandingService::class, $branding);
        $this->assertInstanceOf(CampaignService::class, $campaigns);
    }

    public function test_service_provider_registers_dependencies()
    {
        $api = app(PostcardApi::class);
        $connector = app(SwissPostConnector::class);
        $postcardService = app(PostcardService::class);
        $brandingService = app(BrandingService::class);
        $campaignService = app(CampaignService::class);

        $this->assertInstanceOf(PostcardApi::class, $api);
        $this->assertInstanceOf(SwissPostConnector::class, $connector);
        $this->assertInstanceOf(PostcardService::class, $postcardService);
        $this->assertInstanceOf(BrandingService::class, $brandingService);
        $this->assertInstanceOf(CampaignService::class, $campaignService);
    }

    public function test_services_are_singletons()
    {
        $api1 = app(PostcardApi::class);
        $api2 = app(PostcardApi::class);

        $connector1 = app(SwissPostConnector::class);
        $connector2 = app(SwissPostConnector::class);

        $this->assertSame($api1, $api2);
        $this->assertSame($connector1, $connector2);
    }

    public function test_configuration_is_loaded()
    {
        $baseUrl = config('swiss-post-postcard-api-client.base_url');
        $clientId = config('swiss-post-postcard-api-client.oauth.client_id');
        $defaultCampaign = config('swiss-post-postcard-api-client.default_campaign');

        $this->assertEquals('https://test.api.example.com', $baseUrl);
        $this->assertEquals('test-client-id', $clientId);
        $this->assertEquals('test-campaign-uuid', $defaultCampaign);
    }

    public function test_oauth2_integration_gracefully_handles_missing_credentials()
    {
        // Temporarily clear OAuth credentials
        config([
            'swiss-post-postcard-api-client.oauth.client_id' => null,
            'swiss-post-postcard-api-client.oauth.client_secret' => null,
        ]);

        // Should not throw exception, just continue without authentication
        $api = new PostcardApi;

        $this->assertInstanceOf(PostcardApi::class, $api);
    }
}
