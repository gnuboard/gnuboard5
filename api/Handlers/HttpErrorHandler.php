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
use API\Exceptions\ValidateException;
use Firebase\JWT\ExpiredException;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\HttpException;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use DomainException;
use InvalidArgumentException;
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
        $status_code = 500;
        $type = self::SERVER_ERROR;
        $description = 'An internal error has occurred while processing your request.';

        $is_http_exception = $exception instanceof HttpException;

        if ($is_http_exception) {
            $status_code = $exception->getCode();
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
            } else {
                $type = '';
            }

            return $this->respondWithJson($type, $description, $status_code);
        }

        if ($exception instanceof ValidateException) {
            $status_code = $exception->getCode();
            $description = $exception->getMessage();
            $type = self::UNPROCESSABLE_ENTITY;
            return $this->respondWithJson($type, $description, $status_code);
        }

        if ($exception instanceof DbConnectException) {
            $status_code = 500;
            $type = 'DB Connection Error';
            $description = $exception->getMessage();

            return $this->respondWithJson($type, $description, $status_code);
        }

        if ($exception instanceof PDOException) {
            $status_code = 500;
            $type = 'DB Error';
            $description = 'DB operator error';
            if ($this->displayErrorDetails) {
                $description .= ' : ' . $exception->getMessage();
            }

            return $this->respondWithJson($type, $description, $status_code);
        }

        // JWT 인증만료
        if ($exception instanceof ExpiredException) {
            $status_code = 401;
            $type = 'JWT Token Expired';
            $description = $exception->getMessage();
            return $this->respondWithJson($type, $description, $status_code);
        }

        // JWT 인증만료를 제외한 예외
        if ($exception instanceof InvalidArgumentException
            || $exception instanceof DomainException
            || $exception instanceof UnexpectedValueException
        ) {
            $status_code = 400;
            $type = 'JWT Token error';
            $description = 'jwt token error: ' . $exception->getMessage();
            return $this->respondWithJson($type, $description, $status_code);
        }

        // 나머지 오류 
        return $this->respondWithJson($type, $description, $status_code);
    }

    /**
     * 오류 응답 생성
     * @param string $type 오류 유형
     * @param string $description G5_DEBUG 환경변수에 따라 오류 설명을 노출할지 결정된다.
     * @param int $status_code HTTP 상태 코드
     * @return ResponseInterface
     */
    private function respondWithJson(string $type, string $description, int $status_code = 200)
    {
        if ($status_code >= 500) {
            $description = G5_DEBUG ? $description : 'Error occurred.';
        }

        $error_info = [
            'statusCode' => $status_code,
            'error' => [
                'type' => $type,
                'description' => $description,
            ],
        ];
        $response = $this->responseFactory->createResponse($status_code);
        $payload = json_encode($error_info, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status_code);
    }


}
