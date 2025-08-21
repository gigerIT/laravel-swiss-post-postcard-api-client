<?php

namespace Gigerit\PostcardApi\DTOs\Response;

class DefaultResponse
{
    public function __construct(
        public readonly string $cardKey,
        public readonly ?string $successMessage = null,
        /** @var CodeMessage[] */
        public readonly array $errors = [],
        /** @var CodeMessage[] */
        public readonly array $warnings = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cardKey: $data['cardKey'],
            successMessage: $data['successMessage'] ?? null,
            errors: array_map(
                fn (array $error) => CodeMessage::fromArray($error),
                $data['errors'] ?? []
            ),
            warnings: array_map(
                fn (array $warning) => CodeMessage::fromArray($warning),
                $data['warnings'] ?? []
            )
        );
    }

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    public function hasWarnings(): bool
    {
        return ! empty($this->warnings);
    }

    public function getErrorMessages(): array
    {
        return array_map(fn (CodeMessage $error) => $error->description, $this->errors);
    }

    public function getWarningMessages(): array
    {
        return array_map(fn (CodeMessage $warning) => $warning->description, $this->warnings);
    }
}
