<?php

declare(strict_types=1);

namespace LogParser\Exception\Http;

class LogStashCommunicationException extends ApiClientException
{
    protected static string $defaultMessage = 'An error occurred when communicating with LogStash';
}
