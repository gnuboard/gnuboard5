<?php

namespace API\Handlers;

use API\Exceptions\DbConnectException;
use API\Exceptions\HttpBadRequestException;
use API\Exceptions\HttpConflictException;
use API\Exceptions\HttpForbiddenException;
use API\Exceptions\HttpMethodNotAllowedException;
use API\Exceptions\HttpNotFoundException;
use API\Exceptions\HttpNotImplementedException;
use API\Exceptions\HttpUnauthorizedException;
use API\Exceptions\HttpUnprocessableEntityException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpException;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use DomainException;
use Exception;
use InvalidArgumentException;
use Throwable;
use UnexpectedValueException;
use PDOException;

/**
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     @OA\Property(
 *         property="statusCode",
 *         type="integer",
 *         description="HTTP 상태 코드"
 *     ),
 *     @OA\Property(
 *         property="error",
 *         type="object",
 *         @OA\Property(
 *             property="type",
 *             type="string",
 *             description="오류 유형"
 *         ),
 *         @OA\Property(
 *             property="description",
 *             type="string",
 *             description="오류 설명"
 *         )
 *     )
 * )
 */
class HttpErrorHandler extends SlimErrorHandler
{
    public const BAD_REQUEST = 'Bad Request';
    public const UNAUTHENTICATED = 'Unauthenticated';
    public const FORBIDDEN = 'Forbidden';
    public const RESOURCE_NOT_FOUND = 'Resource Not Found';
    public const NOT_ALLOWED = 'Not Allowed';
    public const CONFLICT = 'Conflict';
    public const UNPROCESSABLE_ENTITY = 'Unprocessable Entity';
    public const SERVER_ERROR = 'Server Error';
    public const NOT_IMPLEMENTED = 'Not Implemented';
    public const INSUFFICIENT_PRIVILEGES = 'Insufficient Privileges';

    protected function respond(): ResponseInterface
    {
        $exception = $this->exception;
        $statusCode = 500;
        $type = self::SERVER_ERROR;
        $description = 'An internal error has occurred while processing your request.';

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getCode();
            $description = $exception->getMessage();

            if ($exception instanceof HttpBadRequestException) {
                $type = self::BAD_REQUEST;
            } elseif ($exception instanceof HttpUnauthorizedException) {
                $type = self::UNAUTHENTICATED;
            } elseif ($exception instanceof HttpForbiddenException) {
                $type = self::FORBIDDEN;
            } elseif ($exception instanceof HttpNotFoundException) {
                $type = self::RESOURCE_NOT_FOUND;
            } elseif ($exception instanceof HttpMethodNotAllowedException) {
                $type = self::NOT_ALLOWED;
            } elseif ($exception instanceof HttpConflictException) {
                $type = self::CONFLICT;
            } elseif ($exception instanceof HttpUnprocessableEntityException) {
                $type = self::UNPROCESSABLE_ENTITY;
            } elseif ($exception instanceof HttpNotImplementedException) {
                $type = self::NOT_IMPLEMENTED;
            }
        }

        if (
            !($exception instanceof HttpException)
            && ($exception instanceof Exception || $exception instanceof Throwable)
            && $this->displayErrorDetails
        ) {
            $description = $exception->getMessage();
        }

        if ($exception instanceof DbConnectException) {
            $statusCode = 500;
            $description = $exception->getMessage();
        }

        if ($exception instanceof PDOException) {
            $statusCode = 500;
            $description = 'DB operator error';
            if ($this->displayErrorDetails) {
                $description .= " : " . $exception->getMessage();
            }
        }

        // Add JWT exceptions
        if (
            $exception instanceof InvalidArgumentException
            || $exception instanceof DomainException
            || $exception instanceof UnexpectedValueException
            || $exception instanceof SignatureInvalidException
            || $exception instanceof BeforeValidException
            || $exception instanceof ExpiredException
        ) {
            $statusCode = 401;
            $type = self::UNAUTHENTICATED;
            $description = $exception->getMessage();
        }

        $error = [
            'statusCode' => $statusCode,
            'error' => [
                'type' => $type,
                'description' => $description,
            ],
        ];

        $payload = json_encode($error, JSON_UNESCAPED_UNICODE);

        $response = $this->responseFactory->createResponse($statusCode);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
