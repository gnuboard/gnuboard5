<?php

namespace API\v1\Routers;

use API\Middleware\ConfigMiddleware;
use API\v1\Controller\ConfigController;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var \Slim\App<\Psr\Container\ContainerInterface> $app
 */
$app->group('/v1/config', function (RouteCollectorProxy $group) {
    $group->get('/html', [ConfigController::class, 'getHtmlConfig']);
    $group->get('/policy', [ConfigController::class, 'getPolicyConfig']);
    $group->get('/member', [ConfigController::class, 'getMemberConfig']);
    $group->get('/memo', [ConfigController::class, 'getMemoConfig']);
    $group->get('/board', [ConfigController::class, 'getBoardConfig']);
})->add(ConfigMiddleware::class);
