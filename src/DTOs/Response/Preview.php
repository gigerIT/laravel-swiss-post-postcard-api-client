<?php

namespace Gigerit\PostcardApi\DTOs\Response;

class Preview
{
    public function __construct(
        public readonly string $cardKey,
        public readonly string $fileType,
        public readonly string $encoding,
        public readonly string $side,
        public readonly string $imagedata,
        /** @var CodeMessage[] */
        public readonly array $errors = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            cardKey: $data['cardKey'],
            fileType: $data['fileType'],
            encoding: $data['encoding'],
            side: $data['side'],
            imagedata: $data['imagedata'],
            errors: array_map(
                fn (array $error) => CodeMessage::fromArray($error),
                $data['errors'] ?? []
            )
        );
    }

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    public function getErrorMessages(): array
    {
        return array_map(fn (CodeMessage $error) => $error->description, $this->errors);
    }

    public function getDecodedImage(): string
    {
        return base64_decode($this->imagedata);
    }
}
