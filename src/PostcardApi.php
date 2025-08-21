<?php

namespace Gigerit\PostcardApi;

use Gigerit\PostcardApi\Connectors\SwissPostConnector;
use Gigerit\PostcardApi\Services\BrandingService;
use Gigerit\PostcardApi\Services\CampaignService;
use Gigerit\PostcardApi\Services\OAuth2Service;
use Gigerit\PostcardApi\Services\PostcardService;

class PostcardApi
{
    protected SwissPostConnector $connector;

    public function __construct(?string $accessToken = null)
    {
        $this->connector = new SwissPostConnector;

        $this->connector = $this->connector->withOAuth2Token($accessToken ?? OAuth2Service::getAccessToken());
    }

    /**
     * Get the Saloon connector instance
     */
    public function connector(): SwissPostConnector
    {
        return $this->connector;
    }

    /**
     * Set the OAuth2 access token
     */
    public function withAccessToken(string $accessToken): self
    {
        $this->connector = $this->connector->withOAuth2Token($accessToken);

        return $this;
    }

    /**
     * Get the postcard service
     */
    public function postcards(): PostcardService
    {
        return new PostcardService($this->connector);
    }

    /**
     * Get the branding service
     */
    public function branding(): BrandingService
    {
        return new BrandingService($this->connector);
    }

    /**
     * Get the campaign service
     */
    public function campaigns(): CampaignService
    {
        return new CampaignService($this->connector);
    }
}
