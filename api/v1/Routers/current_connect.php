<?php

use API\Middleware\OptionalAccessTokenAuthMiddleware;
use API\v1\Controller\CurrentConnectController;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var Slim\App $app
 */

$app->group('/v1/members/current-connect', function (RouteCollectorProxy $group) {
    $group->get('', [CurrentConnectController::class, 'index']);
    $group->post('', [CurrentConnectController::class, 'create'])->add(OptionalAccessTokenAuthMiddleware::class);
});