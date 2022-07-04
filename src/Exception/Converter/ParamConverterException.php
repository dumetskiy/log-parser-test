<?php

declare(strict_types=1);

namespace LogParser\Exception\Converter;

use LogParser\Exception\LogParserException;

class ParamConverterException extends LogParserException
{
    protected static string $defaultMessage = 'Param converter error occurred';
}
