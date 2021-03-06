<?php

declare(strict_types=1);

namespace LogParser\Exception\Configuration;

use LogParser\Exception\LogParserException;

class ConfigurationException extends LogParserException
{
    protected static string $defaultMessage = 'Configuration error occurred';
}
