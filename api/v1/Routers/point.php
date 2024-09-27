<?php

namespace API\v1\Routers;

use API\Middleware\AccessTokenAuthMiddleware;
use API\Middleware\ConfigMiddleware;
use API\v1\Controller\PointController;

/**
 * @var App $app
 */
$app->get('/v1/member/points', [PointController::class, 'getPoints'])
    ->add(AccessTokenAuthMiddleware::class)
    ->add(ConfigMiddleware::class);
