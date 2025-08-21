<?php

namespace Gigerit\PostcardApi\DTOs\Response;

class PostcardStateResponse
{
    public function __construct(
        public readonly string $cardKey,
        public readonly State $state,
        /** @var CodeMessage[] */
        public readonly array $warnings = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cardKey: $data['cardKey'],
            state: State::fromArray($data['state']),
            warnings: array_map(
                fn (array $warning) => CodeMessage::fromArray($warning),
                $data['warnings'] ?? []
            )
        );
    }

    public function hasWarnings(): bool
    {
        return ! empty($this->warnings);
    }

    public function getWarningMessages(): array
    {
        return array_map(fn (CodeMessage $warning) => $warning->description, $this->warnings);
    }
}
