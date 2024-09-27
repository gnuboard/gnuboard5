<?php

namespace API\v1\Routers;

use API\Middleware\AccessTokenAuthMiddleware;
use API\Middleware\ConfigMiddleware;
use API\Middleware\OptionalAccessTokenAuthMiddleware;
use API\v1\Controller\BoardNewController;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var App $app
 */
$app->group('/v1/board-new', function (RouteCollectorProxy $group) {
    $group->get('', [BoardNewController::class, 'getBoardNews'])
        ->add(OptionalAccessTokenAuthMiddleware::class);
    $group->delete('', [BoardNewController::class, 'deleteBoardNews'])
        ->add(AccessTokenAuthMiddleware::class);
})->add(ConfigMiddleware::class);
