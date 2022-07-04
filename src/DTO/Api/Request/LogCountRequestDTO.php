<?php

declare(strict_types=1);

namespace LogParser\DTO\Api\Request;

use LogParser\Validator\Request\LogCountRequestValidator;
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Callback([LogCountRequestValidator::class, 'isDateRangeValid'])]
class LogCountRequestDTO
{
    /**
     * @var string[]
     */
    #[Assert\Unique(message: 'Duplicate service names provided')]
    public ?array $serviceNames = null;

    public ?\DateTimeImmutable $startDate = null;

    public ?\DateTimeImmutable $endDate = null;

    #[Assert\Callback([LogCountRequestValidator::class, 'isStatusCodeValid'])]
    public ?int $statusCode = null;
}
