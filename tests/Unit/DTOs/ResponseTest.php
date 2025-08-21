<?php

namespace Gigerit\PostcardApi\Tests\Unit\DTOs;

use DateTime;
use Gigerit\PostcardApi\DTOs\Response\CampaignStatistic;
use Gigerit\PostcardApi\DTOs\Response\CodeMessage;
use Gigerit\PostcardApi\DTOs\Response\DefaultResponse;
use Gigerit\PostcardApi\DTOs\Response\PostcardStateResponse;
use Gigerit\PostcardApi\DTOs\Response\Preview;
use Gigerit\PostcardApi\DTOs\Response\State;
use Gigerit\PostcardApi\Tests\Fixtures\SampleResponses;

describe('CodeMessage', function () {
    it('can be created with code and description', function () {
        $codeMessage = new CodeMessage(1006, 'Recipient address is required');

        expect($codeMessage->code)->toBe(1006)
            ->and($codeMessage->description)->toBe('Recipient address is required');
    });

    it('can be created from array', function () {
        $data = ['code' => 1007, 'description' => 'Front image is required'];
        $codeMessage = CodeMessage::fromArray($data);

        expect($codeMessage->code)->toBe(1007)
            ->and($codeMessage->description)->toBe('Front image is required');
    });

    it('can be converted to array', function () {
        $codeMessage = new CodeMessage(1006, 'Recipient address is required');
        $array = $codeMessage->toArray();

        expect($array)->toBe([
            'code' => 1006,
            'description' => 'Recipient address is required',
        ]);
    });
});

describe('DefaultResponse', function () {
    it('can be created with minimal data', function () {
        $response = new DefaultResponse('test-card-key');

        expect($response->cardKey)->toBe('test-card-key')
            ->and($response->successMessage)->toBeNull()
            ->and($response->errors)->toBeEmpty()
            ->and($response->warnings)->toBeEmpty()
            ->and($response->hasErrors())->toBeFalse()
            ->and($response->hasWarnings())->toBeFalse();
    });

    it('can be created from success response array', function () {
        $data = SampleResponses::successResponse();
        $response = DefaultResponse::fromArray($data);

        expect($response->cardKey)->toBe('test-card-key-123')
            ->and($response->successMessage)->toBe('Postcard created successfully')
            ->and($response->hasErrors())->toBeFalse()
            ->and($response->hasWarnings())->toBeFalse();
    });

    it('can be created from response with warnings', function () {
        $data = SampleResponses::responseWithWarnings();
        $response = DefaultResponse::fromArray($data);

        expect($response->cardKey)->toBe('test-card-key-123')
            ->and($response->hasWarnings())->toBeTrue()
            ->and($response->hasErrors())->toBeFalse()
            ->and($response->warnings)->toHaveCount(1)
            ->and($response->warnings[0]->code)->toBe(5020)
            ->and($response->getWarningMessages())->toBe(['Image: higher resolution recommended']);
    });

    it('can be created from response with errors', function () {
        $data = SampleResponses::responseWithErrors();
        $response = DefaultResponse::fromArray($data);

        expect($response->cardKey)->toBe('test-card-key-123')
            ->and($response->hasErrors())->toBeTrue()
            ->and($response->hasWarnings())->toBeFalse()
            ->and($response->errors)->toHaveCount(2)
            ->and($response->getErrorMessages())->toHaveCount(2)
            ->and($response->getErrorMessages()[0])->toBe('Recipient address is required');
    });
});

describe('State', function () {
    it('can be created with state and date', function () {
        $date = new DateTime('2024-01-15');
        $state = new State('CREATED', $date);

        expect($state->state)->toBe('CREATED')
            ->and($state->date)->toBe($date);
    });

    it('can be created from array', function () {
        $data = ['state' => 'APPROVED', 'date' => '2024-01-15'];
        $state = State::fromArray($data);

        expect($state->state)->toBe('APPROVED')
            ->and($state->date)->toBeInstanceOf(DateTime::class)
            ->and($state->date->format('Y-m-d'))->toBe('2024-01-15');
    });

    it('can be converted to array', function () {
        $date = new DateTime('2024-01-15');
        $state = new State('CREATED', $date);
        $array = $state->toArray();

        expect($array)->toBe([
            'state' => 'CREATED',
            'date' => '2024-01-15',
        ]);
    });
});

describe('PostcardStateResponse', function () {
    it('can be created from array', function () {
        $data = SampleResponses::postcardStateResponse();
        $response = PostcardStateResponse::fromArray($data);

        expect($response->cardKey)->toBe('test-card-key-123')
            ->and($response->state->state)->toBe('CREATED')
            ->and($response->state->date->format('Y-m-d'))->toBe('2024-01-15')
            ->and($response->hasWarnings())->toBeFalse();
    });
});

describe('Preview', function () {
    it('can be created from array', function () {
        $data = SampleResponses::previewResponse();
        $response = Preview::fromArray($data);

        expect($response->cardKey)->toBe('test-card-key-123')
            ->and($response->fileType)->toBe('image/jpeg')
            ->and($response->encoding)->toBe('base64')
            ->and($response->side)->toBe('front')
            ->and($response->hasErrors())->toBeFalse();
    });

    it('can decode image data', function () {
        $data = SampleResponses::previewResponse();
        $response = Preview::fromArray($data);

        expect($response->getDecodedImage())->toBe('fake-image-data');
    });
});

describe('CampaignStatistic', function () {
    it('can be created from array', function () {
        $data = SampleResponses::campaignStatisticsResponse();
        $response = CampaignStatistic::fromArray($data);

        expect($response->campaignKey)->toBe('test-campaign-123')
            ->and($response->quota)->toBe(1000)
            ->and($response->sendPostcards)->toBe(250)
            ->and($response->freeToSendPostcards)->toBe(750);
    });

    it('calculates remaining quota correctly', function () {
        $data = SampleResponses::campaignStatisticsResponse();
        $response = CampaignStatistic::fromArray($data);

        expect($response->getRemainingQuota())->toBe(750);
    });

    it('calculates usage percentage correctly', function () {
        $data = SampleResponses::campaignStatisticsResponse();
        $response = CampaignStatistic::fromArray($data);

        expect($response->getUsagePercentage())->toBe(25.0);
    });

    it('handles zero quota correctly', function () {
        $data = [
            'campaignKey' => 'test-campaign',
            'quota' => 0,
            'sendPostcards' => 0,
            'freeToSendPostcards' => 0,
        ];

        $response = CampaignStatistic::fromArray($data);

        expect($response->getUsagePercentage())->toBe(0.0);
    });

    it('can be converted to array', function () {
        $data = SampleResponses::campaignStatisticsResponse();
        $response = CampaignStatistic::fromArray($data);
        $array = $response->toArray();

        expect($array)->toBe($data);
    });
});
