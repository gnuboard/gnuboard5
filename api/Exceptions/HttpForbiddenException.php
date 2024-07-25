<?php

declare(strict_types=1);

namespace API\Exceptions;

use Slim\Exception\HttpSpecializedException;

/**
 * @OA\Response(
 *    response="403",
 *    description="권한 없음",
 *    @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 * )
 */
class HttpForbiddenException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = 403;

    /**
     * @var string
     */
    protected $message = 'Forbidden.';

    protected string $title = '403 Forbidden';
    protected string $description = 'You are not permitted to perform the requested operation.';
}
