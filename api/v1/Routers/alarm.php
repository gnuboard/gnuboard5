<?php


use API\Middleware\AccessTokenAuthMiddleware;
use API\v1\Controller\AlarmController;
use Slim\Routing\RouteCollectorProxy;


/**
 * @var \Slim\App<\Psr\Container\ContainerInterface> $app
 */
$app->group('/v1/alarm', function (RouteCollectorProxy $group) {
    $group->post('/test', [AlarmController::class, 'test']);
    $group->post('', [AlarmController::class, 'register'])
        ->add(AccessTokenAuthMiddleware::class);
});