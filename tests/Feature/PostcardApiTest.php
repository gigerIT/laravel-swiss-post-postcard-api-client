<?php

use Gigerit\PostcardApi\Connectors\SwissPostConnector;
use Gigerit\PostcardApi\Facades\PostcardApi as PostcardApiFacade;
use Gigerit\PostcardApi\PostcardApi;
use Gigerit\PostcardApi\Services\BrandingService;
use Gigerit\PostcardApi\Services\CampaignService;
use Gigerit\PostcardApi\Services\PostcardService;
use Gigerit\PostcardApi\Tests\Fixtures\SampleResponses;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    // Mock OAuth2 HTTP requests for all tests
    Http::fake([
        'test.auth.example.com/token' => Http::response(SampleResponses::oauthTokenResponse(), 200),
    ]);
});

describe('PostcardApi instantiation', function () {
    it('can be instantiated without parameters', function () {
        $api = new PostcardApi;

        expect($api)->toBeInstanceOf(PostcardApi::class);
    });

    it('can be instantiated with access token', function () {
        $api = new PostcardApi('test-access-token');

        expect($api)->toBeInstanceOf(PostcardApi::class);
    });
});

describe('PostcardApi service access', function () {
    it('provides connector access', function () {
        $api = new PostcardApi;
        $connector = $api->connector();

        expect($connector)->toBeInstanceOf(SwissPostConnector::class);
    });

    it('provides postcard service', function () {
        $api = new PostcardApi;
        $service = $api->postcards();

        expect($service)->toBeInstanceOf(PostcardService::class);
    });

    it('provides branding service', function () {
        $api = new PostcardApi;
        $service = $api->branding();

        expect($service)->toBeInstanceOf(BrandingService::class);
    });

    it('provides campaign service', function () {
        $api = new PostcardApi;
        $service = $api->campaigns();

        expect($service)->toBeInstanceOf(CampaignService::class);
    });
});

describe('PostcardApi fluent interface', function () {
    it('can set access token fluently', function () {
        $api = new PostcardApi;
        $result = $api->withAccessToken('new-token');

        expect($result)->toBeInstanceOf(PostcardApi::class)
            ->and($result)->toBe($api); // Should return same instance
    });
});

describe('PostcardApi facade', function () {
    it('resolves services correctly', function () {
        $postcards = PostcardApiFacade::postcards();
        $branding = PostcardApiFacade::branding();
        $campaigns = PostcardApiFacade::campaigns();

        expect($postcards)->toBeInstanceOf(PostcardService::class)
            ->and($branding)->toBeInstanceOf(BrandingService::class)
            ->and($campaigns)->toBeInstanceOf(CampaignService::class);
    });
});

describe('PostcardApi service provider', function () {
    it('registers dependencies correctly', function () {
        $api = app(PostcardApi::class);
        $connector = app(SwissPostConnector::class);
        $postcardService = app(PostcardService::class);
        $brandingService = app(BrandingService::class);
        $campaignService = app(CampaignService::class);

        expect($api)->toBeInstanceOf(PostcardApi::class)
            ->and($connector)->toBeInstanceOf(SwissPostConnector::class)
            ->and($postcardService)->toBeInstanceOf(PostcardService::class)
            ->and($brandingService)->toBeInstanceOf(BrandingService::class)
            ->and($campaignService)->toBeInstanceOf(CampaignService::class);
    });

    it('registers services as singletons', function () {
        $api1 = app(PostcardApi::class);
        $api2 = app(PostcardApi::class);

        $connector1 = app(SwissPostConnector::class);
        $connector2 = app(SwissPostConnector::class);

        expect($api1)->toBe($api2)
            ->and($connector1)->toBe($connector2);
    });
});

describe('PostcardApi configuration', function () {
    it('loads configuration correctly', function () {
        $baseUrl = config('swiss-post-postcard-api-client.base_url');
        $clientId = config('swiss-post-postcard-api-client.oauth.client_id');
        $defaultCampaign = config('swiss-post-postcard-api-client.default_campaign');

        expect($baseUrl)->toBe('https://test.api.example.com')
            ->and($clientId)->toBe('test-client-id')
            ->and($defaultCampaign)->toBe('test-campaign-uuid');
    });

    it('gracefully handles missing OAuth credentials', function () {
        // Temporarily clear OAuth credentials
        config([
            'swiss-post-postcard-api-client.oauth.client_id' => null,
            'swiss-post-postcard-api-client.oauth.client_secret' => null,
        ]);

        // Should not throw exception, just continue without authentication
        $api = new PostcardApi;

        expect($api)->toBeInstanceOf(PostcardApi::class);
    });
});
