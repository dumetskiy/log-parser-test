<?php

declare(strict_types=1);

namespace LogParser\DTO\Api\Response\Error;

class ConstraintViolationErrorDTO implements ApiErrorInterface
{
    public function __construct(
        public readonly string $message,
        public readonly ?string $source
    ) {}
}
