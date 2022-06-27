<?php

declare(strict_types=1);

namespace LogParser\Exception\Http;

use LogParser\Exception\LogParserException;
use Symfony\Component\HttpFoundation\Response;

class ApiClientException extends LogParserException
{
    protected static string $defaultMessage = 'An error occurred when calling external API';
}
