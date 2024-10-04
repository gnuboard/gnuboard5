<?php


namespace Api\V1\routers\popular;

use API\Middleware\OptionalAccessTokenAuthMiddleware;
use API\v1\Controller\PopularSearchController;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var \Slim\App<\Psr\Container\ContainerInterface> $app
 */
$app->group('/v1/populars', function (RouteCollectorProxy $group) {
    $group->get('', [PopularSearchController::class, 'show']);
})->add(OptionalAccessTokenAuthMiddleware::class);
