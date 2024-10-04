<?php

namespace API\v1\Routers;

use API\Middleware\AccessTokenAuthMiddleware;
use API\Middleware\ConfigMiddleware;
use API\v1\Controller\PointController;

/**
 * @var \Slim\App<\Psr\Container\ContainerInterface> $app
 */
$app->get('/v1/member/points', [PointController::class, 'getPoints'])
    ->add(AccessTokenAuthMiddleware::class)
    ->add(ConfigMiddleware::class);
