<?php

declare(strict_types=1);

namespace LogParser\DTO\Api\Response\Error;

class ErrorDTO implements ApiErrorInterface
{
    public function __construct(
        public readonly string $message,
        public readonly int $code
    ) {}
}
