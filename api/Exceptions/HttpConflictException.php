<?php

declare(strict_types=1);

namespace API\Exceptions;

use Slim\Exception\HttpSpecializedException;

/**
 * @OA\Response(
 *    response="409",
 *    description="중복된 데이터",
 *    @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 * )
 */
class HttpConflictException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = 409;

    /**
     * @var string
     */
    protected $message = 'Conflict.';

    protected string $title = '409 Conflict';
    protected string $description = 'The request could not be completed due to a conflict with the current state of the target resource.';
}
