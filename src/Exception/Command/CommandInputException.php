<?php

declare(strict_types=1);

namespace LogParser\Exception\Command;

use LogParser\Exception\LogParserException;

class CommandInputException extends LogParserException
{
    protected static string $defaultMessage = 'Command input error occurred';
}
