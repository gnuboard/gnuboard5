<?php

use API\v1\Controller\AuthenticationController;
use Slim\Routing\RouteCollectorProxy;


$app->group('/token', function (RouteCollectorProxy $group) {
    $group->post('', [AuthenticationController::class, 'generateToken']);
    $group->post('/refresh', [AuthenticationController::class, 'refreshToken']);
});
