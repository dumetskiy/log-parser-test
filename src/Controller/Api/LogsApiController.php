<?php

declare(strict_types=1);

namespace LogParser\Controller\Api;

use LogParser\Converter\RequestQueryDataParamConverter;
use LogParser\DTO\Api\Request\LogCountRequestDTO;
use LogParser\DTO\Api\Request\LogCountResponseDTO;
use LogParser\Exception\LogParserException;
use LogParser\Manager\ElasticManager;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolationList;

#[Route(name: 'logs_api_', format: 'json')]
class LogsApiController extends BaseApiController
{
    public function __construct(private readonly ElasticManager $elasticManager)
    {}

    #[Route(
        path: '/count',
        name: 'count',
        methods: [Request::METHOD_GET]
    )]
    #[ParamConverter(
        'logCountRequestDTO',
        class: LogCountRequestDTO::class,
        converter: RequestQueryDataParamConverter::CONVERTER_NAME,
        options: ['constraintViolationsListArgument' => 'constraintViolationList']
    )]
    #[
        OA\Parameter(
            name: 'serviceNames[]',
            description: 'array of service names',
            required: false,
            in: 'query',
            style: 'form',
            explode: true,
            schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string'))
        ),
        OA\Parameter(
            name: 'startDate',
            description: 'start date',
            required: false,
            in: 'query',
            style: 'form',
            explode: true,
            schema: new OA\Schema(type: 'string', format: 'dateTime'),
        ),
        OA\Parameter(
            name: 'endDate',
            description: 'end date',
            required: false,
            in: 'query',
            style: 'form',
            explode: true,
            schema: new OA\Schema(type: 'string', format: 'dateTime'),
        ),
        OA\Parameter(
            name: 'statusCode',
            description: 'filter on request status code',
            required: false,
            in: 'query',
            style: 'form',
            explode: true,
            schema: new OA\Schema(type: 'integer'),
        ),
        OA\Response(
            response: Response::HTTP_OK,
            description: 'count of matching results',
            content: new OA\JsonContent(type: 'array', items: new OA\Items(type: 'string'))
        ),
        OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'bad input parameter'
        ),
        OA\Tag('analytics')
    ]
    public function count(LogCountRequestDTO $logCountRequestDTO, ConstraintViolationList $constraintViolationList): Response
    {
        try {
            if ($constraintViolationList->count()) {
                // Outputting request validation errors if there are any
                return $this->handleView($this->buildConstraintViolationErrorView($constraintViolationList));
            }

            $elasticCountResponse = $this->elasticManager->getLogsCount($logCountRequestDTO);
            $responseView = $this->view((new LogCountResponseDTO($elasticCountResponse->count)), Response::HTTP_OK);

            return $this->handleView($responseView);
        } catch (LogParserException $exception) {
            return $this->handleView($this->buildErrorViewFromException($exception));
        } catch (\Throwable) {
            return $this->handleView($this->buildErrorViewFromException(new LogParserException(
                'Failed to perform logs count operation'
            )));
        }
    }
}
