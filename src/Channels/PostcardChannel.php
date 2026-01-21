<?php

namespace Gigerit\PostcardApi\Channels;

use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Gigerit\PostcardApi\Exceptions\PostcardNotificationException;
use Gigerit\PostcardApi\Messages\PostcardMessage;
use Gigerit\PostcardApi\PostcardApi;
use Illuminate\Notifications\Notification;

class PostcardChannel
{
    public function __construct(
        protected PostcardApi $postcardApi
    ) {}

    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        // The notification must implement a toPostcard method that returns a PostcardMessage
        if (! method_exists($notification, 'toPostcard')) {
            throw new PostcardNotificationException('Notification must implement toPostcard method.');
        }

        /** @var PostcardMessage $message */
        $message = $notification->toPostcard($notifiable);

        if (! $message instanceof PostcardMessage) {
            $type = is_object($message) ? get_class($message) : gettype($message);

            throw new PostcardNotificationException("Notification must return a PostcardMessage instance from toPostcard method, but {$type} was returned instead.");
        }

        try {
            // Get recipient address from the message or notifiable
            $recipientAddress = $this->getRecipientAddress($message, $notifiable);

            // Create and send the postcard
            $response = $this->postcardApi->postcards()->createComplete(
                recipientAddress: $recipientAddress,
                imagePath: $message->imagePath,
                senderAddress: $message->senderAddress,
                senderText: $message->senderText,
                campaignKey: $message->campaignKey
            );

            // Apply branding if specified
            $this->applyBranding($response->cardKey, $message);

            // Optionally approve the postcard if auto-approve is enabled
            if ($message->autoApprove) {
                $this->postcardApi->postcards()->approve($response->cardKey);
            }

        } catch (\Exception $e) {
            throw new PostcardNotificationException(
                "Failed to send postcard notification: {$e->getMessage()}",
                previous: $e
            );
        }
    }

    /**
     * Get the recipient address from the message or notifiable.
     */
    protected function getRecipientAddress(PostcardMessage $message, object $notifiable): RecipientAddress
    {
        // If message has a recipient address, use it
        if ($message->recipientAddress) {
            return $message->recipientAddress;
        }

        // Try to get address from notifiable object
        if (method_exists($notifiable, 'getPostcardRecipientAddress')) {
            $address = $notifiable->getPostcardRecipientAddress();
            if ($address instanceof RecipientAddress) {
                return $address;
            }
            if (is_array($address)) {
                return RecipientAddress::fromArray($address);
            }
        }

        // Try to build address from common attributes
        if (method_exists($notifiable, 'toArray')) {
            $data = $notifiable->toArray();

            return $this->buildRecipientAddressFromArray($data);
        }

        throw new PostcardNotificationException(
            'Unable to determine recipient address. Either set recipientAddress on PostcardMessage or implement getPostcardRecipientAddress() method on notifiable.'
        );
    }

    /**
     * Build recipient address from array data.
     */
    protected function buildRecipientAddressFromArray(array $data): RecipientAddress
    {
        // Map common field names
        $mapping = [
            'street' => ['street', 'address', 'street_address'],
            'zip' => ['zip', 'postal_code', 'postcode', 'zipcode'],
            'city' => ['city', 'town'],
            'country' => ['country', 'country_code'],
            'firstname' => ['firstname', 'first_name', 'name'],
            'lastname' => ['lastname', 'last_name', 'surname'],
            'company' => ['company', 'company_name', 'organization'],
            'houseNr' => ['house_nr', 'house_number', 'number'],
        ];

        $addressData = [];
        foreach ($mapping as $field => $possibleKeys) {
            foreach ($possibleKeys as $key) {
                if (isset($data[$key]) && ! empty($data[$key])) {
                    $addressData[$field] = $data[$key];
                    break;
                }
            }
        }

        // Ensure required fields are present
        $required = ['street', 'zip', 'city', 'country'];
        foreach ($required as $field) {
            if (empty($addressData[$field])) {
                throw new PostcardNotificationException(
                    "Missing required address field: {$field}. Available data: ".implode(', ', array_keys($data))
                );
            }
        }

        return RecipientAddress::fromArray($addressData);
    }

    /**
     * Apply branding to the postcard if specified in the message.
     */
    protected function applyBranding(string $cardKey, PostcardMessage $message): void
    {
        // Apply branding text and QR code if available
        if ($message->branding) {
            if ($message->branding->hasBrandingText()) {
                $this->postcardApi->branding()->uploadText($cardKey, $message->branding->brandingText);
            }

            if ($message->branding->hasBrandingQRCode()) {
                $this->postcardApi->branding()->uploadQRCode($cardKey, $message->branding->brandingQRCode);
            }
        }

        // Apply branding image if specified
        if ($message->brandingImagePath) {
            $this->postcardApi->branding()->uploadImage($cardKey, $message->brandingImagePath);
        }

        // Apply custom stamp if specified
        if ($message->brandingStampPath) {
            $this->postcardApi->branding()->uploadStamp($cardKey, $message->brandingStampPath);
        }
    }
}
