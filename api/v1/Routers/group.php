<?php

namespace API\v1\Routers;

use API\Middleware\ConfigMiddleware;
use API\v1\Controller\GroupController;
use Slim\Routing\RouteCollectorProxy;

$app->group('/v1/groups', function (RouteCollectorProxy $group) {
    $group->get('', [GroupController::class, 'getGroups']);
    $group->get('/{gr_id}/boards', [GroupController::class, 'getBoards']);
})->add(ConfigMiddleware::class);
