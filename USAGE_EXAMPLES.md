# Swiss Post Postcard API - Usage Examples

This document provides comprehensive examples of how to use the Laravel Swiss Post Postcard API client.

## Configuration

First, configure your environment variables in `.env`:

```env
SWISS_POST_POSTCARD_API_BASE_URL=https://apiint.post.ch/pcc/
SWISS_POST_POSTCARD_API_AUTH_URL=https://apiint.post.ch/OAuth/authorization
SWISS_POST_POSTCARD_API_TOKEN_URL=https://apiint.post.ch/OAuth/token
SWISS_POST_POSTCARD_API_CLIENT_ID=your_client_id
SWISS_POST_POSTCARD_API_CLIENT_SECRET=your_client_secret
SWISS_POST_POSTCARD_API_SCOPE=PCCAPI
SWISS_POST_POSTCARD_API_DEFAULT_CAMPAIGN=your_campaign_uuid
```

## Basic Usage

### Initialize the API Client

```php
use Gigerit\PostcardApi\PostcardApi;

// Auto-authenticate using OAuth2
$api = new PostcardApi();

// Or with manual access token
$api = new PostcardApi('your_access_token_here');
```

### Using Facades

```php
use Gigerit\PostcardApi\Facades\PostcardApi;

// Access services through facade
$postcards = PostcardApi::postcards();
$branding = PostcardApi::branding();
$campaigns = PostcardApi::campaigns();
```

## Postcards

### Create a Simple Postcard

```php
use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Gigerit\PostcardApi\DTOs\Address\SenderAddress;

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

// Create sender address (optional if configured in campaign)
$sender = new SenderAddress(
    street: 'Absenderstrasse',
    zip: '3000',
    city: 'Bern',
    firstname: 'Jane',
    lastname: 'Smith',
    houseNr: '456'
);

// Create the postcard
$result = $api->postcards()->createComplete(
    recipientAddress: $recipient,
    imagePath: '/path/to/image.jpg',
    senderAddress: $sender,
    senderText: 'Hello from Switzerland!',
    campaignKey: null, // uses default campaign
    filename: 'postcard_image.jpg'
);

echo "Postcard created with key: {$result->cardKey}";
```

### Step-by-Step Postcard Creation

```php
// 1. Create postcard
$postcard = new \Gigerit\PostcardApi\DTOs\Postcard\Postcard(
    recipientAddress: $recipient,
    senderAddress: $sender
);

$createResult = $api->postcards()->create(null, $postcard);
$cardKey = $createResult->cardKey;

// 2. Upload image
$api->postcards()->uploadImage($cardKey, '/path/to/image.jpg');

// 3. Add sender text
$api->postcards()->uploadSenderText($cardKey, 'Hello from Switzerland!');

// 4. Get state
$state = $api->postcards()->getState($cardKey);
echo "Postcard state: {$state->state->state}";

// 5. Approve for sending
$api->postcards()->approve($cardKey);
```

### Get Previews

```php
// Get front preview
$frontPreview = $api->postcards()->getPreviewFront($cardKey);
file_put_contents('front_preview.jpg', $frontPreview->getDecodedImage());

// Get back preview
$backPreview = $api->postcards()->getPreviewBack($cardKey);
file_put_contents('back_preview.jpg', $backPreview->getDecodedImage());
```

## Branding

### Add Text Branding

```php
use Gigerit\PostcardApi\DTOs\Branding\BrandingText;

$brandingText = new BrandingText(
    text: 'Your Company Name',
    blockColor: '#FF0000',
    textColor: '#FFFFFF'
);

$api->branding()->uploadText($cardKey, $brandingText);

// Or use the simple method
$api->branding()->addSimpleText(
    cardKey: $cardKey,
    text: 'Your Company Name',
    blockColor: '#FF0000',
    textColor: '#FFFFFF'
);
```

### Add QR Code Branding

```php
use Gigerit\PostcardApi\DTOs\Branding\BrandingQRCode;

$qrCode = new BrandingQRCode(
    encodedText: 'https://yourwebsite.com',
    accompanyingText: 'Visit our website',
    blockColor: '#000000',
    textColor: '#FFFFFF'
);

$api->branding()->uploadQRCode($cardKey, $qrCode);

// Or use the simple method
$api->branding()->addSimpleQRCode(
    cardKey: $cardKey,
    encodedText: 'https://yourwebsite.com',
    accompanyingText: 'Visit our website'
);
```

### Add Image Branding

```php
// Upload branding image (777x295 pixels)
$api->branding()->uploadImage($cardKey, '/path/to/branding_image.jpg');

// Upload custom stamp (343x248 pixels)
$api->branding()->uploadStamp($cardKey, '/path/to/stamp.jpg');
```

## Campaign Management

### Check Campaign Statistics

```php
// Get default campaign statistics
$stats = $api->campaigns()->getDefaultCampaignStatistics();

echo "Campaign: {$stats->campaignKey}\n";
echo "Quota: {$stats->quota}\n";
echo "Sent: {$stats->sendPostcards}\n";
echo "Remaining: {$stats->freeToSendPostcards}\n";
echo "Usage: {$stats->getUsagePercentage()}%\n";

// Check if quota is available
if ($api->campaigns()->hasRemainingQuota($campaignKey)) {
    echo "You can send more postcards!";
} else {
    echo "Campaign quota exceeded!";
}
```

### Get Specific Campaign Statistics

```php
$stats = $api->campaigns()->getStatistics('your-campaign-uuid');
$remaining = $api->campaigns()->getRemainingQuota('your-campaign-uuid');
```

## Validation

### Disable Validation (if needed)

```php
// Disable validation for specific operations
$api->postcards()->uploadImage($cardKey, '/path/to/image.jpg', null, false);
$api->postcards()->uploadSenderText($cardKey, 'Text', false);
$api->postcards()->uploadSenderAddress($cardKey, $sender, false);
$api->branding()->uploadText($cardKey, $brandingText, false);
```

### Manual Validation

```php
use Gigerit\PostcardApi\Validation\PostcardValidator;
use Gigerit\PostcardApi\Enums\ImageDimensions;

// Validate addresses
$senderErrors = PostcardValidator::validateSenderAddress($sender);
$recipientErrors = PostcardValidator::validateRecipientAddress($recipient);

// Validate text
$textErrors = PostcardValidator::validateSenderText('Your text here');

// Validate image dimensions
$imageErrors = PostcardValidator::validateImageDimensions(
    '/path/to/image.jpg',
    ImageDimensions::FRONT_IMAGE
);

if (!empty($senderErrors)) {
    echo "Sender address errors: " . implode(', ', $senderErrors);
}
```

## Error Handling

### Handling API Errors

```php
use Gigerit\PostcardApi\Exceptions\SwissPostApiException;

try {
    $result = $api->postcards()->create();

    // Check for warnings
    if ($result->hasWarnings()) {
        foreach ($result->getWarningMessages() as $warning) {
            echo "Warning: {$warning}\n";
        }
    }

} catch (SwissPostApiException $e) {
    echo "API Error: {$e->getMessage()}";
} catch (\InvalidArgumentException $e) {
    echo "Validation Error: {$e->getMessage()}";
}
```

### Understanding Error Codes

```php
use Gigerit\PostcardApi\Enums\ErrorCode;

// Get error descriptions
$errorCode = ErrorCode::CAMPAIGN_QUOTA_EXCEEDED;
echo $errorCode->getDescription(); // "Campaign quota exceeded"

// Check if it's an error or warning
if ($errorCode->isError()) {
    echo "This is an error";
} else {
    echo "This is a warning";
}
```

## Advanced Usage

### Custom Image Dimensions

```php
use Gigerit\PostcardApi\Enums\ImageDimensions;

// Get required dimensions
$frontDimensions = ImageDimensions::getFrontImageDimensions();
echo "Front image needs: {$frontDimensions['width']}x{$frontDimensions['height']} pixels";

$stampDimensions = ImageDimensions::getStampImageDimensions();
echo "Stamp needs: {$stampDimensions['width']}x{$stampDimensions['height']} pixels";
```

### Working with Raw Connector

```php
// Access the underlying Saloon connector for custom requests
$connector = $api->connector();

// Make custom requests
$response = $connector->send(new CustomRequest());
```

### OAuth2 Token Management

```php
use Gigerit\PostcardApi\Services\OAuth2Service;
use Gigerit\PostcardApi\Connectors\SwissPostConnector;

// Using the OAuth2Service
$oauth2 = new OAuth2Service();

// Get a fresh token
$token = $oauth2->getAccessToken();

// Get the full authenticator object (includes expiry, refresh token, etc.)
$authenticator = $oauth2->getAuthenticator();

// Clear cached token
$oauth2->clearToken();

// Use specific token
$api = new PostcardApi($token);

// Using the connector directly with Saloon's OAuth2
$connector = new SwissPostConnector();
$authenticator = $connector->getAccessToken();
$connector->authenticate($authenticator);
```

## Tips and Best Practices

1. **Always check campaign quota** before creating postcards:

    ```php
    if (!$api->campaigns()->hasRemainingQuota($campaignKey)) {
        throw new \Exception('No more postcards available in campaign');
    }
    ```

2. **Handle image dimensions properly**:

    - Front image: 1819×1311 pixels (300 DPI)
    - Stamp: 343×248 pixels
    - Branding image: 777×295 pixels

3. **Use UTF-8 encoding** compatible with CP850:

    ```php
    $text = "Hello World!"; // Good
    $text = "Hello 世界!"; // May cause encoding warnings
    ```

4. **Check for warnings** in responses:

    ```php
    if ($result->hasWarnings()) {
        // Log warnings but continue
        Log::warning('Postcard warnings', $result->getWarningMessages());
    }
    ```

5. **Approve postcards within 60 minutes** or they will be deleted automatically.

## Image Requirements Summary

| Image Type     | Dimensions   | Purpose             |
| -------------- | ------------ | ------------------- |
| Front Image    | 1819×1311 px | Main postcard image |
| Stamp          | 343×248 px   | Custom stamp        |
| Branding Image | 777×295 px   | Company branding    |

All images should be in JPEG or PNG format with RGB color mode (CMYK not supported).
