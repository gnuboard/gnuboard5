<?php


namespace Api\V1\poll;

use API\Middleware\OptionalAccessTokenAuthMiddleware;
use API\v1\Controller\PollController;
use Slim\Routing\RouteCollectorProxy;
use Slim\App;

/**
 * @var App $app
 */

$app->group('/polls', function (RouteCollectorProxy $group) {
    $group->get('/latest', [PollController::class, 'showLatest']);
    $group->get('/{po_id}', [PollController::class, 'show']);
    $group->get('/{po_id}/{item}', [PollController::class, 'vote']);
    $group->post('/{po_id}/etc', [PollController::class, 'createEtc']);
    $group->delete('/{po_id}/etc/{pc_id}', [PollController::class, 'deleteEtc']);
})->add(OptionalAccessTokenAuthMiddleware::class);