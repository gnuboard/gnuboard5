<?php


namespace api\v1\Routers;

use API\Middleware\AccessTokenAuthMiddleware;
use API\v1\Controller\QaController;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var \Slim\App<\Psr\Container\ContainerInterface> $app
 */
$app->group('/v1/qa', function (RouteCollectorProxy $group) {
    $group->get('/config', [QaController::class, 'getQaConfig']);
    $group->get('', [QaController::class, 'index']);
    $group->get('/{qa_id}', [QaController::class, 'show']);
    $group->put('/{qa_id}', [QaController::class, 'update']);
    $group->post('', [QaController::class, 'create']);
    $group->delete('/{qa_id}', [QaController::class, 'delete']);
    $group->post('/{qa_id}/file', [QaController::class, 'fileUpload']);
})->add(AccessTokenAuthMiddleware::class);
