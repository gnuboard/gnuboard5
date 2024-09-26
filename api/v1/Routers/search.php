<?php

use API\Middleware\OptionalAccessTokenAuthMiddleware;
use API\v1\Controller\SearchController;
use Slim\Routing\RouteCollectorProxy;
use Slim\App;

/**
 * @var App $app
 */

$app->group('/v1/search', function (RouteCollectorProxy $group) {
    $group->get('', [SearchController::class, 'searchBoard']);
})->add(OptionalAccessTokenAuthMiddleware::class);