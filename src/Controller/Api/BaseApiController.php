<?php

declare(strict_types=1);

namespace LogParser\Controller\Api;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use LogParser\DTO\Api\Response\Error\ApiErrorResponseDTO;
use LogParser\DTO\Api\Response\Error\ConstraintViolationErrorDTO;
use LogParser\DTO\Api\Response\Error\ErrorDTO;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class BaseApiController extends AbstractFOSRestController
{
    protected function buildConstraintViolationErrorView(ConstraintViolationList $constraintViolationList): View
    {
        $constraintViolationErrors = [];

        foreach ($constraintViolationList->getIterator() as $constraintViolation) {
            /* @var ConstraintViolationInterface $constraintViolation */
            $constraintViolationErrors[] = new ConstraintViolationErrorDTO(
                (string) $constraintViolation->getMessage(),
                $constraintViolation->getPropertyPath(),
            );
        }

        $errorResponse = new ApiErrorResponseDTO(
            errors: $constraintViolationErrors
        );

        return $this->view($errorResponse, Response::HTTP_BAD_REQUEST);
    }

    protected function buildErrorViewFromException(
        \Exception $exception,
        int $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR
    ): View {
        $constraintViolationErrors = [];

        $errorResponse = new ApiErrorResponseDTO(
            errors: [new ErrorDTO($exception->getMessage(), $exception->getCode())]
        );

        return $this->view($errorResponse, $responseCode);
    }
}
