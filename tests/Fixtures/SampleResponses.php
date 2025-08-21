<?php

namespace Gigerit\PostcardApi\Tests\Fixtures;

class SampleResponses
{
    public static function successResponse(): array
    {
        return [
            'cardKey' => 'test-card-key-123',
            'successMessage' => 'Postcard created successfully',
            'errors' => [],
            'warnings' => [],
        ];
    }

    public static function responseWithWarnings(): array
    {
        return [
            'cardKey' => 'test-card-key-123',
            'successMessage' => 'Postcard created successfully',
            'errors' => [],
            'warnings' => [
                [
                    'code' => 5020,
                    'description' => 'Image: higher resolution recommended',
                ],
            ],
        ];
    }

    public static function responseWithErrors(): array
    {
        return [
            'cardKey' => 'test-card-key-123',
            'errors' => [
                [
                    'code' => 1006,
                    'description' => 'Recipient address is required',
                ],
                [
                    'code' => 1007,
                    'description' => 'Front image is required',
                ],
            ],
            'warnings' => [],
        ];
    }

    public static function postcardStateResponse(): array
    {
        return [
            'cardKey' => 'test-card-key-123',
            'state' => [
                'state' => 'CREATED',
                'date' => '2024-01-15',
            ],
            'warnings' => [],
        ];
    }

    public static function previewResponse(): array
    {
        return [
            'cardKey' => 'test-card-key-123',
            'fileType' => 'image/jpeg',
            'encoding' => 'base64',
            'side' => 'front',
            'imagedata' => base64_encode('fake-image-data'),
            'errors' => [],
        ];
    }

    public static function campaignStatisticsResponse(): array
    {
        return [
            'campaignKey' => 'test-campaign-123',
            'quota' => 1000,
            'sendPostcards' => 250,
            'freeToSendPostcards' => 750,
        ];
    }

    public static function oauthTokenResponse(): array
    {
        return [
            'access_token' => 'test-access-token-123',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scope' => 'PCCAPI',
        ];
    }
}
