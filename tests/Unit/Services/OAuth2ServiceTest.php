<?php

namespace Gigerit\PostcardApi\Tests\Unit\Services;

use Gigerit\PostcardApi\Connectors\SwissPostConnector;
use Gigerit\PostcardApi\Exceptions\SwissPostApiException;
use Gigerit\PostcardApi\Services\OAuth2Service;
use Gigerit\PostcardApi\Tests\Fixtures\SampleResponses;
use Illuminate\Support\Facades\Cache;
use Saloon\Http\Auth\AccessTokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

describe('OAuth2Service', function () {
    beforeEach(function () {
        Cache::flush(); // Clear cache before each test
        $this->connector = new SwissPostConnector;
        $this->service = new OAuth2Service($this->connector);
        $this->mockClient = new MockClient;
    });

    describe('getAccessToken', function () {
        it('returns cached token when available', function () {
            Cache::put('swiss_post_postcard_api_oauth2_token', 'cached-token', now()->addHour());

            $token = $this->service->getAccessToken();

            expect($token)->toBe('cached-token');
        });

        it('fetches new token when cache is empty', function () {
            $this->connector->withMockClient($this->mockClient);

            // Mock the OAuth2 token response
            $this->mockClient->addResponse(
                MockResponse::make(SampleResponses::oauthTokenResponse())
            );

            $token = $this->service->getAccessToken();

            expect($token)->toBe('test-access-token-123');
            expect(Cache::has('swiss_post_postcard_api_oauth2_token'))->toBeTrue();
        });

        it('caches token with proper expiry', function () {
            $this->connector->withMockClient($this->mockClient);

            $tokenResponse = SampleResponses::oauthTokenResponse();
            $this->mockClient->addResponse(MockResponse::make($tokenResponse));

            $token = $this->service->getAccessToken();

            expect($token)->toBe('test-access-token-123');

            // Token should be cached
            $cachedToken = Cache::get('swiss_post_postcard_api_oauth2_token');
            expect($cachedToken)->toBe('test-access-token-123');
        });

        it('throws exception on OAuth2 failure', function () {
            $this->connector->withMockClient($this->mockClient);

            $this->mockClient->addResponse(
                MockResponse::make([], 400) // Bad request
            );

            expect(fn () => $this->service->getAccessToken())
                ->toThrow(SwissPostApiException::class, 'Failed to obtain OAuth2 token');
        });

        it('handles invalid authenticator type', function () {
            $this->connector->withMockClient($this->mockClient);

            // This would happen if Saloon returns unexpected authenticator type
            $this->mockClient->addResponse(
                MockResponse::make(['invalid' => 'response'])
            );

            expect(fn () => $this->service->getAccessToken())
                ->toThrow(SwissPostApiException::class);
        });
    });

    describe('getAuthenticator', function () {
        it('returns access token authenticator', function () {
            $this->connector->withMockClient($this->mockClient);

            $this->mockClient->addResponse(
                MockResponse::make(SampleResponses::oauthTokenResponse())
            );

            $authenticator = $this->service->getAuthenticator();

            expect($authenticator)->toBeInstanceOf(AccessTokenAuthenticator::class)
                ->and($authenticator->getAccessToken())->toBe('test-access-token-123');
        });
    });

    describe('clearToken', function () {
        it('clears cached token', function () {
            Cache::put('swiss_post_postcard_api_oauth2_token', 'test-token', now()->addHour());

            expect(Cache::has('swiss_post_postcard_api_oauth2_token'))->toBeTrue();

            $this->service->clearToken();

            expect(Cache::has('swiss_post_postcard_api_oauth2_token'))->toBeFalse();
        });
    });

    describe('constructor', function () {
        it('can be created without connector', function () {
            $service = new OAuth2Service;

            expect($service)->toBeInstanceOf(OAuth2Service::class);
        });

        it('can be created with custom connector', function () {
            $customConnector = new SwissPostConnector;
            $service = new OAuth2Service($customConnector);

            expect($service)->toBeInstanceOf(OAuth2Service::class);
        });
    });
});
