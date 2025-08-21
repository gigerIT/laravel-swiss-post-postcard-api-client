<?php

namespace Gigerit\PostcardApi\Services;

use Gigerit\PostcardApi\Connectors\SwissPostConnector;
use Gigerit\PostcardApi\Exceptions\SwissPostApiException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Saloon\Http\Auth\AccessTokenAuthenticator;

class OAuth2Service
{
    protected SwissPostConnector $connector;

    public function __construct(?SwissPostConnector $connector = null)
    {
        $this->connector = $connector ?? new SwissPostConnector;
    }

    /**
     * Get an access token using direct HTTP request (bypassing Saloon OAuth for compatibility)
     */
    public static function getAccessToken(): string
    {
        $cacheKey = 'swiss_post_postcard_api_oauth2_token';

        // Try to get token from cache first
        $cachedToken = Cache::get($cacheKey);
        if ($cachedToken) {
            return $cachedToken;
        }

        try {
            // Use direct HTTP request instead of Saloon OAuth to avoid JSON parsing issues
            $clientId = config('swiss-post-postcard-api-client.oauth.client_id');
            $clientSecret = config('swiss-post-postcard-api-client.oauth.client_secret');
            $tokenUrl = config('swiss-post-postcard-api-client.oauth.token_url');
            $scope = config('swiss-post-postcard-api-client.oauth.scope');

            $response = Http::asForm()
                ->timeout(config('swiss-post-postcard-api-client.timeout', 30))
                ->post($tokenUrl, [
                    'grant_type' => 'client_credentials',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'scope' => $scope,
                ]);

            if (! $response->successful()) {
                throw new SwissPostApiException("OAuth2 request failed with status {$response->status()}: {$response->body()}");
            }

            $data = $response->json();

            if (! isset($data['access_token'])) {
                throw new SwissPostApiException('OAuth2 response does not contain access_token');
            }

            $accessToken = $data['access_token'];

            // Cache the token - default to 55 minutes if no expiry provided
            $expiresIn = $data['expires_in'] ?? 3600; // Default to 1 hour
            $cacheSeconds = max($expiresIn - 60, 60); // Cache for slightly less than expiry
            Cache::put($cacheKey, $accessToken, now()->addSeconds($cacheSeconds));

            return $accessToken;

        } catch (\Exception $e) {
            throw new SwissPostApiException(
                "Failed to obtain OAuth2 token: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get the full authenticator object
     */
    public function getAuthenticator(): AccessTokenAuthenticator
    {
        $accessToken = $this->getAccessToken();

        return new AccessTokenAuthenticator($accessToken);
    }

    /**
     * Clear the cached access token
     */
    public function clearToken(): void
    {
        Cache::forget('swiss_post_postcard_api_oauth2_token');
    }
}
