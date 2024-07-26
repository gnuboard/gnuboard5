<?php

use API\Middleware\AccessTokenAuthMiddleware;
use API\Middleware\ConfigMiddleware;
use API\v1\Controller\PointController;

$app->get('/member/points', [PointController::class, 'getPoints'])
    ->add(AccessTokenAuthMiddleware::class)
    ->add(ConfigMiddleware::class);
