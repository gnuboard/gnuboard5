<?php


use API\v1\Controller\AlarmController;
use Slim\Routing\RouteCollectorProxy;
use Slim\App;


/**
 * @var App $app
 */


$app->group('/v1/alarm', function (RouteCollectorProxy $group) {
    $group->post('/test', [AlarmController::class, 'test']);
    $group->post('', [AlarmController::class, 'register'])->add(\API\Middleware\OptionalAccessTokenAuthMiddleware::class);
});
