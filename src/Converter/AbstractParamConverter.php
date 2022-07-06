<?php

declare(strict_types=1);

namespace LogParser\Converter;

use LogParser\Converter\ParamConverterInterface as LogParserParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;

abstract class AbstractParamConverter implements ParamConverterInterface, LogParserParamConverterInterface
{
    public const CONVERTER_NAME = '';

    public function supports(ParamConverter $configuration): bool
    {
        return null !== $configuration->getClass() && static::CONVERTER_NAME === $configuration->getConverter();
    }
}
