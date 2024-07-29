<?php

namespace Api\V1\menu;

use API\Middleware\OptionalAccessTokenAuthMiddleware;
use API\v1\Controller\MenuController;
use Slim\Routing\RouteCollectorProxy;
use Slim\App;


/**
 * @var App $app
 */

$app->group('/menus', function (RouteCollectorProxy $group) {
    $group->get('', [MenuController::class, 'index']);
})->add(OptionalAccessTokenAuthMiddleware::class);