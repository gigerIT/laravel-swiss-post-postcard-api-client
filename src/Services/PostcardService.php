<?php

namespace Gigerit\PostcardApi\Services;

use Gigerit\PostcardApi\Connectors\SwissPostConnector;
use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Gigerit\PostcardApi\DTOs\Address\SenderAddress;
use Gigerit\PostcardApi\DTOs\Postcard\Postcard;
use Gigerit\PostcardApi\DTOs\Response\DefaultResponse;
use Gigerit\PostcardApi\DTOs\Response\PostcardStateResponse;
use Gigerit\PostcardApi\DTOs\Response\Preview;
use Gigerit\PostcardApi\Enums\ImageDimensions;
use Gigerit\PostcardApi\Requests\Addresses\UploadRecipientAddressRequest;
use Gigerit\PostcardApi\Requests\Addresses\UploadSenderAddressRequest;
use Gigerit\PostcardApi\Requests\Postcards\ApprovePostcardRequest;
use Gigerit\PostcardApi\Requests\Postcards\CreatePostcardRequest;
use Gigerit\PostcardApi\Requests\Postcards\GetPostcardStateRequest;
use Gigerit\PostcardApi\Requests\Postcards\GetPreviewBackRequest;
use Gigerit\PostcardApi\Requests\Postcards\GetPreviewFrontRequest;
use Gigerit\PostcardApi\Requests\Postcards\UploadImageRequest;
use Gigerit\PostcardApi\Requests\Postcards\UploadSenderTextRequest;
use Gigerit\PostcardApi\Validation\PostcardValidator;

class PostcardService
{
    public function __construct(
        protected SwissPostConnector $connector
    ) {}

    /**
     * Create a new postcard
     */
    public function create(?string $campaignKey = null, ?Postcard $postcard = null): DefaultResponse
    {
        $campaignKey = $campaignKey ?? config('swiss-post-postcard-api-client.default_campaign');

        if (! $campaignKey) {
            throw new \InvalidArgumentException('Campaign key is required. Either provide it as parameter or set SWISS_POST_POSTCARD_API_DEFAULT_CAMPAIGN in your .env file.');
        }

        $request = new CreatePostcardRequest($campaignKey, $postcard);
        $response = $this->connector->send($request);

        return DefaultResponse::fromArray($response->json());
    }

    /**
     * Upload an image to a postcard
     */
    public function uploadImage(string $cardKey, string $imagePath, bool $validateDimensions = true): DefaultResponse
    {
        if ($validateDimensions) {
            $errors = PostcardValidator::validateImageDimensions($imagePath, ImageDimensions::FRONT_IMAGE);
            if (! empty($errors)) {
                throw new \InvalidArgumentException('Image validation failed: '.implode(', ', $errors));
            }
        }

        $request = new UploadImageRequest($cardKey, $imagePath);
        $response = $this->connector->send($request);

        return DefaultResponse::fromArray($response->json());
    }

    /**
     * Upload sender text to a postcard
     */
    public function uploadSenderText(string $cardKey, string $senderText, bool $validateText = true): DefaultResponse
    {
        if ($validateText) {
            $errors = PostcardValidator::validateSenderText($senderText);
            if (! empty($errors)) {
                throw new \InvalidArgumentException('Sender text validation failed: '.implode(', ', $errors));
            }
        }

        $request = new UploadSenderTextRequest($cardKey, $senderText);
        $response = $this->connector->send($request);

        return DefaultResponse::fromArray($response->json());
    }

    /**
     * Upload sender address to a postcard
     */
    public function uploadSenderAddress(string $cardKey, SenderAddress $senderAddress, bool $validateAddress = true): DefaultResponse
    {
        if ($validateAddress) {
            $errors = PostcardValidator::validateSenderAddress($senderAddress);
            if (! empty($errors)) {
                throw new \InvalidArgumentException('Sender address validation failed: '.implode(', ', $errors));
            }
        }

        $request = new UploadSenderAddressRequest($cardKey, $senderAddress);
        $response = $this->connector->send($request);

        return DefaultResponse::fromArray($response->json());
    }

    /**
     * Upload recipient address to a postcard
     */
    public function uploadRecipientAddress(string $cardKey, RecipientAddress $recipientAddress, bool $validateAddress = true): DefaultResponse
    {
        if ($validateAddress) {
            $errors = PostcardValidator::validateRecipientAddress($recipientAddress);
            if (! empty($errors)) {
                throw new \InvalidArgumentException('Recipient address validation failed: '.implode(', ', $errors));
            }
        }

        $request = new UploadRecipientAddressRequest($cardKey, $recipientAddress);
        $response = $this->connector->send($request);

        return DefaultResponse::fromArray($response->json());
    }

    /**
     * Approve a postcard for sending
     */
    public function approve(string $cardKey): DefaultResponse
    {
        $request = new ApprovePostcardRequest($cardKey);
        $response = $this->connector->send($request);

        return DefaultResponse::fromArray($response->json());
    }

    /**
     * Get the current state of a postcard
     */
    public function getState(string $cardKey): PostcardStateResponse
    {
        $request = new GetPostcardStateRequest($cardKey);
        $response = $this->connector->send($request);

        return PostcardStateResponse::fromArray($response->json());
    }

    /**
     * Get front preview of a postcard
     */
    public function getPreviewFront(string $cardKey): Preview
    {
        $request = new GetPreviewFrontRequest($cardKey);
        $response = $this->connector->send($request);

        return Preview::fromArray($response->json());
    }

    /**
     * Get back preview of a postcard
     */
    public function getPreviewBack(string $cardKey): Preview
    {
        $request = new GetPreviewBackRequest($cardKey);
        $response = $this->connector->send($request);

        return Preview::fromArray($response->json());
    }

    /**
     * Create a complete postcard with all required data
     */
    public function createComplete(
        RecipientAddress $recipientAddress,
        string $imagePath,
        ?SenderAddress $senderAddress = null,
        ?string $senderText = null,
        ?string $campaignKey = null,
        ?string $filename = null
    ): DefaultResponse {
        // Create the postcard
        $postcard = new Postcard(
            senderAddress: $senderAddress,
            recipientAddress: $recipientAddress,
            senderText: $senderText
        );

        $createResponse = $this->create($campaignKey, $postcard);
        $cardKey = $createResponse->cardKey;

        // Upload the image
        $this->uploadImage($cardKey, $imagePath);

        return $createResponse;
    }
}
