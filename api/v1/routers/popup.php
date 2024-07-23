<?php


namespace Api\V1\Popup;

use API\Middleware\OptionalAccessTokenAuthMiddleware;
use API\v1\Controller\PopupController;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var \Slim\App $app
 */

$app->group('/newwins', function (RouteCollectorProxy $group) {
    $group->get('', [PopupController::class, 'show']);
})->add(OptionalAccessTokenAuthMiddleware::class);
