<?php

namespace Gigerit\PostcardApi;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Gigerit\PostcardApi\Commands\PostcardApiCommand;

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
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_swiss_post_postcard_api_client_table')
            ->hasCommand(PostcardApiCommand::class);
    }
}
