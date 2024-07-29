<?php

namespace API\v1\Routers;

use API\Middleware\AccessTokenAuthMiddleware;
use API\Middleware\ConfigMiddleware;
use API\v1\Controller\ScrapController;
use Slim\Routing\RouteCollectorProxy;


$app->group('/member/scraps', function (RouteCollectorProxy $group) {
    $group->get('', [ScrapController::class, 'getScraps']);
    $group->get('/{bo_table}/{wr_id}', [ScrapController::class, 'get']);
    $group->post('/{bo_table}/{wr_id}', [ScrapController::class, 'create']);
    $group->delete('/{ms_id}', [ScrapController::class, 'delete']);
})
    ->add(AccessTokenAuthMiddleware::class)
    ->add(ConfigMiddleware::class);