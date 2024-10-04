<?php

namespace API\v1\Routers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @var \Slim\App<\Psr\Container\ContainerInterface> $app
 */
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('Hello API');
    return $response;
});
