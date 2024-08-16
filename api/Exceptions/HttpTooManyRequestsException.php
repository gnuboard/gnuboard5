<?php

declare(strict_types=1);

namespace API\Exceptions;

use Slim\Exception\HttpSpecializedException;

/**
 * @OA\Response(
 *    response="429",
 *    description="너무 많은 요청",
 *    @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 * )
 */
class HttpTooManyRequestsException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = 429;

    /**
     * @var string
     */
    protected $message = 'Too many requests.';

    protected string $title = '429 Too Many Requests';
    protected string $description = 'The client application has surpassed its rate limit, ' .
        'or number of requests they can send in a given period of time.';
}
