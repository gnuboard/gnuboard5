<?php

namespace API\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;


/**
 * Config Middleware
 */
class ConfigMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        global $g5;

        $sql = "SELECT * FROM {$g5['config_table']}";
        $config = sql_fetch($sql);

        if (!$config) {
            throw new HttpNotFoundException($request, 'Config not found.');
        }

        $request = $request->withAttribute('config', $config);

        return $handler->handle($request);
    }
}
