<?php

namespace API\v1\Routers;

use API\v1\Controller\AuthenticationController;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var \Slim\App<\Psr\Container\ContainerInterface> $app
 */
$app->group('/v1/token', function (RouteCollectorProxy $group) {
    $group->post('', [AuthenticationController::class, 'generateToken']);
    $group->post('/refresh', [AuthenticationController::class, 'refreshToken']);
    $group->post('/guest', [AuthenticationController::class, 'generateTokenByGuest']);
});