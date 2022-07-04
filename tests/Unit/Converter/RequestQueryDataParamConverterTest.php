<?php

declare(strict_types=1);

namespace LogParser\Tests\Unit\Converter;

use LogParser\Converter\RequestQueryDataParamConverter;
use LogParser\DTO\Api\Request\LogCountRequestDTO;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\RecursiveValidator;

class RequestQueryDataParamConverterTest extends TestCase
{
    private MockObject|Serializer $serializerMock;

    private MockObject|ParamConverter $configurationMock;

    private MockObject|RecursiveValidator $validatorMock;

    private RequestQueryDataParamConverter $testInstance;

    private LogCountRequestDTO $logCountRequestDTO;

    private ConstraintViolationList $constraintViolationList;

    private Request $request;

    protected function setUp(): void
    {
        $this->logCountRequestDTO = new LogCountRequestDTO();
        $this->request = new Request();
        $this->constraintViolationList = new ConstraintViolationList();
        $this->serializerMock = $this->createMock(Serializer::class);
        $this->configurationMock = $this->createMock(ParamConverter::class);
        $this->validatorMock = $this->createMock(RecursiveValidator::class);

        $this
            ->configurationMock
            ->method('getClass')
            ->willReturn(LogCountRequestDTO::class);

        $this
            ->serializerMock
            ->method('denormalize')
            ->willReturn($this->logCountRequestDTO);

        $this
            ->configurationMock
            ->method('getName')
            ->willReturn('argumentName');

        $this
            ->validatorMock
            ->method('validate')
            ->with($this->logCountRequestDTO)
            ->willReturn($this->constraintViolationList);

        $this->testInstance = new RequestQueryDataParamConverter($this->serializerMock, $this->validatorMock);
    }

    public function testPlainApply(): void
    {
        $this->configurationMock->method('getOptions')->willReturn([]);
        $this->validatorMock->expects($this->never())->method('validate');
        $this->testInstance->apply($this->request, $this->configurationMock);
        $this->assertEquals($this->request->attributes->get('argumentName'), $this->logCountRequestDTO);
    }

    public function testApplyWithConstraintViolationsPropagation(): void
    {
        $this->configurationMock->method('getOptions')->willReturn(['constraintViolationsListArgument' => 'argName']);
        $this->validatorMock->expects($this->once())->method('validate');
        $this->testInstance->apply($this->request, $this->configurationMock);
        $this->assertEquals($this->request->attributes->get('argumentName'), $this->logCountRequestDTO);
        $this->assertEquals($this->request->attributes->get('argName'), $this->constraintViolationList);
    }

    public function testApplyWithSerializerException(): void
    {
        $this->validatorMock->expects($this->never())->method('validate');
        $this->serializerMock->method('denormalize')->willThrowException(new RuntimeException('test'));
        $this->expectExceptionMessage('Failed to process query data');
        $this->testInstance->apply($this->request, $this->configurationMock);
    }

    public function testSupportsSupportedConfiguration(): void
    {
        $this
            ->configurationMock
            ->expects($this->once())
            ->method('getConverter')
            ->willReturn(RequestQueryDataParamConverter::CONVERTER_NAME);

        $this->assertTrue($this->testInstance->supports($this->configurationMock));
    }

    public function testSupportsUnsupportedConfiguration(): void
    {
        $this
            ->configurationMock
            ->expects($this->once())
            ->method('getConverter')
            ->willReturn('');

        $this->assertFalse($this->testInstance->supports($this->configurationMock));
    }
}
