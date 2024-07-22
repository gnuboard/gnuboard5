<?php


namespace Api\V1\poll;

use API\Middleware\OptionalAccessTokenAuthMiddleware;
use API\v1\Controller\PopupController;
use Slim\Routing\RouteCollectorProxy;
use Slim\App;

/**
 * @var App $app
 */

$app->group('/newwins', function (RouteCollectorProxy $group) {
    $group->get('', [PopupController::class, 'show']);
})->add(OptionalAccessTokenAuthMiddleware::class);
