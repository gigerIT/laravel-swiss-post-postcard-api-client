<?php

namespace Gigerit\PostcardApi\DTOs\Address;

class RecipientAddress
{
    public function __construct(
        public readonly string $street,
        public readonly string $zip,
        public readonly string $city,
        public readonly string $country,
        public readonly ?string $title = null,
        public readonly ?string $lastname = null,
        public readonly ?string $firstname = null,
        public readonly ?string $company = null,
        public readonly ?string $houseNr = null,
        public readonly ?string $poBox = null,
        public readonly ?string $additionalAdrInfo = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            street: $data['street'],
            zip: $data['zip'],
            city: $data['city'],
            country: $data['country'],
            title: $data['title'] ?? null,
            lastname: $data['lastname'] ?? null,
            firstname: $data['firstname'] ?? null,
            company: $data['company'] ?? null,
            houseNr: $data['houseNr'] ?? null,
            poBox: $data['poBox'] ?? null,
            additionalAdrInfo: $data['additionalAdrInfo'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'lastname' => $this->lastname,
            'firstname' => $this->firstname,
            'company' => $this->company,
            'street' => $this->street,
            'houseNr' => $this->houseNr,
            'zip' => $this->zip,
            'city' => $this->city,
            'country' => $this->country,
            'poBox' => $this->poBox,
            'additionalAdrInfo' => $this->additionalAdrInfo,
        ], fn ($value) => $value !== null);
    }

    public function getFullName(): ?string
    {
        $name = '';

        if ($this->title) {
            $name .= $this->title.' ';
        }

        if ($this->firstname) {
            $name .= $this->firstname.' ';
        }

        if ($this->lastname) {
            $name .= $this->lastname;
        }

        $name = trim($name);

        return $name ?: $this->company;
    }

    public function getFullAddress(): string
    {
        $address = '';

        if ($this->poBox) {
            $address = "PO Box {$this->poBox}";
        } else {
            $address = $this->street;
            if ($this->houseNr) {
                $address .= " {$this->houseNr}";
            }
        }

        if ($this->additionalAdrInfo) {
            $address .= "\n{$this->additionalAdrInfo}";
        }

        $address .= "\n{$this->zip} {$this->city}\n{$this->country}";

        return $address;
    }
}
