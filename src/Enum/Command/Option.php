<?php

declare(strict_types=1);

namespace LogParser\Enum\Command;

enum Option: string
{
    case LOG_PROCESSING_STRATEGY = 'strategy';
    case OFFSET_LINES = 'offset';
}