<?php

namespace API\Middleware;

use API\Exceptions\HttpBadRequestException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * JSON Body Parser Middleware
 *
 * This middleware parses JSON request body and attaches it to the request as a `parsedBody` attribute
 *
 * @see https://www.slimframework.com/docs/v4/objects/request.html#the-request-body
 */
class JsonBodyParserMiddleware implements MiddlewareInterface
{
    /**
     * Parse JSON request body and attach it to the request as a `parsedBody` attribute
     *
     * @param Request $request PSR-7 request
     * @param RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $content_type = $request->getHeader('Content-Type');
        if (isset($content_type[0]) && $content_type[0] === 'application/json') {
            if (!$receive = file_get_contents('php://input')) {
                return $handler->handle($request);
            }
            
            $contents = json_decode($receive, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request = $request->withParsedBody($contents);
            } else {
                error_log('JSON Body Parser Middleware error msg: ' . json_last_error_msg());
                throw new  HttpBadRequestException($request, 'Invalid JSON');
            }
        }

        return $handler->handle($request);
    }
}
