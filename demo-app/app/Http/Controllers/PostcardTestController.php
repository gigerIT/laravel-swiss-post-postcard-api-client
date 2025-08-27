<?php

namespace App\Http\Controllers;

use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Gigerit\PostcardApi\DTOs\Address\SenderAddress;
use Gigerit\PostcardApi\Enums\ImageDimensions;
use Gigerit\PostcardApi\Exceptions\SwissPostApiException;
use Gigerit\PostcardApi\PostcardApi as PostcardApiInstance;
use Gigerit\PostcardApi\Services\OAuth2Service;
use Gigerit\PostcardApi\Validation\PostcardValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostcardTestController
{
    public function __construct(private PostcardApiInstance $postcardApi) {}

    /**
     * Show the main testing interface
     */
    public function index(): View
    {
        return view('postcard-test');
    }

    /**
     * Test raw OAuth2 request to see exact response
     */
    public function testRawOAuth(Request $request): JsonResponse
    {
        try {
            $clientId = config('swiss-post-postcard-api-client.oauth.client_id');
            $clientSecret = config('swiss-post-postcard-api-client.oauth.client_secret');
            $tokenUrl = config('swiss-post-postcard-api-client.oauth.token_url');
            $scope = config('swiss-post-postcard-api-client.oauth.scope');

            // Make raw HTTP request to OAuth endpoint
            $response = Http::asForm()
                ->timeout(30)
                ->post($tokenUrl, [
                    'grant_type' => 'client_credentials',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'scope' => $scope,
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Raw OAuth request completed',
                'data' => [
                    'status_code' => $response->status(),
                    'headers' => $response->headers(),
                    'body_preview' => substr($response->body(), 0, 500),
                    'is_json' => $response->json() !== null,
                    'content_type' => $response->header('Content-Type'),
                    'config_used' => [
                        'token_url' => $tokenUrl,
                        'client_id' => substr($clientId, 0, 8).'...',
                        'scope' => $scope,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Raw OAuth request failed: '.$e->getMessage(),
                'type' => 'raw_oauth_error',
                'details' => [
                    'exception_class' => get_class($e),
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'config_used' => [
                        'token_url' => config('swiss-post-postcard-api-client.oauth.token_url'),
                        'client_id' => substr(config('swiss-post-postcard-api-client.oauth.client_id'), 0, 8).'...',
                        'scope' => config('swiss-post-postcard-api-client.oauth.scope'),
                    ],
                ],
            ], 400);
        }
    }

    /**
     * Debug OAuth2 token request
     */
    public function debugOAuth(Request $request): JsonResponse
    {
        try {
            $oauth2Service = new OAuth2Service;

            // Clear any cached token first
            $oauth2Service->clearToken();

            // Try to get a fresh token
            $token = $oauth2Service->getAccessToken();

            return response()->json([
                'success' => true,
                'message' => 'OAuth2 token obtained successfully',
                'data' => [
                    'token_preview' => substr($token, 0, 20).'...',
                    'token_length' => strlen($token),
                    'config' => [
                        'client_id' => substr(config('swiss-post-postcard-api-client.oauth.client_id'), 0, 8).'...',
                        'token_url' => config('swiss-post-postcard-api-client.oauth.token_url'),
                        'base_url' => config('swiss-post-postcard-api-client.base_url'),
                        'scope' => config('swiss-post-postcard-api-client.oauth.scope'),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'OAuth2 token request failed: '.$e->getMessage(),
                'type' => 'oauth_error',
                'details' => [
                    'exception_class' => get_class($e),
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'config' => [
                        'client_id' => substr(config('swiss-post-postcard-api-client.oauth.client_id'), 0, 8).'...',
                        'token_url' => config('swiss-post-postcard-api-client.oauth.token_url'),
                        'base_url' => config('swiss-post-postcard-api-client.base_url'),
                        'scope' => config('swiss-post-postcard-api-client.oauth.scope'),
                    ],
                ],
            ], 400);
        }
    }

    /**
     * Test campaign statistics
     */
    public function testCampaignStats(Request $request): JsonResponse
    {
        try {
            // Check if required config is available
            $clientId = config('swiss-post-postcard-api-client.oauth.client_id');
            $clientSecret = config('swiss-post-postcard-api-client.oauth.client_secret');

            if (empty($clientId) || empty($clientSecret)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Swiss Post API credentials not configured. Please set SWISS_POST_POSTCARD_API_CLIENT_ID and SWISS_POST_POSTCARD_API_CLIENT_SECRET in your .env file.',
                    'type' => 'config_error',
                    'config_info' => [
                        'client_id_set' => ! empty($clientId),
                        'client_secret_set' => ! empty($clientSecret),
                        'base_url' => config('swiss-post-postcard-api-client.base_url'),
                        'auth_url' => config('swiss-post-postcard-api-client.oauth.auth_url'),
                        'token_url' => config('swiss-post-postcard-api-client.oauth.token_url'),
                    ],
                ], 400);
            }

            $campaignKey = $request->input('campaign_key');

            if ($campaignKey) {
                $stats = $this->postcardApi->campaigns()->getStatistics($campaignKey);
            } else {
                $stats = $this->postcardApi->campaigns()->getDefaultCampaignStatistics();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'campaignKey' => $stats->campaignKey,
                    'quota' => $stats->quota,
                    'sendPostcards' => $stats->sendPostcards,
                    'freeToSendPostcards' => $stats->freeToSendPostcards,
                    'usagePercentage' => $stats->getUsagePercentage(),
                    'hasRemainingQuota' => $stats->freeToSendPostcards > 0,
                ],
            ]);
        } catch (SwissPostApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'api_error',
                'details' => [
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ], 400);
        } catch (\Exception $e) {
            // Check if this is likely a JSON parsing error
            if (str_contains($e->getMessage(), 'Syntax error') && str_contains($e->getTraceAsString(), 'json_decode')) {
                return response()->json([
                    'success' => false,
                    'error' => 'The Swiss Post API returned an invalid response (likely HTML error page instead of JSON). This usually indicates authentication or configuration issues.',
                    'type' => 'json_parse_error',
                    'details' => [
                        'original_error' => $e->getMessage(),
                        'suggestions' => [
                            'Check your SWISS_POST_POSTCARD_API_CLIENT_ID and SWISS_POST_POSTCARD_API_CLIENT_SECRET environment variables',
                            'Verify your API credentials are valid and active',
                            'Ensure you have the correct API endpoints configured',
                            'Check if your IP address is whitelisted (if required)',
                        ],
                        'config_check' => [
                            'client_id_set' => ! empty(config('swiss-post-postcard-api-client.oauth.client_id')),
                            'client_secret_set' => ! empty(config('swiss-post-postcard-api-client.oauth.client_secret')),
                            'base_url' => config('swiss-post-postcard-api-client.base_url'),
                        ],
                    ],
                ], 400);
            }

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'general_error',
                'details' => [
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ],
            ], 500);
        }
    }

    /**
     * Validate address data
     */
    public function validateAddress(Request $request): JsonResponse
    {
        $addressData = $request->input('address');
        $addressType = $request->input('type', 'recipient'); // recipient or sender

        try {
            if ($addressType === 'recipient') {
                $address = new RecipientAddress(
                    street: $addressData['street'] ?? '',
                    zip: $addressData['zip'] ?? '',
                    city: $addressData['city'] ?? '',
                    country: $addressData['country'] ?? 'CH',
                    firstname: $addressData['firstname'] ?? '',
                    lastname: $addressData['lastname'] ?? '',
                    houseNr: $addressData['houseNr'] ?? '',
                    company: $addressData['company'] ?? null,
                    poBox: $addressData['poBox'] ?? null
                );
                $errors = PostcardValidator::validateRecipientAddress($address);
            } else {
                $address = new SenderAddress(
                    street: $addressData['street'] ?? '',
                    zip: $addressData['zip'] ?? '',
                    city: $addressData['city'] ?? '',
                    firstname: $addressData['firstname'] ?? '',
                    lastname: $addressData['lastname'] ?? '',
                    houseNr: $addressData['houseNr'] ?? '',
                    company: $addressData['company'] ?? null
                );
                $errors = PostcardValidator::validateSenderAddress($address);
            }

            return response()->json([
                'success' => true,
                'valid' => empty($errors),
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Validate text content
     */
    public function validateText(Request $request): JsonResponse
    {
        $text = $request->input('text', '');

        try {
            $errors = PostcardValidator::validateSenderText($text);

            return response()->json([
                'success' => true,
                'valid' => empty($errors),
                'errors' => $errors,
                'length' => strlen($text),
                'encoding_compatible' => mb_check_encoding($text, 'UTF-8'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create a test postcard (step-by-step)
     */
    public function createPostcard(Request $request): JsonResponse
    {
        try {
            // Validate required fields
            $recipientData = $request->input('recipient');
            $senderData = $request->input('sender');

            $recipient = new RecipientAddress(
                street: $recipientData['street'],
                zip: $recipientData['zip'],
                city: $recipientData['city'],
                country: $recipientData['country'] ?? 'CH',
                firstname: $recipientData['firstname'],
                lastname: $recipientData['lastname'],
                houseNr: $recipientData['houseNr']
            );

            $sender = null;
            if (! empty($senderData['firstname']) && ! empty($senderData['lastname'])) {
                $sender = new SenderAddress(
                    street: $senderData['street'],
                    zip: $senderData['zip'],
                    city: $senderData['city'],
                    firstname: $senderData['firstname'],
                    lastname: $senderData['lastname'],
                    houseNr: $senderData['houseNr']
                );
            }

            $postcard = new \Gigerit\PostcardApi\DTOs\Postcard\Postcard(
                recipientAddress: $recipient,
                senderAddress: $sender
            );

            $campaignKey = $request->input('campaign_key');
            $result = $this->postcardApi->postcards()->create($campaignKey, $postcard);

            return response()->json([
                'success' => true,
                'data' => [
                    'cardKey' => $result->cardKey,
                    'hasWarnings' => $result->hasWarnings(),
                    'warnings' => $result->hasWarnings() ? $result->getWarningMessages() : [],
                ],
            ]);
        } catch (SwissPostApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'api_error',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'validation_error',
            ], 400);
        }
    }

    /**
     * Upload image to postcard
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'card_key' => 'required|string',
            'image' => 'required|file|mimes:jpeg,png|max:10240', // 10MB max
        ]);

        try {
            $cardKey = $request->input('card_key');
            $image = $request->file('image');

            // Store the uploaded file temporarily
            $path = $image->store('temp', 'local');
            $fullPath = Storage::disk('local')->path($path);

            // Validate image dimensions
            $errors = PostcardValidator::validateImageDimensions($fullPath, ImageDimensions::FRONT_IMAGE);

            if (! empty($errors)) {
                Storage::delete($path);

                return response()->json([
                    'success' => false,
                    'error' => 'Image validation failed',
                    'validation_errors' => $errors,
                ], 400);
            }

            // Upload to Swiss Post
            $this->postcardApi->postcards()->uploadImage($cardKey, $fullPath);

            // Clean up temp file
            Storage::delete($path);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
            ]);
        } catch (SwissPostApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'api_error',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'general_error',
            ], 500);
        }
    }

    /**
     * Add sender text to postcard
     */
    public function addSenderText(Request $request): JsonResponse
    {
        $request->validate([
            'card_key' => 'required|string',
            'text' => 'required|string|max:2000',
        ]);

        try {
            $cardKey = $request->input('card_key');
            $text = $request->input('text');

            $this->postcardApi->postcards()->uploadSenderText($cardKey, $text);

            return response()->json([
                'success' => true,
                'message' => 'Sender text added successfully',
            ]);
        } catch (SwissPostApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'api_error',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'general_error',
            ], 500);
        }
    }

    /**
     * Add branding to postcard
     */
    public function addBranding(Request $request): JsonResponse
    {
        $cardKey = $request->input('card_key');
        $brandingType = $request->input('type'); // text, qr, image, stamp

        try {
            switch ($brandingType) {
                case 'text':
                    $this->postcardApi->branding()->addSimpleText(
                        cardKey: $cardKey,
                        text: $request->input('text'),
                        blockColor: $request->input('block_color', '#000000'),
                        textColor: $request->input('text_color', '#FFFFFF')
                    );
                    break;

                case 'qr':
                    $this->postcardApi->branding()->addSimpleQRCode(
                        cardKey: $cardKey,
                        encodedText: $request->input('encoded_text'),
                        accompanyingText: $request->input('accompanying_text', '')
                    );
                    break;

                case 'image':
                case 'stamp':
                    $request->validate([
                        'image' => 'required|file|mimes:jpeg,png|max:5120',
                    ]);

                    $image = $request->file('image');
                    $path = $image->store('temp', 'local');
                    $fullPath = Storage::disk('local')->path($path);

                    if ($brandingType === 'image') {
                        $this->postcardApi->branding()->uploadImage($cardKey, $fullPath);
                    } else {
                        $this->postcardApi->branding()->uploadStamp($cardKey, $fullPath);
                    }

                    Storage::delete($path);
                    break;

                default:
                    throw new \InvalidArgumentException('Invalid branding type');
            }

            return response()->json([
                'success' => true,
                'message' => ucfirst($brandingType).' branding added successfully',
            ]);
        } catch (SwissPostApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'api_error',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'general_error',
            ], 500);
        }
    }

    /**
     * Get postcard state
     */
    public function getPostcardState(Request $request): JsonResponse
    {
        $cardKey = $request->input('card_key');

        try {
            $state = $this->postcardApi->postcards()->getState($cardKey);

            return response()->json([
                'success' => true,
                'data' => [
                    'cardKey' => $state->cardKey,
                    'state' => $state->state->state,
                    'hasWarnings' => $state->hasWarnings(),
                    'warnings' => $state->hasWarnings() ? $state->getWarningMessages() : [],
                ],
            ]);
        } catch (SwissPostApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'api_error',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'general_error',
            ], 500);
        }
    }

    /**
     * Get postcard preview
     */
    public function getPreview(Request $request): JsonResponse
    {
        $cardKey = $request->input('card_key');
        $side = $request->input('side', 'front'); // front or back

        try {
            if ($side === 'front') {
                $preview = $this->postcardApi->postcards()->getPreviewFront($cardKey);
            } else {
                $preview = $this->postcardApi->postcards()->getPreviewBack($cardKey);
            }

            // Store preview image temporarily for display
            $filename = "preview_{$side}_{$cardKey}_{time()}.jpg";
            $path = "previews/{$filename}";
            Storage::disk('public')->put($path, $preview->getDecodedImage());

            return response()->json([
                'success' => true,
                'data' => [
                    'preview_url' => url("storage/{$path}"),
                    'filename' => $filename,
                ],
            ]);
        } catch (SwissPostApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'api_error',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'general_error',
            ], 500);
        }
    }

    /**
     * Approve postcard for sending
     */
    public function approvePostcard(Request $request): JsonResponse
    {
        $cardKey = $request->input('card_key');

        try {
            $this->postcardApi->postcards()->approve($cardKey);

            return response()->json([
                'success' => true,
                'message' => 'Postcard approved for sending',
            ]);
        } catch (SwissPostApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'api_error',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'general_error',
            ], 500);
        }
    }

    /**
     * Create complete postcard (all-in-one method)
     */
    public function createCompletePostcard(Request $request): JsonResponse
    {
        $request->validate([
            'recipient' => 'required|array',
            'image' => 'required|file|mimes:jpeg,png|max:10240',
            'sender_text' => 'required|string|max:2000',
        ]);

        try {
            $recipientData = $request->input('recipient');
            $senderData = $request->input('sender', []);

            $recipient = new RecipientAddress(
                street: $recipientData['street'],
                zip: $recipientData['zip'],
                city: $recipientData['city'],
                country: $recipientData['country'] ?? 'CH',
                firstname: $recipientData['firstname'],
                lastname: $recipientData['lastname'],
                houseNr: $recipientData['houseNr']
            );

            $sender = null;
            if (! empty($senderData['firstname']) && ! empty($senderData['lastname'])) {
                $sender = new SenderAddress(
                    street: $senderData['street'],
                    zip: $senderData['zip'],
                    city: $senderData['city'],
                    firstname: $senderData['firstname'],
                    lastname: $senderData['lastname'],
                    houseNr: $senderData['houseNr']
                );
            }

            // Store uploaded image temporarily
            $image = $request->file('image');
            $path = $image->store('temp', 'local');
            $fullPath = Storage::disk('local')->path($path);

            $campaignKey = $request->input('campaign_key');
            $senderText = $request->input('sender_text');

            $result = $this->postcardApi->postcards()->createComplete(
                recipientAddress: $recipient,
                imagePath: $fullPath,
                senderAddress: $sender,
                senderText: $senderText,
                campaignKey: $campaignKey,
                filename: $image->getClientOriginalName()
            );

            // Clean up temp file
            Storage::delete($path);

            return response()->json([
                'success' => true,
                'data' => [
                    'cardKey' => $result->cardKey,
                    'hasWarnings' => $result->hasWarnings(),
                    'warnings' => $result->hasWarnings() ? $result->getWarningMessages() : [],
                ],
            ]);
        } catch (SwissPostApiException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'api_error',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'general_error',
            ], 500);
        }
    }

    /**
     * Debug create postcard with raw API logging
     */
    public function debugCreatePostcard(Request $request): JsonResponse
    {
        try {
            $recipientData = $request->input('recipient');

            // For debugging purposes, let's simulate what the API call would look like
            $baseUrl = config('swiss-post-postcard-api-client.base_url');
            $debugInfo = [];

            // Simulate OAuth2 request
            $oauthUrl = config('swiss-post-postcard-api-client.oauth.token_url');
            $clientId = config('swiss-post-postcard-api-client.oauth.client_id');
            $clientSecret = config('swiss-post-postcard-api-client.oauth.client_secret');

            $debugInfo[] = [
                'method' => 'POST',
                'url' => $oauthUrl,
                'status' => 200,
                'request' => [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Accept' => 'application/json',
                    ],
                    'body' => http_build_query([
                        'grant_type' => 'client_credentials',
                        'client_id' => $clientId,
                        'client_secret' => substr($clientSecret, 0, 8).'...',
                        'scope' => config('swiss-post-postcard-api-client.oauth.scope'),
                    ]),
                ],
                'response' => [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode([
                        'access_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
                        'token_type' => 'Bearer',
                        'expires_in' => 3600,
                    ]),
                ],
                'timestamp' => now()->toISOString(),
            ];

            // Simulate Create Postcard API call
            $createPostcardUrl = $baseUrl.'/postcards';
            $postcardPayload = [
                'recipientAddress' => [
                    'firstname' => $recipientData['firstname'],
                    'lastname' => $recipientData['lastname'],
                    'street' => $recipientData['street'],
                    'houseNr' => $recipientData['houseNr'],
                    'zip' => $recipientData['zip'],
                    'city' => $recipientData['city'],
                    'country' => $recipientData['country'] ?? 'CH',
                ],
            ];

            $debugInfo[] = [
                'method' => 'POST',
                'url' => $createPostcardUrl,
                'status' => 201,
                'request' => [
                    'headers' => [
                        'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ],
                    'body' => json_encode($postcardPayload, JSON_PRETTY_PRINT),
                ],
                'response' => [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode([
                        'cardKey' => 'card_'.uniqid(),
                        'state' => 'CREATED',
                        'warnings' => [],
                    ], JSON_PRETTY_PRINT),
                ],
                'timestamp' => now()->toISOString(),
            ];

            // Actually perform the API call
            $recipient = new RecipientAddress(
                street: $recipientData['street'],
                zip: $recipientData['zip'],
                city: $recipientData['city'],
                country: $recipientData['country'] ?? 'CH',
                firstname: $recipientData['firstname'],
                lastname: $recipientData['lastname'],
                houseNr: $recipientData['houseNr']
            );

            $postcard = new \Gigerit\PostcardApi\DTOs\Postcard\Postcard(
                recipientAddress: $recipient
            );

            $campaignKey = $request->input('campaign_key');
            $result = $this->postcardApi->postcards()->create($campaignKey, $postcard);

            // Update the simulated response with actual data
            $debugInfo[1]['response']['body'] = json_encode([
                'cardKey' => $result->cardKey,
                'state' => 'CREATED',
                'warnings' => $result->hasWarnings() ? $result->getWarningMessages() : [],
            ], JSON_PRETTY_PRINT);

            return response()->json([
                'success' => true,
                'data' => [
                    'cardKey' => $result->cardKey,
                    'debugInfo' => $debugInfo,
                    'note' => 'This shows simulated HTTP requests/responses. Real API calls are made but request/response interception requires deeper integration.',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'debug_error',
            ], 400);
        }
    }

    /**
     * Debug campaign stats with raw API logging
     */
    public function debugCampaignStats(Request $request): JsonResponse
    {
        try {
            $baseUrl = config('swiss-post-postcard-api-client.base_url');
            $debugInfo = [];

            // Simulate OAuth2 request
            $oauthUrl = config('swiss-post-postcard-api-client.oauth.token_url');
            $clientId = config('swiss-post-postcard-api-client.oauth.client_id');
            $clientSecret = config('swiss-post-postcard-api-client.oauth.client_secret');

            $debugInfo[] = [
                'method' => 'POST',
                'url' => $oauthUrl,
                'status' => 200,
                'request' => [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Accept' => 'application/json',
                    ],
                    'body' => http_build_query([
                        'grant_type' => 'client_credentials',
                        'client_id' => $clientId,
                        'client_secret' => substr($clientSecret, 0, 8).'...',
                        'scope' => config('swiss-post-postcard-api-client.oauth.scope'),
                    ]),
                ],
                'response' => [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode([
                        'access_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
                        'token_type' => 'Bearer',
                        'expires_in' => 3600,
                    ]),
                ],
                'timestamp' => now()->toISOString(),
            ];

            // Simulate Get Campaign Stats API call
            $campaignStatsUrl = $baseUrl.'/campaigns/statistics';

            $campaignKey = $request->input('campaign_key');

            if ($campaignKey) {
                $stats = $this->postcardApi->campaigns()->getStatistics($campaignKey);
            } else {
                $stats = $this->postcardApi->campaigns()->getDefaultCampaignStatistics();
            }

            $debugInfo[] = [
                'method' => 'GET',
                'url' => $campaignStatsUrl,
                'status' => 200,
                'request' => [
                    'headers' => [
                        'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
                        'Accept' => 'application/json',
                    ],
                    'body' => '',
                ],
                'response' => [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode([
                        'campaignKey' => $stats->campaignKey,
                        'quota' => $stats->quota,
                        'sendPostcards' => $stats->sendPostcards,
                        'freeToSendPostcards' => $stats->freeToSendPostcards,
                    ], JSON_PRETTY_PRINT),
                ],
                'timestamp' => now()->toISOString(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'campaignStats' => [
                        'campaignKey' => $stats->campaignKey,
                        'quota' => $stats->quota,
                        'sendPostcards' => $stats->sendPostcards,
                        'freeToSendPostcards' => $stats->freeToSendPostcards,
                        'usagePercentage' => $stats->getUsagePercentage(),
                    ],
                    'debugInfo' => $debugInfo,
                    'note' => 'This shows simulated HTTP requests/responses based on actual API calls made.',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'debug_error',
            ], 400);
        }
    }

    /**
     * Debug address validation with raw API logging
     */
    public function debugValidateAddress(Request $request): JsonResponse
    {
        try {
            $addressData = $request->input('address');
            $addressType = $request->input('type', 'recipient');
            $debugInfo = [];

            // Simulate OAuth2 request
            $oauthUrl = config('swiss-post-postcard-api-client.oauth.token_url');
            $clientId = config('swiss-post-postcard-api-client.oauth.client_id');
            $clientSecret = config('swiss-post-postcard-api-client.oauth.client_secret');

            $debugInfo[] = [
                'method' => 'POST',
                'url' => $oauthUrl,
                'status' => 200,
                'request' => [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Accept' => 'application/json',
                    ],
                    'body' => http_build_query([
                        'grant_type' => 'client_credentials',
                        'client_id' => $clientId,
                        'client_secret' => substr($clientSecret, 0, 8).'...',
                        'scope' => config('swiss-post-postcard-api-client.oauth.scope'),
                    ]),
                ],
                'response' => [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode([
                        'access_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
                        'token_type' => 'Bearer',
                        'expires_in' => 3600,
                    ]),
                ],
                'timestamp' => now()->toISOString(),
            ];

            // Perform local validation
            if ($addressType === 'recipient') {
                $address = new RecipientAddress(
                    street: $addressData['street'] ?? '',
                    zip: $addressData['zip'] ?? '',
                    city: $addressData['city'] ?? '',
                    country: $addressData['country'] ?? 'CH',
                    firstname: $addressData['firstname'] ?? '',
                    lastname: $addressData['lastname'] ?? '',
                    houseNr: $addressData['houseNr'] ?? '',
                    company: $addressData['company'] ?? null,
                    poBox: $addressData['poBox'] ?? null
                );
                $errors = PostcardValidator::validateRecipientAddress($address);
            } else {
                $address = new SenderAddress(
                    street: $addressData['street'] ?? '',
                    zip: $addressData['zip'] ?? '',
                    city: $addressData['city'] ?? '',
                    firstname: $addressData['firstname'] ?? '',
                    lastname: $addressData['lastname'] ?? '',
                    houseNr: $addressData['houseNr'] ?? '',
                    company: $addressData['company'] ?? null
                );
                $errors = PostcardValidator::validateSenderAddress($address);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'validation' => [
                        'valid' => empty($errors),
                        'errors' => $errors,
                        'type' => $addressType,
                    ],
                    'debugInfo' => $debugInfo,
                    'note' => 'Address validation is performed locally. OAuth request shown for demonstration of API authentication flow.',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'type' => 'debug_error',
            ], 400);
        }
    }
}
