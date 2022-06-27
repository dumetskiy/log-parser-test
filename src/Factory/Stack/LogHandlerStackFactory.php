<?php

declare(strict_types=1);

namespace LogParser\Factory\Stack;

use LogParser\Enum\LogProcessingStrategy;
use LogParser\Handler\LogProcessing\LogProcessingHandlerInterface;
use LogParser\Handler\Stack\LogHandlerStack;

class LogHandlerStackFactory
{
    /**
     * @var array<string, LogHandlerStack> the strategy-handlerStack map
     */
    private array $handlerStacks = [];

    public function pushStackHandler(
        LogProcessingHandlerInterface $handler,
        int $order,
        LogProcessingStrategy $processingStrategy
    ): void {
        if (!isset($this->handlerStacks[$processingStrategy->value])) {
            $this->handlerStacks[$processingStrategy->value] = new LogHandlerStack($processingStrategy);
        }

        $this->handlerStacks[$processingStrategy->value]->pushHandler($handler, $order);
    }

    public function getForStrategy(LogProcessingStrategy $processingStrategy): LogHandlerStack
    {
        return $this->handlerStacks[$processingStrategy->value] ?? new LogHandlerStack($processingStrategy);
    }
}
