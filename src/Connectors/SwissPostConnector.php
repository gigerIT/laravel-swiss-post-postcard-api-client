<?php

namespace Gigerit\PostcardApi\Connectors;

use Gigerit\PostcardApi\Exceptions\SwissPostApiException;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Response;
use Saloon\Traits\OAuth2\ClientCredentialsGrant;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class SwissPostConnector extends Connector
{
    use AcceptsJson;
    use AlwaysThrowOnErrors;
    use ClientCredentialsGrant;

    public function resolveBaseUrl(): string
    {
        return config('swiss-post-postcard-api-client.base_url');
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function defaultOauthHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
    }

    protected function defaultConfig(): array
    {
        return [
            'timeout' => config('swiss-post-postcard-api-client.timeout', 30),
        ];
    }

    protected function defaultOauthConfig(): OAuthConfig
    {
        $clientId = config('swiss-post-postcard-api-client.oauth.client_id');
        $clientSecret = config('swiss-post-postcard-api-client.oauth.client_secret');
        $tokenUrl = config('swiss-post-postcard-api-client.oauth.token_url');
        $scope = config('swiss-post-postcard-api-client.oauth.scope');

        if (! $clientId || ! $clientSecret || ! $tokenUrl) {
            throw new \InvalidArgumentException('OAuth2 configuration is incomplete. Please check your credentials.');
        }

        return OAuthConfig::make()
            ->setClientId($clientId)
            ->setClientSecret($clientSecret)
            ->setTokenEndpoint($tokenUrl)
            ->setDefaultScopes([$scope]);
    }

    public function withOAuth2Token(string $accessToken): static
    {
        return $this->authenticate(new TokenAuthenticator($accessToken));
    }

    public function withOAuth2(): static
    {
        $authenticator = $this->getAccessToken();

        return $this->authenticate($authenticator);
    }

    public function hasRequestFailed(Response $response): ?bool
    {
        // Always use status code for OAuth token requests
        if (str_contains($response->getPendingRequest()->getUri(), '/OAuth/token')) {
            return $response->status() >= 400;
        }

        // Check if response is HTML (Swiss Post returns HTML error pages for auth failures)
        $contentType = $response->header('Content-Type') ?? '';
        if (str_contains($contentType, 'text/html')) {
            return $response->status() >= 400;
        }

        // Only try JSON parsing if we have JSON content
        if (str_contains($contentType, 'application/json')) {
            try {
                $data = $response->json();

                // Swiss Post API returns errors in the response body even with 200 status
                if (isset($data['errors']) && ! empty($data['errors'])) {
                    return true;
                }
            } catch (\Exception $e) {
                // If JSON parsing fails, fall back to status code check
                return $response->status() >= 400;
            }
        }

        return $response->status() >= 400;
    }

    public function getRequestException(Response $response, ?\Throwable $senderException): ?\Throwable
    {
        // Don't apply custom error handling to OAuth token requests
        if (str_contains($response->getPendingRequest()->getUri(), '/OAuth/token')) {
            return $senderException;
        }

        // Check if response is HTML (Swiss Post returns HTML error pages for auth failures)
        $contentType = $response->header('Content-Type') ?? '';
        if (str_contains($contentType, 'text/html')) {
            $bodyPreview = substr($response->body(), 0, 2000);

            return new SwissPostApiException(
                "Postcard API Error {$response->status()} {$bodyPreview}",
                $response->status(),
                $senderException
            );
        }

        // Only try JSON parsing if we have JSON content
        if (str_contains($contentType, 'application/json')) {
            try {
                $data = $response->json();

                if (isset($data['errors']) && ! empty($data['errors'])) {
                    $errors = collect($data['errors'])->map(function ($error) {
                        return sprintf('[%d] %s', $error['code'], $error['description']);
                    })->implode(', ');

                    return new SwissPostApiException(
                        "Swiss Post API Error: {$errors}",
                        $response->status(),
                        $senderException
                    );
                }
            } catch (\Exception $e) {
                // If JSON parsing fails, return a descriptive error
                $bodyPreview = substr($response->body(), 0, 2000);

                return new SwissPostApiException(
                    "Postcard API Error {$response->status()} {$bodyPreview}",
                    $response->status(),
                    $senderException
                );
            }
        }

        return $senderException;
    }
}
