<?php

namespace Gigerit\PostcardApi\Tests;

use Gigerit\PostcardApi\PostcardApiServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Gigerit\\PostcardApi\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            PostcardApiServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('cache.default', 'array');

        // Set up test configuration for Swiss Post API
        config()->set('swiss-post-postcard-api-client', [
            'base_url' => 'https://test.api.example.com',
            'oauth' => [
                'auth_url' => 'https://test.auth.example.com/authorize',
                'token_url' => 'https://test.auth.example.com/token',
                'client_id' => 'test-client-id',
                'client_secret' => 'test-client-secret',
                'scope' => 'PCCAPI',
            ],
            'default_campaign' => 'test-campaign-uuid',
            'timeout' => 30,
            'retry_times' => 3,
            'retry_sleep' => 500,
            'debug' => false,
        ]);
    }
}
