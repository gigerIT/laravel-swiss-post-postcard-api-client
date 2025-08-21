<?php

use Gigerit\PostcardApi\PostcardApi;

it('can instantiate the main API class', function () {
    $api = new PostcardApi;

    expect($api)->toBeInstanceOf(PostcardApi::class);
});

it('has all required services', function () {
    $api = new PostcardApi;

    expect($api->postcards())->toBeInstanceOf(\Gigerit\PostcardApi\Services\PostcardService::class)
        ->and($api->branding())->toBeInstanceOf(\Gigerit\PostcardApi\Services\BrandingService::class)
        ->and($api->campaigns())->toBeInstanceOf(\Gigerit\PostcardApi\Services\CampaignService::class);
});
