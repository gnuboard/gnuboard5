<?php

declare(strict_types=1);

namespace API\Exceptions;

use Slim\Exception\HttpSpecializedException;

/**
 * @OA\Response(
 *    response="500",
 *    description="내부 서버 오류",
 *    @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 * )
 */
class HttpInternalServerErrorException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = 500;

    /**
     * @var string
     */
    protected $message = 'Internal server error.';

    protected string $title = '500 Internal Server Error';
    protected string $description = 'An internal error has occurred while processing your request.';
}
