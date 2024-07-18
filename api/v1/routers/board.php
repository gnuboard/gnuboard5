<?php

use API\Middleware\BoardMiddleware;
use API\Middleware\ConfigMiddleware;
use API\Middleware\OptionalAccessTokenAuthMiddleware;
use API\Middleware\WriteMiddleware;
use API\v1\Controller\BoardController;
use Slim\Routing\RouteCollectorProxy;


$app->group('/boards/{bo_table}', function (RouteCollectorProxy $group){
    $group->get('', [BoardController::class, 'getBoard']);

    $group->group('/writes', function (RouteCollectorProxy $group) {
        $group->get('', [BoardController::class, 'getWrites']);
        $group->post('', [BoardController::class, 'createWrite']);

        $group->group('/{wr_id}', function (RouteCollectorProxy $group) {
            $group->get('', [BoardController::class, 'getWrite']);
            $group->put('', [BoardController::class, 'updateWrite']);
            $group->delete('', [BoardController::class, 'deleteWrite']);
        })
        ->add(WriteMiddleware::class);
    });
})
->add(OptionalAccessTokenAuthMiddleware::class)
->add(BoardMiddleware::class)
->add(ConfigMiddleware::class);