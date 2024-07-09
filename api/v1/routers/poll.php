<?php


namespace Api\V1\poll;

use API\v1\Controller\PollController;
use Slim\Routing\RouteCollectorProxy;
use Slim\App;


/**
 * @var App $app
 */

$app->group('/polls', function (RouteCollectorProxy $group) {
    $group->get('/latest', [PollController::class, 'latest']);
    $group->get('/{po_id}', [PollController::class, 'show']);
    $group->patch('/{po_id}/item', [PollController::class, 'vote']);
    $group->post('/{po_id}/etc', [PollController::class, 'etc_create']);
    $group->delete('/{po_id}/etc/{pc_id}', [PollController::class, 'etc_delete']);

});