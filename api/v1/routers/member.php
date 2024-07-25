<?php

use API\Middleware\AccessTokenAuthMiddleware;
use API\Middleware\ConfigMiddleware;
use API\v1\Controller\MemberController;
use Slim\Routing\RouteCollectorProxy;


$app->group('/members', function (RouteCollectorProxy $group) {
    $group->get('/me', [MemberController::class, 'getMe'])->add(AccessTokenAuthMiddleware::class);
    $group->get('/{mb_id}', [MemberController::class, 'getMember'])->add(AccessTokenAuthMiddleware::class);
    $group->post('', [MemberController::class, 'create']);
    $group->post('/search/password', [MemberController::class, 'searchPasswordResetMail']);
    $group->put('/{mb_id}/email-certification/change', [MemberController::class, 'changeCertificationEmail']);
})
->add(ConfigMiddleware::class);

$app->group('/member', function (RouteCollectorProxy $group) {
    $group->put('', [MemberController::class, 'updateMember']);
    $group->post('/images', [MemberController::class, 'updateMemberImages']);
    $group->delete('', [MemberController::class, 'leaveMember']);
})
->add(AccessTokenAuthMiddleware::class)
->add(ConfigMiddleware::class);