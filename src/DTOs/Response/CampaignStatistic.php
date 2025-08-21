<?php

namespace Gigerit\PostcardApi\DTOs\Response;

class CampaignStatistic
{
    public function __construct(
        public readonly string $campaignKey,
        public readonly int $quota,
        public readonly int $sendPostcards,
        public readonly int $freeToSendPostcards
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            campaignKey: $data['campaignKey'],
            quota: $data['quota'],
            sendPostcards: $data['sendPostcards'],
            freeToSendPostcards: $data['freeToSendPostcards']
        );
    }

    public function toArray(): array
    {
        return [
            'campaignKey' => $this->campaignKey,
            'quota' => $this->quota,
            'sendPostcards' => $this->sendPostcards,
            'freeToSendPostcards' => $this->freeToSendPostcards,
        ];
    }

    public function getRemainingQuota(): int
    {
        return $this->quota - $this->sendPostcards;
    }

    public function getUsagePercentage(): float
    {
        if ($this->quota === 0) {
            return 0.0;
        }

        return ($this->sendPostcards / $this->quota) * 100;
    }
}
