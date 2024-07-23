<?php


namespace Api\V1\routers\popular;

use API\Middleware\OptionalAccessTokenAuthMiddleware;
use API\v1\Controller\PopularSearchController;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var \Slim\App $app
 */

$app->group('/populars', function (RouteCollectorProxy $group) {
    $group->get('', [PopularSearchController::class, 'show']);
})->add(OptionalAccessTokenAuthMiddleware::class);
