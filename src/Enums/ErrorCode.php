<?php

namespace Gigerit\PostcardApi\Enums;

enum ErrorCode: int
{
    // Missing required fields
    case NAME_REQUIRED = 1000;
    case FIRSTNAME_REQUIRED = 1001;
    case STREET_REQUIRED = 1002;
    case ZIP_REQUIRED = 1003;
    case CITY_REQUIRED = 1004;
    case NAMES_COMBINATION_REQUIRED = 1005;
    case RECIPIENT_ADDRESS_REQUIRED = 1006;
    case FRONT_IMAGE_REQUIRED = 1007;
    case SENDER_ADDRESS_REQUIRED = 1008;

    // Data validation messages
    case TITLE_LENGTH_INVALID = 1100;
    case NAME_LENGTH_INVALID = 1101;
    case FIRSTNAME_LENGTH_INVALID = 1102;
    case COMPANY_LENGTH_INVALID = 1103;
    case STREET_LENGTH_INVALID = 1104;
    case HOUSENR_LENGTH_INVALID = 1105;
    case ZIP_LENGTH_INVALID = 1106;
    case CITY_LENGTH_INVALID = 1107;
    case POBOX_LENGTH_INVALID = 1108;
    case SENDER_TEXT_LENGTH_INVALID = 1109;
    case BRANDING_TEXT_LENGTH_INVALID = 1110;
    case BRANDING_TEXT_COLOR_LENGTH_INVALID = 1111;
    case BRANDING_BG_COLOR_LENGTH_INVALID = 1112;
    case BRANDING_ENCODED_TEXT_LENGTH_INVALID = 1113;

    // Logical data validation messages
    case NAMES_COMBINATION_NOT_ALLOWED = 1200;
    case NAME_OVERRIDE_NOT_ALLOWED = 1201;
    case FIRSTNAME_OVERRIDE_NOT_ALLOWED = 1202;
    case COMPANY_OVERRIDE_NOT_ALLOWED = 1203;
    case STREET_OVERRIDE_NOT_ALLOWED = 1204;
    case HOUSENR_OVERRIDE_NOT_ALLOWED = 1205;
    case ZIP_OVERRIDE_NOT_ALLOWED = 1206;
    case CITY_OVERRIDE_NOT_ALLOWED = 1207;
    case POBOX_OVERRIDE_NOT_ALLOWED = 1208;
    case TITLE_OVERRIDE_NOT_ALLOWED = 1209;
    case BRANDING_TEXT_OVERRIDE_NOT_ALLOWED = 1210;
    case BRANDING_TEXT_COLOR_OVERRIDE_NOT_ALLOWED = 1211;
    case BRANDING_BG_COLOR_OVERRIDE_NOT_ALLOWED = 1212;
    case BRANDING_QR_TEXT_OVERRIDE_NOT_ALLOWED = 1213;

    // Text encoding messages
    case SENDER_TEXT_INVALID_ENCODING = 1300;
    case NAME_INVALID_ENCODING = 1301;
    case FIRSTNAME_INVALID_ENCODING = 1302;
    case STREET_INVALID_ENCODING = 1303;
    case CITY_INVALID_ENCODING = 1304;
    case TITLE_INVALID_ENCODING = 1305;
    case COMPANY_INVALID_ENCODING = 1306;
    case BRANDING_TEXT_INVALID_ENCODING = 1307;
    case BRANDING_QR_INVALID_ENCODING = 1308;

    // Logical branding block validation messages
    case BRANDING_COMBINATION_NOT_ALLOWED = 1400;
    case TEXT_COLOR_INVALID = 1401;
    case BG_COLOR_INVALID = 1402;
    case BRANDING_LINK_INVALID = 1403;

    // Campaign messages
    case CAMPAIGN_QUOTA_EXCEEDED = 2000;
    case CAMPAIGN_EXPIRED = 2010;
    case CAMPAIGN_NOT_STARTED = 2020;
    case CAMPAIGN_NOT_ACTIVE = 2030;
    case CAMPAIGN_NOT_FOUND = 4000;
    case CAMPAIGN_CONFIG_NOT_FOUND = 4001;
    case BRANDING_NOT_FOUND = 4002;
    case POSTCARD_NOT_FOUND = 4003;

    // General messages
    case ENCODING_VIOLATION = 5000;
    case FILE_FORMAT_NOT_SUPPORTED = 5010;
    case BAD_RESOLUTION = 5020;
    case PERIPHERAL_SYSTEM_NOT_AVAILABLE = 5050;

    // Process messages
    case POSTCARD_ALREADY_APPROVED = 6000;

    public function getDescription(): string
    {
        return match ($this) {
            self::NAME_REQUIRED => 'The name is required',
            self::FIRSTNAME_REQUIRED => 'The first name is required',
            self::STREET_REQUIRED => 'The street is required',
            self::ZIP_REQUIRED => 'The zip is required',
            self::CITY_REQUIRED => 'The city is required',
            self::NAMES_COMBINATION_REQUIRED => 'Name/ first name or company is required',
            self::RECIPIENT_ADDRESS_REQUIRED => 'Recipient address is required',
            self::FRONT_IMAGE_REQUIRED => 'Front image is required',
            self::SENDER_ADDRESS_REQUIRED => 'Sender address is required',
            self::TITLE_LENGTH_INVALID => 'The length of title is invalid',
            self::NAME_LENGTH_INVALID => 'The length of name is invalid',
            self::FIRSTNAME_LENGTH_INVALID => 'The length of first name is invalid',
            self::COMPANY_LENGTH_INVALID => 'The length of company is invalid',
            self::STREET_LENGTH_INVALID => 'The length of street is invalid',
            self::HOUSENR_LENGTH_INVALID => 'The length of houseNumber is invalid',
            self::ZIP_LENGTH_INVALID => 'The length of zip is invalid',
            self::CITY_LENGTH_INVALID => 'The length of city is invalid',
            self::POBOX_LENGTH_INVALID => 'The length of poBox is invalid',
            self::SENDER_TEXT_LENGTH_INVALID => 'The length of sender text is invalid',
            self::BRANDING_TEXT_LENGTH_INVALID => 'The length of branding text is invalid',
            self::BRANDING_TEXT_COLOR_LENGTH_INVALID => 'The length of branding text color is invalid',
            self::BRANDING_BG_COLOR_LENGTH_INVALID => 'The length of branding background color is invalid',
            self::BRANDING_ENCODED_TEXT_LENGTH_INVALID => 'The length of branding encoded text is invalid',
            self::CAMPAIGN_QUOTA_EXCEEDED => 'Campaign quota exceeded',
            self::CAMPAIGN_EXPIRED => 'The end date of campaign is reached',
            self::CAMPAIGN_NOT_STARTED => 'The campaign has not yet started',
            self::CAMPAIGN_NOT_ACTIVE => 'The campaign is not active',
            self::CAMPAIGN_NOT_FOUND => 'Campaign not found',
            self::CAMPAIGN_CONFIG_NOT_FOUND => 'Campaign configuration not found',
            self::BRANDING_NOT_FOUND => 'Branding for campaign not found',
            self::POSTCARD_NOT_FOUND => 'Postcard not found',
            self::POSTCARD_ALREADY_APPROVED => 'The given postcard is already approved',
            default => 'Unknown error',
        };
    }

    public function isError(): bool
    {
        return ! $this->isWarning();
    }

    public function isWarning(): bool
    {
        return in_array($this, [
            self::BAD_RESOLUTION,
            self::SENDER_TEXT_LENGTH_INVALID,
            self::BRANDING_TEXT_LENGTH_INVALID,
            self::ENCODING_VIOLATION,
        ]);
    }
}
