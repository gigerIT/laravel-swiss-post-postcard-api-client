<?php

namespace Gigerit\PostcardApi\Enums;

enum ImageDimensions: string
{
    case FRONT_IMAGE = '1819x1311';
    case STAMP_IMAGE = '343x248';
    case BRANDING_IMAGE = '777x295';

    public function getWidth(): int
    {
        return (int) explode('x', $this->value)[0];
    }

    public function getHeight(): int
    {
        return (int) explode('x', $this->value)[1];
    }

    public function getDimensions(): array
    {
        return [
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
        ];
    }

    public function getAspectRatio(): float
    {
        return $this->getWidth() / $this->getHeight();
    }

    public static function getFrontImageDimensions(): array
    {
        return self::FRONT_IMAGE->getDimensions();
    }

    public static function getStampImageDimensions(): array
    {
        return self::STAMP_IMAGE->getDimensions();
    }

    public static function getBrandingImageDimensions(): array
    {
        return self::BRANDING_IMAGE->getDimensions();
    }
}
