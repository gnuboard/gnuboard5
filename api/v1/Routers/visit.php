<?php


namespace Api\V1\Visit;

use API\Middleware\OptionalAccessTokenAuthMiddleware;
use API\v1\Controller\VisitController;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var \Slim\App<\Psr\Container\ContainerInterface> $app
 */
$app->group('/v1/visit', function (RouteCollectorProxy $group) {
    $group->get('', [VisitController::class, 'show']);
})->add(OptionalAccessTokenAuthMiddleware::class);