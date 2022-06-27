<?php

declare(strict_types=1);

namespace LogParser\Handler\Stack;

use LogParser\Enum\LogProcessingStrategy;
use LogParser\Exception\Configuration\LogParserConfigurationException;
use LogParser\Handler\LogProcessing\LogProcessingHandlerInterface;

/**
 * @implements \IteratorAggregate<LogProcessingHandlerInterface>
 */
class LogHandlerStack implements \IteratorAggregate, \Countable
{
    /**
     * @param LogProcessingHandlerInterface[] $handlers
     */
    public function __construct(
        readonly private LogProcessingStrategy $processingStrategy,
        private array $handlers = []
    ) {}

    public function pushHandler(LogProcessingHandlerInterface $handler, int $order): void
    {
        if (isset($this->handlers[$order])) {
            throw LogParserConfigurationException::create(sprintf(
                'Duplicate handler order %d for stack "%s" [%s | %s]',
                $order,
                $this->processingStrategy->value,
                get_class($handler),
                get_class($this->handlers[$order])
            ));
        }

        $handlers = $this->handlers;
        $handlers[$order] = $handler;
        ksort($handlers);
        $this->handlers = $handlers;
    }

    public function getIterator(): \Traversable {
        return new \ArrayIterator($this->handlers);
    }

    public function count(): int
    {
        return count($this->handlers);
    }
}
