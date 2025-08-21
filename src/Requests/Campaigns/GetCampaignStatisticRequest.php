<?php

namespace Gigerit\PostcardApi\Requests\Campaigns;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetCampaignStatisticRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        protected string $campaignKey
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/campaigns/{$this->campaignKey}/statistic";
    }
}
