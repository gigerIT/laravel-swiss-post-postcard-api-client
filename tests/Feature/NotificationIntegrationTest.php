<?php

use Gigerit\PostcardApi\Channels\PostcardChannel;
use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Gigerit\PostcardApi\DTOs\Response\DefaultResponse;
use Gigerit\PostcardApi\Exceptions\PostcardNotificationException;
use Gigerit\PostcardApi\Messages\PostcardMessage;
use Gigerit\PostcardApi\PostcardApi;
use Gigerit\PostcardApi\Services\PostcardService;
use Illuminate\Notifications\Notification;

beforeEach(function () {
    $this->postcardApi = Mockery::mock(PostcardApi::class);
    $this->postcardService = Mockery::mock(PostcardService::class);

    $this->app->instance(PostcardApi::class, $this->postcardApi);
    $this->app->instance(PostcardChannel::class, new PostcardChannel($this->postcardApi));
});

afterEach(function () {
    Mockery::close();
});

it('can be resolved from service container', function () {
    $channel = $this->app->make(PostcardChannel::class);

    expect($channel)->toBeInstanceOf(PostcardChannel::class);
});

it('is registered as notification channel', function () {
    // Check if the channel is registered
    $manager = $this->app->make('Illuminate\Notifications\ChannelManager');

    expect($manager)->toBeInstanceOf(\Illuminate\Notifications\ChannelManager::class);

    // The channel should be resolvable through the manager
    $channel = $manager->driver('postcard');
    expect($channel)->toBeInstanceOf(PostcardChannel::class);
});

it('sends notification through Laravel notification system', function () {
    // Create a test notification
    $notification = new class extends Notification
    {
        public function via($notifiable): array
        {
            return ['postcard'];
        }

        public function toPostcard($notifiable): PostcardMessage
        {
            return PostcardMessage::create('/path/to/test-image.jpg')
                ->text('Test notification message')
                ->autoApprove(true);
        }
    };

    // Create a test notifiable
    $notifiable = new class
    {
        public function getPostcardRecipientAddress(): RecipientAddress
        {
            return new RecipientAddress(
                street: 'Test Street 1',
                zip: '8000',
                city: 'Zurich',
                country: 'Switzerland',
                firstname: 'John',
                lastname: 'Doe'
            );
        }

        public function notify($notification)
        {
            app(\Illuminate\Notifications\ChannelManager::class)
                ->driver('postcard')
                ->send($this, $notification);
        }
    };

    $response = new DefaultResponse(
        cardKey: 'test-card-key',
        successMessage: 'Postcard created successfully'
    );

    // Mock API calls
    $this->postcardApi->shouldReceive('postcards')
        ->twice()
        ->andReturn($this->postcardService);

    $this->postcardService->shouldReceive('createComplete')
        ->once()
        ->andReturn($response);

    $this->postcardService->shouldReceive('approve')
        ->once()
        ->with('test-card-key')
        ->andReturn($response);

    // Send notification - should not throw any exceptions
    expect(fn () => $notifiable->notify($notification))->not->toThrow(Exception::class);
});

it('handles notification failures gracefully', function () {
    $notification = new class extends Notification
    {
        public function via($notifiable): array
        {
            return ['postcard'];
        }

        public function toPostcard($notifiable): PostcardMessage
        {
            return PostcardMessage::create('/path/to/test-image.jpg')
                ->text('Test notification message');
        }
    };

    $notifiable = new class
    {
        public function getPostcardRecipientAddress(): RecipientAddress
        {
            return new RecipientAddress(
                street: 'Test Street 1',
                zip: '8000',
                city: 'Zurich',
                country: 'Switzerland',
                firstname: 'John',
                lastname: 'Doe'
            );
        }

        public function notify($notification)
        {
            app(\Illuminate\Notifications\ChannelManager::class)
                ->driver('postcard')
                ->send($this, $notification);
        }
    };

    // Mock API failure
    $this->postcardApi->shouldReceive('postcards')
        ->once()
        ->andReturn($this->postcardService);

    $this->postcardService->shouldReceive('createComplete')
        ->once()
        ->andThrow(new \Exception('API Error'));

    expect(fn () => $notifiable->notify($notification))
        ->toThrow(PostcardNotificationException::class, 'Failed to send postcard notification');
});

it('works with notification facade', function () {
    // Create a test notifiable
    $notifiable = new class
    {
        public function getPostcardRecipientAddress(): RecipientAddress
        {
            return new RecipientAddress(
                street: 'Test Street 1',
                zip: '8000',
                city: 'Zurich',
                country: 'Switzerland',
                firstname: 'John',
                lastname: 'Doe'
            );
        }
    };

    $notification = new class extends Notification
    {
        public function via($notifiable): array
        {
            return ['postcard'];
        }

        public function toPostcard($notifiable): PostcardMessage
        {
            return PostcardMessage::create('/path/to/test-image.jpg')
                ->text('Facade notification message')
                ->autoApprove(true);
        }
    };

    $response = new DefaultResponse(
        cardKey: 'test-card-key',
        successMessage: 'Postcard created successfully'
    );

    // Mock API calls
    $this->postcardApi->shouldReceive('postcards')
        ->twice()
        ->andReturn($this->postcardService);

    $this->postcardService->shouldReceive('createComplete')
        ->once()
        ->andReturn($response);

    $this->postcardService->shouldReceive('approve')
        ->once()
        ->with('test-card-key')
        ->andReturn($response);

    // Send notification through channel manager directly
    expect(function () use ($notifiable, $notification) {
        app(\Illuminate\Notifications\ChannelManager::class)
            ->driver('postcard')
            ->send($notifiable, $notification);
    })->not->toThrow(Exception::class);
});

describe('address resolution strategies', function () {
    beforeEach(function () {
        $this->response = new DefaultResponse(
            cardKey: 'test-card-key',
            successMessage: 'Postcard created successfully'
        );
    });

    it('prioritizes explicit recipient address in message', function () {
        $explicitAddress = new RecipientAddress(
            street: 'Explicit Street 1',
            zip: '1000',
            city: 'Explicit City',
            country: 'Switzerland'
        );

        $notification = new class($explicitAddress) extends Notification
        {
            public function __construct(private RecipientAddress $address) {}

            public function via($notifiable): array
            {
                return ['postcard'];
            }

            public function toPostcard($notifiable): PostcardMessage
            {
                return PostcardMessage::create('/path/to/test-image.jpg')
                    ->to($this->address)
                    ->text('Test message');
            }
        };

        $notifiable = new class
        {
            public function getPostcardRecipientAddress(): RecipientAddress
            {
                return new RecipientAddress(
                    street: 'Should Not Use This',
                    zip: '9999',
                    city: 'Wrong City',
                    country: 'Switzerland'
                );
            }
        };

        $this->postcardApi->shouldReceive('postcards')
            ->once()
            ->andReturn($this->postcardService);

        $this->postcardService->shouldReceive('createComplete')
            ->once()
            ->withArgs(function ($recipientAddress) use ($explicitAddress) {
                return $recipientAddress === $explicitAddress;
            })
            ->andReturn($this->response);

        $channel = new PostcardChannel($this->postcardApi);
        expect(fn () => $channel->send($notifiable, $notification))->not->toThrow(Exception::class);
    });

    it('falls back to notifiable method when message has no address', function () {
        $notifiableAddress = new RecipientAddress(
            street: 'Notifiable Street 1',
            zip: '2000',
            city: 'Notifiable City',
            country: 'Switzerland'
        );

        $notification = new class extends Notification
        {
            public function via($notifiable): array
            {
                return ['postcard'];
            }

            public function toPostcard($notifiable): PostcardMessage
            {
                return PostcardMessage::create('/path/to/test-image.jpg')
                    ->text('Test message');
            }
        };

        $notifiable = new class($notifiableAddress)
        {
            public function __construct(private RecipientAddress $address) {}

            public function getPostcardRecipientAddress(): RecipientAddress
            {
                return $this->address;
            }
        };

        $this->postcardApi->shouldReceive('postcards')
            ->once()
            ->andReturn($this->postcardService);

        $this->postcardService->shouldReceive('createComplete')
            ->once()
            ->withArgs(function ($recipientAddress) use ($notifiableAddress) {
                return $recipientAddress === $notifiableAddress;
            })
            ->andReturn($this->response);

        $channel = new PostcardChannel($this->postcardApi);
        expect(fn () => $channel->send($notifiable, $notification))->not->toThrow(Exception::class);
    });

    it('builds address from array when no explicit address or method', function () {
        $notification = new class extends Notification
        {
            public function via($notifiable): array
            {
                return ['postcard'];
            }

            public function toPostcard($notifiable): PostcardMessage
            {
                return PostcardMessage::create('/path/to/test-image.jpg')
                    ->text('Test message');
            }
        };

        $notifiable = new class
        {
            public function toArray(): array
            {
                return [
                    'first_name' => 'Array',
                    'last_name' => 'User',
                    'street' => 'Array Street 1',
                    'zip' => '3000',
                    'city' => 'Array City',
                    'country' => 'Switzerland',
                ];
            }
        };

        $this->postcardApi->shouldReceive('postcards')
            ->once()
            ->andReturn($this->postcardService);

        $this->postcardService->shouldReceive('createComplete')
            ->once()
            ->withArgs(function ($recipientAddress) {
                return $recipientAddress instanceof RecipientAddress
                    && $recipientAddress->firstname === 'Array'
                    && $recipientAddress->lastname === 'User'
                    && $recipientAddress->street === 'Array Street 1';
            })
            ->andReturn($this->response);

        $channel = new PostcardChannel($this->postcardApi);
        expect(fn () => $channel->send($notifiable, $notification))->not->toThrow(Exception::class);
    });
});
