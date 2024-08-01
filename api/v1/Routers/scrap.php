<?php

namespace API\v1\Routers;

use API\Middleware\AccessTokenAuthMiddleware;
use API\Middleware\BoardMiddleware;
use API\Middleware\ConfigMiddleware;
use API\Middleware\WriteMiddleware;
use API\v1\Controller\ScrapController;
use Slim\Routing\RouteCollectorProxy;


$app->group('/member/scraps', function (RouteCollectorProxy $group) {
    $group->get('', [ScrapController::class, 'getScraps']);
    $group->get('/{bo_table}/{wr_id}', [ScrapController::class, 'createPage'])
        ->add(WriteMiddleware::class)
        ->add(BoardMiddleware::class);
    $group->post('/{bo_table}/{wr_id}', [ScrapController::class, 'create'])
        ->add(WriteMiddleware::class)
        ->add(BoardMiddleware::class);
    $group->delete('/{ms_id}', [ScrapController::class, 'delete']);
})
    ->add(AccessTokenAuthMiddleware::class)
    ->add(ConfigMiddleware::class);