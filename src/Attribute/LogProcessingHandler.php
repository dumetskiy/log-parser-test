<?php

declare(strict_types=1);

namespace LogParser\Attribute;

use LogParser\Enum\LogProcessingStrategy;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class LogProcessingHandler
{
    public function __construct(
        public readonly LogProcessingStrategy $logProcessingStrategy,
        public readonly int $executionOrder = 0
    ) {}
}
