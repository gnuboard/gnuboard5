<?php

use API\Middleware\AccessTokenAuthMiddleware;
use API\Middleware\BoardMiddleware;
use API\Middleware\ConfigMiddleware;
use API\v1\Controller\BoardController;
use Slim\Routing\RouteCollectorProxy;


$app->group('/boards', function (RouteCollectorProxy $group) {
    $group->get('/{bo_table}', [BoardController::class, 'getBoard']);
    $group->get('/{bo_table}/writes', [BoardController::class, 'getWrites']);

})
->add(BoardMiddleware::class)
->add(ConfigMiddleware::class);