<?php

use Gigerit\PostcardApi\Channels\PostcardChannel;
use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Gigerit\PostcardApi\DTOs\Address\SenderAddress;
use Gigerit\PostcardApi\DTOs\Response\DefaultResponse;
use Gigerit\PostcardApi\Exceptions\PostcardNotificationException;
use Gigerit\PostcardApi\Messages\PostcardMessage;
use Gigerit\PostcardApi\PostcardApi;
use Gigerit\PostcardApi\Services\PostcardService;
use Illuminate\Notifications\Notification;

afterEach(function () {
    Mockery::close();
});

it('sends postcard notification successfully', function () {
    // Mock dependencies
    $postcardApi = Mockery::mock(PostcardApi::class);
    $postcardService = Mockery::mock(PostcardService::class);
    $channel = new PostcardChannel($postcardApi);

    // Create test data
    $recipientAddress = new RecipientAddress(
        street: 'Test Street 1',
        zip: '8000',
        city: 'Zurich',
        country: 'Switzerland',
        firstname: 'John',
        lastname: 'Doe'
    );

    $senderAddress = new SenderAddress(
        street: 'Sender Street 1',
        zip: '3000',
        city: 'Bern',
        firstname: 'Jane',
        lastname: 'Smith'
    );

    $message = PostcardMessage::create('/path/to/image.jpg')
        ->to($recipientAddress)
        ->from($senderAddress)
        ->text('Hello from Laravel!')
        ->campaign('test-campaign')
        ->autoApprove(true);

    $response = new DefaultResponse(
        cardKey: 'test-card-key',
        successMessage: 'Postcard created successfully'
    );

    // Create notification
    $notification = new class($message) extends Notification
    {
        public function __construct(private PostcardMessage $message) {}

        public function toPostcard($notifiable)
        {
            return $this->message;
        }
    };

    // Mock API calls
    $postcardApi->shouldReceive('postcards')
        ->twice()
        ->andReturn($postcardService);

    $postcardService->shouldReceive('createComplete')
        ->once()
        ->with(
            $recipientAddress,
            '/path/to/image.jpg',
            $senderAddress,
            'Hello from Laravel!',
            'test-campaign'
        )
        ->andReturn($response);

    $postcardService->shouldReceive('approve')
        ->once()
        ->with('test-card-key')
        ->andReturn($response);

    // Mock notifiable
    $notifiable = new class
    {
        //
    };

    // Test the channel - should not throw any exceptions
    expect(fn () => $channel->send($notifiable, $notification))->not->toThrow(Exception::class);
});

it('throws exception when notification does not have toPostcard method', function () {
    $postcardApi = Mockery::mock(PostcardApi::class);
    $channel = new PostcardChannel($postcardApi);

    $notification = new class extends Notification
    {
        // Missing toPostcard method
    };

    $notifiable = new class
    {
        //
    };

    expect(fn () => $channel->send($notifiable, $notification))
        ->toThrow(PostcardNotificationException::class, 'Notification must implement toPostcard method.');
});

it('throws exception when notification does not return postcard message', function () {
    $postcardApi = Mockery::mock(PostcardApi::class);
    $channel = new PostcardChannel($postcardApi);

    $notification = new class extends Notification
    {
        public function toPostcard($notifiable)
        {
            return 'invalid-return-type';
        }
    };

    $notifiable = new class
    {
        //
    };

    expect(fn () => $channel->send($notifiable, $notification))
        ->toThrow(PostcardNotificationException::class, 'Notification must return a PostcardMessage instance from toPostcard method.');
});

it('gets recipient address from notifiable method', function () {
    $postcardApi = Mockery::mock(PostcardApi::class);
    $postcardService = Mockery::mock(PostcardService::class);
    $channel = new PostcardChannel($postcardApi);

    $recipientAddress = new RecipientAddress(
        street: 'Test Street 1',
        zip: '8000',
        city: 'Zurich',
        country: 'Switzerland',
        firstname: 'John',
        lastname: 'Doe'
    );

    $message = PostcardMessage::create('/path/to/image.jpg');

    $response = new DefaultResponse(
        cardKey: 'test-card-key',
        successMessage: 'Postcard created successfully'
    );

    $notification = new class($message) extends Notification
    {
        public function __construct(private PostcardMessage $message) {}

        public function toPostcard($notifiable)
        {
            return $this->message;
        }
    };

    $notifiable = new class($recipientAddress)
    {
        public function __construct(private RecipientAddress $address) {}

        public function getPostcardRecipientAddress(): RecipientAddress
        {
            return $this->address;
        }
    };

    $postcardApi->shouldReceive('postcards')
        ->once()
        ->andReturn($postcardService);

    $postcardService->shouldReceive('createComplete')
        ->once()
        ->with(
            $recipientAddress,
            '/path/to/image.jpg',
            null,
            null,
            null
        )
        ->andReturn($response);

    expect(fn () => $channel->send($notifiable, $notification))->not->toThrow(Exception::class);
});

it('builds recipient address from array data', function () {
    $postcardApi = Mockery::mock(PostcardApi::class);
    $postcardService = Mockery::mock(PostcardService::class);
    $channel = new PostcardChannel($postcardApi);

    $message = PostcardMessage::create('/path/to/image.jpg');

    $response = new DefaultResponse(
        cardKey: 'test-card-key',
        successMessage: 'Postcard created successfully'
    );

    $notification = new class($message) extends Notification
    {
        public function __construct(private PostcardMessage $message) {}

        public function toPostcard($notifiable)
        {
            return $this->message;
        }
    };

    $notifiable = new class
    {
        public function toArray(): array
        {
            return [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'street' => 'Test Street 1',
                'zip' => '8000',
                'city' => 'Zurich',
                'country' => 'Switzerland',
            ];
        }
    };

    $postcardApi->shouldReceive('postcards')
        ->once()
        ->andReturn($postcardService);

    $postcardService->shouldReceive('createComplete')
        ->once()
        ->withArgs(function ($recipientAddress, $imagePath, $senderAddress, $senderText, $campaignKey) {
            return $recipientAddress instanceof RecipientAddress
                && $recipientAddress->firstname === 'John'
                && $recipientAddress->lastname === 'Doe'
                && $recipientAddress->street === 'Test Street 1'
                && $recipientAddress->zip === '8000'
                && $recipientAddress->city === 'Zurich'
                && $recipientAddress->country === 'Switzerland'
                && $imagePath === '/path/to/image.jpg';
        })
        ->andReturn($response);

    expect(fn () => $channel->send($notifiable, $notification))->not->toThrow(Exception::class);
});

it('throws exception when unable to determine recipient address', function () {
    $postcardApi = Mockery::mock(PostcardApi::class);
    $channel = new PostcardChannel($postcardApi);

    $message = PostcardMessage::create('/path/to/image.jpg');

    $notification = new class($message) extends Notification
    {
        public function __construct(private PostcardMessage $message) {}

        public function toPostcard($notifiable)
        {
            return $this->message;
        }
    };

    $notifiable = new class
    {
        //
    };

    expect(fn () => $channel->send($notifiable, $notification))
        ->toThrow(PostcardNotificationException::class, 'Unable to determine recipient address');
});

it('handles missing required address fields gracefully', function () {
    $postcardApi = Mockery::mock(PostcardApi::class);
    $channel = new PostcardChannel($postcardApi);

    $message = PostcardMessage::create('/path/to/image.jpg');

    $notification = new class($message) extends Notification
    {
        public function __construct(private PostcardMessage $message) {}

        public function toPostcard($notifiable)
        {
            return $this->message;
        }
    };

    $notifiable = new class
    {
        public function toArray(): array
        {
            return [
                'first_name' => 'John',
                'last_name' => 'Doe',
                // Missing required address fields
            ];
        }
    };

    expect(fn () => $channel->send($notifiable, $notification))
        ->toThrow(PostcardNotificationException::class, 'Missing required address field');
});
