# Swiss Post Postcard API Test Interface

This is a test application built with Laravel Workbench to test and demonstrate the Swiss Post Postcard API client functionality.

## Setup

1. **Configure your API credentials**
   Copy your Swiss Post API credentials to the main project's `.env` file:
   ```bash
   # In the main project root (not workbench)
   SWISS_POST_POSTCARD_API_BASE_URL=https://apiint.post.ch/pcc/
   SWISS_POST_POSTCARD_API_AUTH_URL=https://apiint.post.ch/OAuth/authorization
   SWISS_POST_POSTCARD_API_TOKEN_URL=https://apiint.post.ch/OAuth/token
   SWISS_POST_POSTCARD_API_CLIENT_ID=your_client_id
   SWISS_POST_POSTCARD_API_CLIENT_SECRET=your_client_secret
   SWISS_POST_POSTCARD_API_SCOPE=PCCAPI
   SWISS_POST_POSTCARD_API_DEFAULT_CAMPAIGN=your_campaign_uuid
   ```

2. **Start the workbench server**
   ```bash
   # From the main project root
   ./vendor/bin/testbench serve
   # Or if you have the testbench package globally:
   testbench serve
   ```

3. **Access the test interface**
   Open your browser to `http://localhost:8000`

## Features

### Quick Send Tab
- **One-step postcard creation**: Create and send a complete postcard with image, text, and addressing in a single operation
- **Simple form interface**: Fill in recipient details, upload an image, and add your message
- **Immediate feedback**: Get instant success/error responses with detailed information

### Step by Step Tab
- **Manual workflow**: Follow the complete postcard creation workflow step by step
- **Create postcard**: Initialize a postcard with recipient and sender addresses
- **Upload content**: Add images and text separately
- **Preview**: View front and back previews before sending
- **Approve**: Final approval to send the postcard

### Validation Tab
- **Address validation**: Test address data against Swiss Post requirements
- **Text validation**: Check sender text for encoding and length requirements
- **Real-time feedback**: Immediate validation results with detailed error messages

### Branding Tab
- **Text branding**: Add company text with custom colors
- **QR code branding**: Add QR codes with accompanying text
- **Image branding**: Upload custom branding images and stamps
- **Requirements checking**: Automatic validation of branding image dimensions

### Campaign Management
- **Quota checking**: View campaign statistics and remaining quota
- **Usage monitoring**: Track how many postcards have been sent vs. available quota
- **Campaign switching**: Test with different campaigns if available

## API Testing Features

The interface provides comprehensive testing for all major API endpoints:

- **Campaign Statistics**: `/api/campaigns/statistics`
- **Postcard Creation**: `/api/postcards/create`
- **Content Upload**: `/api/postcards/{cardKey}/image`, `/api/postcards/{cardKey}/text`
- **Branding**: `/api/postcards/{cardKey}/branding/*`
- **Preview Generation**: `/api/postcards/{cardKey}/preview/front|back`
- **Postcard Approval**: `/api/postcards/{cardKey}/approve`
- **State Checking**: `/api/postcards/{cardKey}/state`

## Image Requirements

The interface automatically validates image dimensions:

| Image Type | Required Dimensions | Purpose |
|------------|-------------------|---------|
| Front Image | 1819×1311 pixels | Main postcard image |
| Branding Image | 777×295 pixels | Company branding |
| Custom Stamp | 343×248 pixels | Custom stamp design |

All images should be JPEG or PNG format, RGB color mode.

## Error Handling

The interface provides detailed error handling for:
- **API errors**: Swiss Post API response errors with error codes
- **Validation errors**: Client-side validation failures
- **Network errors**: Connection and timeout issues
- **File upload errors**: Image format and size validation

## Development Notes

- Built with **Alpine.js** for reactive frontend functionality
- Uses **Tailwind CSS** for modern, responsive styling
- **CSRF protection** enabled for all API calls
- **File upload** handling with temporary storage
- **Preview image** storage and display
- **Real-time validation** feedback

## Troubleshooting

### Common Issues

1. **"Campaign quota exceeded"**
   - Check your campaign statistics in the header
   - Contact Swiss Post to increase quota if needed

2. **"Invalid image dimensions"**
   - Ensure images match the required dimensions exactly
   - Use image editing software to resize if needed

3. **"Authentication failed"**
   - Verify your API credentials in the `.env` file
   - Check if your credentials are for the correct environment (integration vs. production)

4. **"Address validation failed"**
   - Ensure all required fields are filled
   - Check that the address format matches Swiss Post requirements

### File Permissions

Ensure the storage directories are writable:
```bash
chmod -R 775 workbench/storage
```

### Logs

Check the Laravel logs for detailed error information:
```bash
tail -f storage/logs/laravel.log
```

## Security

- Never commit real API credentials to version control
- Use the integration environment for testing
- Be mindful of quota usage when testing
- Clean up test images regularly from storage

## Support

For issues with the Swiss Post API itself, consult:
- Swiss Post Postcard API documentation
- Your Swiss Post account manager
- The main package documentation and examples
