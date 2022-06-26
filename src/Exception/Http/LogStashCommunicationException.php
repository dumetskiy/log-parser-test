<?php

declare(strict_types=1);

namespace LogParser\Exception\Http;

use LogParser\Exception\LogParserException;

class LogStashCommunicationException extends LogParserException
{
    protected static string $defaultMessage = 'An error occurred when communicating with LogStash';
}
