<?php


namespace Api\V1\content;

use API\Middleware\OptionalAccessTokenAuthMiddleware;

use API\v1\Controller\ContentController;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var \Slim\App<\Psr\Container\ContainerInterface> $app
 */
$app->group('/v1/content', function (RouteCollectorProxy $group) {
    $group->get('', [ContentController::class, 'index']);
    $group->get('/{co_id}', [ContentController::class, 'show']);
})->add(OptionalAccessTokenAuthMiddleware::class);