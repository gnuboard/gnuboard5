<?php

use API\Middleware\ConfigMiddleware;
use API\v1\Controller\ConfigController;
use Slim\Routing\RouteCollectorProxy;


$app->group('/config', function (RouteCollectorProxy $group) {
    $group->get('/html', [ConfigController::class, 'getHtmlConfig']);
    $group->get('/policy', [ConfigController::class, 'getPolicyConfig']);
    $group->get('/member', [ConfigController::class, 'getMemberConfig']);
    $group->get('/memo', [ConfigController::class, 'getMemoConfig']);
    $group->get('/board', [ConfigController::class, 'getBoardConfig']);
})->add(new ConfigMiddleware());
