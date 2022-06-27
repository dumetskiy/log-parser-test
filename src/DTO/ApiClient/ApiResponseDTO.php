<?php

declare(strict_types=1);

namespace LogParser\DTO\ApiClient;

class ApiResponseDTO
{
    private ?array $data = null;

    public function __construct(
        public readonly int $code,
        public readonly ?string $content,
        public readonly ?array $headers,
    ) {
        $this->data = json_decode($this->content, true);
    }

    public function getData(): ?array
    {
        return $this->data;
    }
}
