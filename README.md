# Laravel Swiss Post Postcard API Client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gigerit/laravel-swiss-post-postcard-api-client.svg?style=flat-square)](https://packagist.org/packages/gigerit/laravel-swiss-post-postcard-api-client)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/gigerit/laravel-swiss-post-postcard-api-client/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/gigerit/laravel-swiss-post-postcard-api-client/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/gigerit/laravel-swiss-post-postcard-api-client/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/gigerit/laravel-swiss-post-postcard-api-client/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/gigerit/laravel-swiss-post-postcard-api-client.svg?style=flat-square)](https://packagist.org/packages/gigerit/laravel-swiss-post-postcard-api-client)

A Laravel package for sending physical postcards through the Swiss Post Postcard API. This package provides a complete, type-safe wrapper around the Swiss Post Postcard API, allowing you to programmatically send real postcards with images, addresses, branding, and QR codes.

**Features:**

-   🚀 Complete API coverage for all Swiss Post Postcard endpoints
-   🔐 OAuth2 authentication with automatic token management
-   ✅ Client-side validation for addresses, text, and image dimensions
-   🎨 Support for branding (text, images, QR codes, custom stamps)
-   📧 Type-safe DTOs for all request and response data
-   🛡️ Comprehensive error handling with descriptive error codes
-   📱 Laravel facades and dependency injection support
-   🧪 Built with Saloon HTTP client and OAuth2 for robust API communication

## Requirements

-   PHP 8.3 or higher
-   Laravel 10.0, 11.0, or 12.0
-   Swiss Post Postcard API credentials (obtained through contract with Swiss Post)

## Installation

Install the package via Composer:

```bash
composer require gigerit/laravel-swiss-post-postcard-api-client
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="swiss-post-postcard-api-client-config"
```

## Configuration

Add your Swiss Post API credentials to your `.env` file:

```env
SWISS_POST_POSTCARD_API_BASE_URL=https://apiint.post.ch/pcc/
SWISS_POST_POSTCARD_API_AUTH_URL=https://apiint.post.ch/OAuth/authorization
SWISS_POST_POSTCARD_API_TOKEN_URL=https://apiint.post.ch/OAuth/token
SWISS_POST_POSTCARD_API_CLIENT_ID=your_client_id
SWISS_POST_POSTCARD_API_CLIENT_SECRET=your_client_secret
SWISS_POST_POSTCARD_API_SCOPE=PCCAPI
SWISS_POST_POSTCARD_API_DEFAULT_CAMPAIGN=your_campaign_uuid
```

> **Note:** The URLs shown above are for the integration environment. Swiss Post will provide you with production URLs and credentials upon contract signing.

## Quick Start

### Using Dependency Injection

```php
use Gigerit\PostcardApi\PostcardApi;
use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;

class PostcardController extends Controller
{
    public function __construct(private PostcardApi $postcardApi) {}

    public function sendPostcard()
    {
        // Create recipient address
        $recipient = new RecipientAddress(
            street: 'Musterstrasse',
            zip: '8000',
            city: 'Zürich',
            country: 'CH',
            firstname: 'John',
            lastname: 'Doe',
            houseNr: '123'
        );

        // Send postcard
        $result = $this->postcardApi->postcards()->createComplete(
            recipientAddress: $recipient,
            imagePath: storage_path('postcards/my-image.jpg'),
            senderText: 'Hello from Laravel!'
        );

        return response()->json(['cardKey' => $result->cardKey]);
    }
}
```

### Using Facades

```php
use Gigerit\PostcardApi\Facades\PostcardApi;
use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;

// Check campaign quota
$stats = PostcardApi::campaigns()->getDefaultCampaignStatistics();
if ($stats->freeToSendPostcards === 0) {
    throw new Exception('No postcards remaining in campaign');
}

// Create and send postcard
$recipient = new RecipientAddress(/* ... */);
$result = PostcardApi::postcards()->createComplete(
    recipientAddress: $recipient,
    imagePath: 'path/to/image.jpg'
);
```

### Manual Instantiation

```php
use Gigerit\PostcardApi\PostcardApi;
use Gigerit\PostcardApi\Connectors\SwissPostConnector;

// Auto-authenticate using configured OAuth2 credentials
$api = new PostcardApi();

// Or provide specific access token
$api = new PostcardApi('your_access_token_here');

// Using Saloon's OAuth2 directly
$connector = new SwissPostConnector();
$authenticator = $connector->getAccessToken(); // Uses client credentials grant
$connector->authenticate($authenticator);
```

## Core Concepts

### Postcard Workflow

1. **Check quota** - Verify campaign has remaining postcards
2. **Create postcard** - Initialize with recipient address
3. **Upload content** - Add image, sender text, addresses
4. **Add branding** - Optional logos, QR codes, custom stamps
5. **Approve** - Submit for printing and sending

### Image Requirements

| Type           | Dimensions   | Format   | Purpose             |
| -------------- | ------------ | -------- | ------------------- |
| Front Image    | 1819×1311 px | JPEG/PNG | Main postcard image |
| Branding Image | 777×295 px   | JPEG/PNG | Company branding    |
| Stamp          | 343×248 px   | JPEG/PNG | Custom stamp        |

All images should be RGB color mode at 300 DPI for optimal print quality.

### Address Validation

The package validates addresses according to Swiss Post requirements:

-   Required fields: street, ZIP, city, country (for recipients)
-   Name requirements: firstname/lastname OR company name
-   Text length limits enforced
-   Character encoding validation (CP850 compatibility)

## API Services

The package is organized into three main services:

### PostcardService

```php
$postcards = $api->postcards();

// Create postcards
$result = $postcards->create($campaignKey, $postcard);
$result = $postcards->createComplete($recipient, $imagePath, $sender, $text);

// Upload content
$postcards->uploadImage($cardKey, $imagePath);
$postcards->uploadSenderText($cardKey, $text);
$postcards->uploadSenderAddress($cardKey, $senderAddress);
$postcards->uploadRecipientAddress($cardKey, $recipientAddress);

// Manage postcards
$postcards->approve($cardKey);
$state = $postcards->getState($cardKey);
$frontPreview = $postcards->getPreviewFront($cardKey);
$backPreview = $postcards->getPreviewBack($cardKey);
```

### BrandingService

```php
$branding = $api->branding();

// Text branding
$branding->addSimpleText($cardKey, 'Your Company', '#FF0000', '#FFFFFF');

// QR code branding
$branding->addSimpleQRCode($cardKey, 'https://yoursite.com', 'Visit us!');

// Image branding
$branding->uploadImage($cardKey, 'path/to/logo.jpg');
$branding->uploadStamp($cardKey, 'path/to/stamp.jpg');
```

### CampaignService

```php
$campaigns = $api->campaigns();

// Get statistics
$stats = $campaigns->getDefaultCampaignStatistics();
$stats = $campaigns->getStatistics($campaignKey);

// Check quota
$hasQuota = $campaigns->hasRemainingQuota($campaignKey);
$remaining = $campaigns->getRemainingQuota($campaignKey);
```

## Error Handling

The package provides comprehensive error handling:

```php
use Gigerit\PostcardApi\Exceptions\SwissPostApiException;
use Gigerit\PostcardApi\Enums\ErrorCode;

try {
    $result = $api->postcards()->create();

    // Check for warnings
    if ($result->hasWarnings()) {
        foreach ($result->getWarningMessages() as $warning) {
            Log::warning("Postcard warning: {$warning}");
        }
    }

} catch (SwissPostApiException $e) {
    // API errors (quota exceeded, invalid data, etc.)
    Log::error("Swiss Post API error: {$e->getMessage()}");

} catch (\InvalidArgumentException $e) {
    // Validation errors (invalid image dimensions, text too long, etc.)
    Log::error("Validation error: {$e->getMessage()}");
}

// Error codes are available as enums
$errorCode = ErrorCode::CAMPAIGN_QUOTA_EXCEEDED;
echo $errorCode->getDescription(); // "Campaign quota exceeded"
```

## Validation

Client-side validation helps catch errors before API calls:

```php
use Gigerit\PostcardApi\Validation\PostcardValidator;
use Gigerit\PostcardApi\Enums\ImageDimensions;

// Validate addresses
$errors = PostcardValidator::validateRecipientAddress($recipient);

// Validate text
$errors = PostcardValidator::validateSenderText($text);

// Validate images
$errors = PostcardValidator::validateImageDimensions(
    '/path/to/image.jpg',
    ImageDimensions::FRONT_IMAGE
);

// Disable validation if needed
$api->postcards()->uploadImage($cardKey, $path, null, false); // Skip validation
```

## Advanced Usage

For more detailed examples and advanced usage patterns, see [USAGE_EXAMPLES.md](USAGE_EXAMPLES.md).

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Manu](https://github.com/gigerIT)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
