<?php

declare(strict_types=1);

namespace LogParser\DependencyInjection\Compiler;

use LogParser\Attribute\LogProcessingHandler;
use LogParser\Exception\LogParserException;
use LogParser\Factory\Stack\LogHandlerStackFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class LogHandlerStackFactoryCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $handlerStackFactoryDefinition = $container->getDefinition(LogHandlerStackFactory::class);

        if (!$handlerStackFactoryDefinition instanceof Definition) {
            throw LogParserException::create(sprintf(
                'Failed to configure handler stacks as "%s" is not configured as a service',
                LogHandlerStackFactory::class
            ));
        }

        foreach ($container->findTaggedServiceIds('log_parser.processing.handler') as $handlerId => $tags) {
            $handlerDefinition = $container->getDefinition($handlerId);
            /** @phpstan-ignore-next-line */
            $handlerClassReflection = new \ReflectionClass($handlerDefinition->getClass());

            foreach ($handlerClassReflection->getAttributes() as $attributeReflection) {
                $attribute = $attributeReflection->newInstance();

                if (!$attribute instanceof LogProcessingHandler) {
                    continue;
                }

                $handlerStackFactoryDefinition->addMethodCall('pushStackHandler', [
                    new Reference($handlerId, ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE),
                    $attribute->executionOrder,
                    $attribute->logProcessingStrategy,
                ]);
            }
        }
    }
}
