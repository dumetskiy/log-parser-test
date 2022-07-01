<?php

declare(strict_types=1);

namespace LogParser\ValueObject;

class ApiResponse
{
    private const RESPONSE_CODE_GROUP_SUCCESS = 200;
    private const RESPONSE_CODE_GROUP_RANGE = 100;

    /**
     * @var array<string, mixed>
     */
    private array $data;

    /**
     * @param array<array<string>>|null $headers
     */
    public function __construct(
        public readonly int $code,
        public readonly ?string $content,
        public readonly ?array $headers,
    ) {
        $this->data = $this->content ? json_decode($this->content, true) : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    public function isSuccessful(): bool
    {
        return self::RESPONSE_CODE_GROUP_SUCCESS === $this->getResponseCodeGroup();
    }

    private function getResponseCodeGroup(): int
    {
        return (int) floor($this->code / self::RESPONSE_CODE_GROUP_RANGE) * self::RESPONSE_CODE_GROUP_RANGE;
    }
}
