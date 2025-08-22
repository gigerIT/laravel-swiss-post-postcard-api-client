<?php

namespace Gigerit\PostcardApi\Requests\Campaigns;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Plugins\AcceptsJson;

class GetCampaignStatisticRequest extends Request
{
    use AcceptsJson;

    protected Method $method = Method::GET;

    public function __construct(
        protected string $campaignKey
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/campaigns/{$this->campaignKey}/statistic";
    }
}
