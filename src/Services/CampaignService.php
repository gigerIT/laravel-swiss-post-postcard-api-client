<?php

namespace Gigerit\PostcardApi\Services;

use Gigerit\PostcardApi\Connectors\SwissPostConnector;
use Gigerit\PostcardApi\DTOs\Response\CampaignStatistic;
use Gigerit\PostcardApi\Requests\Campaigns\GetCampaignStatisticRequest;

class CampaignService
{
    public function __construct(
        protected SwissPostConnector $connector
    ) {}

    /**
     * Get campaign statistics
     */
    public function getStatistics(string $campaignKey): CampaignStatistic
    {
        $request = new GetCampaignStatisticRequest($campaignKey);
        $response = $this->connector->send($request);

        return CampaignStatistic::fromArray($response->json());
    }

    /**
     * Get statistics for the default campaign
     */
    public function getDefaultCampaignStatistics(): CampaignStatistic
    {
        $campaignKey = config('swiss-post-postcard-api-client.default_campaign');

        if (! $campaignKey) {
            throw new \InvalidArgumentException('Default campaign key is not set. Please set SWISS_POST_POSTCARD_API_DEFAULT_CAMPAIGN in your .env file.');
        }

        return $this->getStatistics($campaignKey);
    }

    /**
     * Check if campaign has remaining quota
     */
    public function hasRemainingQuota(string $campaignKey): bool
    {
        $statistics = $this->getStatistics($campaignKey);

        return $statistics->getRemainingQuota() > 0;
    }

    /**
     * Get remaining quota for campaign
     */
    public function getRemainingQuota(string $campaignKey): int
    {
        $statistics = $this->getStatistics($campaignKey);

        return $statistics->getRemainingQuota();
    }
}
