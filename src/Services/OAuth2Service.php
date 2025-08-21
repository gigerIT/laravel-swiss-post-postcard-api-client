<?php

namespace Gigerit\PostcardApi\Services;

use Gigerit\PostcardApi\Connectors\SwissPostConnector;
use Gigerit\PostcardApi\Exceptions\SwissPostApiException;
use Illuminate\Support\Facades\Cache;
use Saloon\Http\Auth\AccessTokenAuthenticator;

class OAuth2Service
{
    protected SwissPostConnector $connector;

    public function __construct(?SwissPostConnector $connector = null)
    {
        $this->connector = $connector ?? new SwissPostConnector;
    }

    /**
     * Get an access token using Saloon's OAuth2 client credentials grant
     */
    public function getAccessToken(): string
    {
        $cacheKey = 'swiss_post_oauth2_token';

        // Try to get token from cache first
        $cachedToken = Cache::get($cacheKey);
        if ($cachedToken) {
            return $cachedToken;
        }

        try {
            // Use Saloon's built-in OAuth2 client credentials grant
            $authenticator = $this->connector->getAccessToken();

            if (! $authenticator instanceof AccessTokenAuthenticator) {
                throw new SwissPostApiException('Invalid authenticator type returned from OAuth2 flow');
            }

            $accessToken = $authenticator->getAccessToken();

            // Cache the token based on expiry
            $expiresAt = $authenticator->getExpiresAt();
            if ($expiresAt) {
                $expiresIn = $expiresAt->getTimestamp() - time();
                // Cache for slightly less than expiry time to avoid edge cases
                Cache::put($cacheKey, $accessToken, now()->addSeconds(max($expiresIn - 60, 60)));
            } else {
                // Default cache time if no expiry provided
                Cache::put($cacheKey, $accessToken, now()->addMinutes(55));
            }

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
        return $this->connector->getAccessToken();
    }

    /**
     * Clear the cached access token
     */
    public function clearToken(): void
    {
        Cache::forget('swiss_post_oauth2_token');
    }
}
