<?php

declare(strict_types=1);

namespace LogParser\DTO\Api\Response;

class LogCountResponseDTO
{
    public function __construct(public readonly int $counter) {}
}
