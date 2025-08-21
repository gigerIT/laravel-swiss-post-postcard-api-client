<?php

namespace Gigerit\PostcardApi\DTOs\Response;

use DateTime;

class State
{
    public function __construct(
        public readonly string $state,
        public readonly DateTime $date
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            state: $data['state'],
            date: new DateTime($data['date'])
        );
    }

    public function toArray(): array
    {
        return [
            'state' => $this->state,
            'date' => $this->date->format('Y-m-d'),
        ];
    }
}
