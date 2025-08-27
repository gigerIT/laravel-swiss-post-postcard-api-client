<?php

namespace Examples;

use Gigerit\PostcardApi\DTOs\Address\RecipientAddress;
use Gigerit\PostcardApi\DTOs\Address\SenderAddress;
use Gigerit\PostcardApi\Messages\PostcardMessage;
use Illuminate\Notifications\Notification;

class WelcomePostcardNotification extends Notification
{
    public function __construct(
        private string $imagePath,
        private string $welcomeMessage,
        private ?string $campaignKey = null
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['postcard'];
    }

    /**
     * Get the postcard representation of the notification.
     */
    public function toPostcard($notifiable): PostcardMessage
    {
        // Create the postcard message
        $message = PostcardMessage::create($this->imagePath)
            ->text($this->welcomeMessage);

        // Set campaign if provided
        if ($this->campaignKey) {
            $message = $message->campaign($this->campaignKey);
        }

        // Set sender address (you might want to get this from config)
        $senderAddress = new SenderAddress(
            street: 'Your Company Street 1',
            zip: '8000',
            city: 'Zurich',
            company: 'Your Company Name'
        );
        $message = $message->from($senderAddress);

        // If notifiable has specific address method, use it
        if (method_exists($notifiable, 'getPostcardRecipientAddress')) {
            return $message->autoApprove(true);
        }

        // Otherwise, try to build address from notifiable attributes
        if (method_exists($notifiable, 'toArray')) {
            $data = $notifiable->toArray();
            
            if (isset($data['street'], $data['zip'], $data['city'], $data['country'])) {
                $recipientAddress = new RecipientAddress(
                    street: $data['street'],
                    zip: $data['zip'],
                    city: $data['city'],
                    country: $data['country'],
                    firstname: $data['first_name'] ?? $data['firstname'] ?? null,
                    lastname: $data['last_name'] ?? $data['lastname'] ?? null,
                    company: $data['company'] ?? null,
                    houseNr: $data['house_number'] ?? $data['house_nr'] ?? null
                );
                
                return $message->to($recipientAddress)->autoApprove(true);
            }
        }

        // Let the channel handle address resolution
        return $message->autoApprove(true);
    }
}
