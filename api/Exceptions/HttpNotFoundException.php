<?php

declare(strict_types=1);

namespace API\Exceptions;

use Slim\Exception\HttpSpecializedException;

/**
 * @OA\Response(
 *    response="404",
 *    description="리소스를 찾을 수 없음",
 *    @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 * )
 */
class HttpNotFoundException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = 404;

    /**
     * @var string
     */
    protected $message = 'Not found.';

    protected string $title = '404 Not Found';
    protected string $description = 'The requested resource could not be found. Please verify the URI and try again.';
}
