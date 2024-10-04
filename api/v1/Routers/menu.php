<?php

namespace Api\V1\menu;

use API\Middleware\OptionalAccessTokenAuthMiddleware;
use API\v1\Controller\MenuController;
use Slim\Routing\RouteCollectorProxy;


/**
 * @var \Slim\App<\Psr\Container\ContainerInterface> $app
 */
$app->group('/v1/menus', function (RouteCollectorProxy $group) {
    $group->get('', [MenuController::class, 'index']);
})->add(OptionalAccessTokenAuthMiddleware::class);