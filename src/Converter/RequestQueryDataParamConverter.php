<?php

declare(strict_types=1);

namespace LogParser\Converter;

use LogParser\Exception\Converter\ParamConverterException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestQueryDataParamConverter extends AbstractParamConverter
{
    public const CONVERTER_NAME = 'log_parser.converter.query_data';

    /**
     * @param Serializer $serializer
     */
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {}

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        try {
            $object = $this->serializer->denormalize(
                data: $request->query->all(),
                type: $configuration->getClass(),
                context: [AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true]
            );

            $constraintViolationsListArgument = $configuration->getOptions()['constraintViolationsListArgument'] ?? null;

            if (null !== $constraintViolationsListArgument) {
                $constraintViolationsList = $this->validator->validate($object);
                $request->attributes->set($constraintViolationsListArgument, $constraintViolationsList);
            }

            $request->attributes->set($configuration->getName(), $object);

            return true;
        } catch (SerializerExceptionInterface) {
            throw ParamConverterException::create('Failed to process query data');
        }
    }
}
