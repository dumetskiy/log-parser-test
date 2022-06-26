<?php

declare(strict_types=1);

namespace LogParser;

use LogParser\DependencyInjection\Compiler\LogHandlerStackFactoryCompilerPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new LogHandlerStackFactoryCompilerPass());
    }
}
