<?php

declare(strict_types=1);

namespace LogParser\Exception;

class LogParserException extends \RuntimeException
{
    protected static string $defaultMessage = 'Log parser error occurred';

    final public function __construct(?string $message = null, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ?? static::$defaultMessage, $code, $previous);
    }

    final public static function create(?string $message = null, int $code = 0, \Throwable $previous = null): static
    {
        return new static($message, $code, $previous);
    }
}
