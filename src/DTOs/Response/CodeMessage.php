<?php

namespace Gigerit\PostcardApi\DTOs\Response;

class CodeMessage
{
    public function __construct(
        public readonly int $code,
        public readonly string $description
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'],
            description: $data['description']
        );
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'description' => $this->description,
        ];
    }
}
