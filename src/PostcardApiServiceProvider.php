<?php

namespace Gigerit\PostcardApi;

use Gigerit\PostcardApi\Channels\PostcardChannel;
use Gigerit\PostcardApi\Connectors\SwissPostConnector;
use Gigerit\PostcardApi\Services\BrandingService;
use Gigerit\PostcardApi\Services\CampaignService;
use Gigerit\PostcardApi\Services\PostcardService;
use Illuminate\Support\Facades\Notification;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PostcardApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-swiss-post-postcard-api-client')
            ->hasConfigFile('swiss-post-postcard-api-client');
    }

    public function packageRegistered(): void
    {
        // Register the main PostcardApi class as a singleton
        $this->app->singleton(PostcardApi::class, function () {
            return new PostcardApi;
        });

        // Register the connector as a singleton
        $this->app->singleton(SwissPostConnector::class, function () {
            return new SwissPostConnector;
        });

        // Register services
        $this->app->singleton(PostcardService::class, function ($app) {
            return new PostcardService($app->make(SwissPostConnector::class));
        });

        $this->app->singleton(BrandingService::class, function ($app) {
            return new BrandingService($app->make(SwissPostConnector::class));
        });

        $this->app->singleton(CampaignService::class, function ($app) {
            return new CampaignService($app->make(SwissPostConnector::class));
        });

        // Register the postcard notification channel
        $this->app->singleton(PostcardChannel::class, function ($app) {
            return new PostcardChannel($app->make(PostcardApi::class));
        });
    }

    public function packageBooted(): void
    {
        // Register the notification channel
        Notification::extend('postcard', function ($app) {
            return $app->make(PostcardChannel::class);
        });
    }
}
