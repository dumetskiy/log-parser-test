<?php

declare(strict_types=1);

namespace LogParser\DTO\Api\Response\Error;

class ApiErrorResponseDTO
{
    public readonly int $errorsCount;

    public readonly bool $error;

    /**
     * @param ApiErrorInterface[] $errors
     */
    public function __construct(public readonly array $errors = [])
    {
        $this->errorsCount = count($this->errors);
        $this->error = true;
    }
}
