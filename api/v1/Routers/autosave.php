<?php


use API\Middleware\AccessTokenAuthMiddleware;
use API\v1\Controller\AutosaveController;
use Slim\Routing\RouteCollectorProxy;
use Slim\App;


/**
 * @var App $app
 */


$app->group('/v1/autosaves', function (RouteCollectorProxy $group) {
    $group->get('', [AutosaveController::class, 'index']);
    $group->post('', [AutosaveController::class, 'save']);
    $group->get('/count', [AutosaveController::class, 'getCount']);
    $group->get('/{as_id}', [AutosaveController::class, 'show']);
    $group->delete('/{as_id}', [AutosaveController::class, 'delete']);
})->add(AccessTokenAuthMiddleware::class);
