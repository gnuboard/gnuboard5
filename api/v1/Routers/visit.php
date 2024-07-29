<?php


namespace Api\V1\Visit;

use API\Middleware\AccessTokenAuthMiddleware;
use API\v1\Controller\VisitController;
use Slim\Routing\RouteCollectorProxy;
use Slim\App;

/**
 * @var App $app
 */

$app->group('/visit', function (RouteCollectorProxy $group) {
    $group->get('', [VisitController::class, 'show_count']);
})->add(AccessTokenAuthMiddleware::class);