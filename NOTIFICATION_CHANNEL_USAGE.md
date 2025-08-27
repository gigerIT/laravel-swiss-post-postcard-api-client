# Laravel Notification Channel for Swiss Post Postcard API

This package provides a Laravel notification channel for sending postcards using the Swiss Post Postcard API.

## Installation

The notification channel is automatically registered when you install the package.

## Usage

### 1. Create a Notification

Create a notification class that implements the `toPostcard` method:

```php
<?php

namespace App\Notifications;

use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Gigerit\PostcardApi\DTOs\Address\SenderAddress;
use Gigerit\PostcardApi\Messages\PostcardMessage;
use Illuminate\Notifications\Notification;

class WelcomePostcard extends Notification
{
    public function __construct(
        private string $imagePath,
        private string $welcomeMessage
    ) {}

    public function via($notifiable): array
    {
        return ['postcard'];
    }

    public function toPostcard($notifiable): PostcardMessage
    {
        // Option 1: Let the channel determine recipient address from notifiable
        return PostcardMessage::create($this->imagePath)
            ->text($this->welcomeMessage)
            ->campaign('welcome-campaign')
            ->autoApprove(true);

        // Option 2: Explicitly set recipient address
        return PostcardMessage::create($this->imagePath)
            ->to(new RecipientAddress(
                street: $notifiable->street,
                zip: $notifiable->zip,
                city: $notifiable->city,
                country: $notifiable->country,
                firstname: $notifiable->first_name,
                lastname: $notifiable->last_name
            ))
            ->from(new SenderAddress(
                street: 'Your Company Street 1',
                zip: '8000',
                city: 'Zurich',
                company: 'Your Company'
            ))
            ->text($this->welcomeMessage)
            ->campaign('welcome-campaign')
            ->autoApprove(true);
    }
}
```

### 2. Prepare Your Notifiable Model

#### Option A: Implement `getPostcardRecipientAddress()` method

```php
<?php

namespace App\Models;

use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'street',
        'house_number',
        'zip',
        'city',
        'country',
    ];

    public function getPostcardRecipientAddress(): RecipientAddress
    {
        return new RecipientAddress(
            street: $this->street,
            zip: $this->zip,
            city: $this->city,
            country: $this->country,
            firstname: $this->first_name,
            lastname: $this->last_name,
            houseNr: $this->house_number
        );
    }
}
```

#### Option B: Use standard attribute names

The channel can automatically map common address fields from your model's `toArray()` method:

```php
// The channel will automatically map these fields:
[
    'firstname' => ['firstname', 'first_name', 'name'],
    'lastname' => ['lastname', 'last_name', 'surname'],
    'street' => ['street', 'address', 'street_address'],
    'zip' => ['zip', 'postal_code', 'postcode', 'zipcode'],
    'city' => ['city', 'town'],
    'country' => ['country', 'country_code'],
    'company' => ['company', 'company_name', 'organization'],
    'houseNr' => ['house_nr', 'house_number', 'number'],
]
```

### 3. Send the Notification

```php
<?php

use App\Models\User;
use App\Notifications\WelcomePostcard;

$user = User::find(1);

$user->notify(new WelcomePostcard(
    imagePath: storage_path('postcards/welcome-image.jpg'),
    welcomeMessage: 'Welcome to our service!'
));
```

### 4. Using with Notification Facade

```php
<?php

use App\Models\User;
use App\Notifications\WelcomePostcard;
use Illuminate\Support\Facades\Notification;

$users = User::whereNotNull('street')->get();

Notification::send($users, new WelcomePostcard(
    imagePath: storage_path('postcards/welcome-image.jpg'),
    welcomeMessage: 'Welcome to our service!'
));
```

## PostcardMessage Methods

The `PostcardMessage` class provides a fluent interface for building postcard notifications:

### Required Methods

- `PostcardMessage::create(string $imagePath)` - Create a new postcard message with an image

### Optional Methods

- `->to(RecipientAddress $address)` - Set the recipient address
- `->from(SenderAddress $address)` - Set the sender address  
- `->text(string $text)` - Set the message text on the back of the postcard
- `->campaign(string $campaignKey)` - Set the campaign key (overrides default)
- `->autoApprove(bool $autoApprove = true)` - Enable/disable automatic approval

## Configuration

Make sure your `.env` file contains the required Swiss Post API configuration:

```env
SWISS_POST_POSTCARD_API_CLIENT_ID=your_client_id
SWISS_POST_POSTCARD_API_CLIENT_SECRET=your_client_secret
SWISS_POST_POSTCARD_API_DEFAULT_CAMPAIGN=your_default_campaign_key
```

## Error Handling

The channel throws `PostcardNotificationException` for various error conditions:

- Invalid notification return type (must return `PostcardMessage`)
- Unable to determine recipient address
- API errors during postcard creation
- Image validation failures

```php
<?php

use Gigerit\PostcardApi\Exceptions\PostcardNotificationException;

try {
    $user->notify(new WelcomePostcard($imagePath, $message));
} catch (PostcardNotificationException $e) {
    // Handle the error
    Log::error('Failed to send postcard notification: ' . $e->getMessage());
}
```

## Advanced Usage

### Custom Recipient Address Resolution

You can customize how the channel resolves recipient addresses by extending the `PostcardChannel` class:

```php
<?php

namespace App\Channels;

use Gigerit\PostcardApi\Channels\PostcardChannel as BasePostcardChannel;
use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Gigerit\PostcardApi\Messages\PostcardMessage;

class CustomPostcardChannel extends BasePostcardChannel
{
    protected function getRecipientAddress(PostcardMessage $message, object $notifiable): RecipientAddress
    {
        // Your custom logic here
        return parent::getRecipientAddress($message, $notifiable);
    }
}
```

Then register your custom channel in a service provider:

```php
<?php

use Illuminate\Support\Facades\Notification;

public function boot(): void
{
    Notification::extend('postcard', function ($app) {
        return $app->make(CustomPostcardChannel::class);
    });
}
```
