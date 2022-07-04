<?php

declare(strict_types=1);

namespace LogParser\Validator\Request;

use LogParser\DTO\Api\Request\LogCountRequestDTO;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class LogCountRequestValidator
{
    public static function isDateRangeValid(
        LogCountRequestDTO $logCountRequest,
        ExecutionContextInterface $executionContext
    ): void {
        if (
            !$logCountRequest->startDate instanceof \DateTimeImmutable
            || !$logCountRequest->endDate instanceof \DateTimeImmutable
        ) {
            return;
        }

        if ($logCountRequest->startDate > $logCountRequest->endDate) {
            $executionContext->addViolation('Start date should not be greater then the end date');
        }
    }

    public static function isStatusCodeValid(
        ?int $statusCode,
        ExecutionContextInterface $executionContext
    ): void {
        if (null === $statusCode || isset(Response::$statusTexts[$statusCode])) {
            return;
        }

        $executionContext->addViolation(
            'The value provided for "statusCode" is not a valid HTTP response code'
        );
    }
}
