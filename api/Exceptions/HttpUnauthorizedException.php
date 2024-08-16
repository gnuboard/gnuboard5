<?php

declare(strict_types=1);

namespace API\Exceptions;

use Slim\Exception\HttpSpecializedException;

/**
 * @OA\Response(
 *    response="401",
 *    description="인증 실패",
 *    @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 * )
 */
class HttpUnauthorizedException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = 401;

    /**
     * @var string
     */
    protected $message = 'Unauthorized.';

    protected string $title = '401 Unauthorized';
    protected string $description = 'The request requires valid user authentication.';
}
