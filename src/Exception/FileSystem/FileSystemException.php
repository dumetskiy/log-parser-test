<?php

declare(strict_types=1);

namespace LogParser\Exception\FileSystem;

use LogParser\Exception\LogParserException;

class FileSystemException extends LogParserException
{
    protected static string $defaultMessage = 'Command input error occurred';
}
