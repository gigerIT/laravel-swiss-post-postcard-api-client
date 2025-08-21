<?php

namespace Gigerit\PostcardApi\DTOs\Address;

class SenderAddress
{
    public function __construct(
        public readonly string $street,
        public readonly string $zip,
        public readonly string $city,
        public readonly ?string $lastname = null,
        public readonly ?string $firstname = null,
        public readonly ?string $company = null,
        public readonly ?string $houseNr = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            street: $data['street'],
            zip: $data['zip'],
            city: $data['city'],
            lastname: $data['lastname'] ?? null,
            firstname: $data['firstname'] ?? null,
            company: $data['company'] ?? null,
            houseNr: $data['houseNr'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'lastname' => $this->lastname,
            'firstname' => $this->firstname,
            'company' => $this->company,
            'street' => $this->street,
            'houseNr' => $this->houseNr,
            'zip' => $this->zip,
            'city' => $this->city,
        ], fn ($value) => $value !== null);
    }

    public function getFullName(): ?string
    {
        if ($this->firstname && $this->lastname) {
            return trim("{$this->firstname} {$this->lastname}");
        }

        return $this->lastname ?: $this->firstname ?: $this->company;
    }

    public function getFullAddress(): string
    {
        $address = $this->street;
        if ($this->houseNr) {
            $address .= " {$this->houseNr}";
        }
        $address .= ", {$this->zip} {$this->city}";

        return $address;
    }
}
