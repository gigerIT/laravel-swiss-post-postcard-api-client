<?php

namespace Gigerit\PostcardApi\Enums;

enum TextLimits
{
    case SENDER_TEXT;
    case SENDER_ADDRESS;
    case SENDER_ADDRESS_FIRSTNAME;
    case SENDER_ADDRESS_LASTNAME;
    case SENDER_ADDRESS_COMPANY;
    case SENDER_ADDRESS_STREET;
    case SENDER_ADDRESS_HOUSENR;
    case SENDER_ADDRESS_ZIP;
    case SENDER_ADDRESS_CITY;
    case RECIPIENT_ADDRESS_TITLE;
    case RECIPIENT_ADDRESS_FIRSTNAME;
    case RECIPIENT_ADDRESS_LASTNAME;
    case RECIPIENT_ADDRESS_COMPANY;
    case RECIPIENT_ADDRESS_STREET;
    case RECIPIENT_ADDRESS_HOUSENR;
    case RECIPIENT_ADDRESS_ZIP;
    case RECIPIENT_ADDRESS_CITY;
    case RECIPIENT_ADDRESS_POBOX;
    case RECIPIENT_ADDRESS_ADDITIONAL_INFO;
    case BRANDING_TEXT_TEXT;
    case BRANDING_TEXT_TEXTCOLOR;
    case BRANDING_TEXT_BACKGROUND_COLOR;
    case BRANDING_QR_ENCODED_TEXT;
    case BRANDING_QR_ACCOMPANYING_TEXT;
    case BRANDING_QR_TEXTCOLOR;
    case BRANDING_QR_BACKGROUND_COLOR;

    public function getLimits(): array
    {
        return match ($this) {
            self::SENDER_TEXT => ['max' => 900, 'min' => 0],
            self::SENDER_ADDRESS => ['max' => 45, 'min' => 0],
            self::SENDER_ADDRESS_FIRSTNAME => ['max' => 75, 'min' => 2],
            self::SENDER_ADDRESS_LASTNAME => ['max' => 75, 'min' => 2],
            self::SENDER_ADDRESS_COMPANY => ['max' => 39, 'min' => 2],
            self::SENDER_ADDRESS_STREET => ['max' => 50, 'min' => 2],
            self::SENDER_ADDRESS_HOUSENR => ['max' => 5, 'min' => 0],
            self::SENDER_ADDRESS_ZIP => ['max' => 39, 'min' => 4],
            self::SENDER_ADDRESS_CITY => ['max' => 30, 'min' => 2],
            self::RECIPIENT_ADDRESS_TITLE => ['max' => 30, 'min' => 0],
            self::RECIPIENT_ADDRESS_FIRSTNAME => ['max' => 75, 'min' => 2],
            self::RECIPIENT_ADDRESS_LASTNAME => ['max' => 75, 'min' => 2],
            self::RECIPIENT_ADDRESS_COMPANY => ['max' => 39, 'min' => 2],
            self::RECIPIENT_ADDRESS_STREET => ['max' => 50, 'min' => 2],
            self::RECIPIENT_ADDRESS_HOUSENR => ['max' => 5, 'min' => 0],
            self::RECIPIENT_ADDRESS_ZIP => ['max' => 39, 'min' => 4],
            self::RECIPIENT_ADDRESS_CITY => ['max' => 30, 'min' => 2],
            self::RECIPIENT_ADDRESS_POBOX => ['max' => 5, 'min' => 0],
            self::RECIPIENT_ADDRESS_ADDITIONAL_INFO => ['max' => 75, 'min' => 0],
            self::BRANDING_TEXT_TEXT => ['max' => 250, 'min' => 0],
            self::BRANDING_TEXT_TEXTCOLOR => ['max' => 7, 'min' => 4],
            self::BRANDING_TEXT_BACKGROUND_COLOR => ['max' => 7, 'min' => 4],
            self::BRANDING_QR_ENCODED_TEXT => ['max' => 100, 'min' => 0],
            self::BRANDING_QR_ACCOMPANYING_TEXT => ['max' => 250, 'min' => 0],
            self::BRANDING_QR_TEXTCOLOR => ['max' => 7, 'min' => 4],
            self::BRANDING_QR_BACKGROUND_COLOR => ['max' => 7, 'min' => 4],
        };
    }

    public function getMaxLength(): int
    {
        return $this->getLimits()['max'];
    }

    public function getMinLength(): int
    {
        return $this->getLimits()['min'];
    }

    public function isValidLength(string $text): bool
    {
        $length = mb_strlen($text, 'UTF-8');

        return $length >= $this->getMinLength() && $length <= $this->getMaxLength();
    }

    public function validateLength(string $text, string $fieldName): ?string
    {
        if (! $this->isValidLength($text)) {
            $length = mb_strlen($text, 'UTF-8');
            $min = $this->getMinLength();
            $max = $this->getMaxLength();

            if ($length < $min) {
                return "The length of {$fieldName} is too short (minimum: {$min}, provided: {$length})";
            }

            return "The length of {$fieldName} is too long (maximum: {$max}, provided: {$length})";
        }

        return null;
    }
}
