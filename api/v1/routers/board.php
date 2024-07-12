<?php

use API\Middleware\AccessTokenAuthMiddleware;
use API\Middleware\BoardMiddleware;
use API\Middleware\ConfigMiddleware;
use API\Middleware\WriteMiddleware;
use API\v1\Controller\BoardController;
use Slim\Routing\RouteCollectorProxy;


$app->group('/boards', function (RouteCollectorProxy $group) {
    $group->get('/{bo_table}', [BoardController::class, 'getBoard']);
    $group->get('/{bo_table}/writes', [BoardController::class, 'getWrites']);
    $group->get('/{bo_table}/writes/{wr_id}', [BoardController::class, 'getWrite'])
        ->add(WriteMiddleware::class);
})
->add(BoardMiddleware::class)
->add(ConfigMiddleware::class);