<?php

namespace API\Middleware;

use API\Exceptions\HttpNotFoundException;
use API\Service\ConfigService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Config Middleware
 */
class ConfigMiddleware
{

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $config = ConfigService::getConfig();
        if (!$config) {
            throw new HttpNotFoundException($request, 'Config not found.');
        }

        $request = $request->withAttribute('config', $config);

        return $handler->handle($request);
    }
}
