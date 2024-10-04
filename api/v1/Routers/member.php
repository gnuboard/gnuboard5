<?php

namespace API\v1\Routers;

use API\Middleware\AccessTokenAuthMiddleware;
use API\Middleware\ConfigMiddleware;
use API\v1\Controller\MemberController;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var \Slim\App<\Psr\Container\ContainerInterface> $app
 */
$app->group('/v1/members', function (RouteCollectorProxy $group) {
    $group->get('/me', [MemberController::class, 'getMe'])->add(AccessTokenAuthMiddleware::class);
    $group->get('/{mb_id}', [MemberController::class, 'getMember'])->add(AccessTokenAuthMiddleware::class);
    $group->post('', [MemberController::class, 'create']);
    $group->post('/search/password', [MemberController::class, 'searchPasswordResetMail']);
    $group->put('/{mb_id}/email-certification/change', [MemberController::class, 'changeCertificationEmail']);
})
->add(ConfigMiddleware::class);

$app->group('/v1/member', function (RouteCollectorProxy $group) {
    $group->put('', [MemberController::class, 'update']);
    $group->post('/images', [MemberController::class, 'updateImages']);
    $group->delete('', [MemberController::class, 'leave']);
})
->add(AccessTokenAuthMiddleware::class)
->add(ConfigMiddleware::class);