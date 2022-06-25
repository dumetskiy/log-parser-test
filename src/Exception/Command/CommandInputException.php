<?php

declare(strict_types=1);

namespace LogParser\Exception\Command;

use LogParser\Exception\LogParserException;

class CommandInputException extends LogParserException
{
    private static string $defaultMessage = 'Command input error occurred';
}
