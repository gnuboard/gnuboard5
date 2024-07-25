<?php

declare(strict_types=1);

namespace API\Exceptions;

use Slim\Exception\HttpSpecializedException;

/**
 * @OA\Response(
 *    response="422",
 *    description="입력값 오류",
 *    @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 * )
 */
class HttpUnprocessableEntityException extends HttpSpecializedException
{
    /**
     * @var int
     */
    protected $code = 422;

    /**
     * @var string
     */
    protected $message = 'Unprocessable Entity.';

    protected string $title = '422 Unprocessable Entity';
    protected string $description = 'The request was well-formed but was unable to be followed due to semantic errors.';
}
