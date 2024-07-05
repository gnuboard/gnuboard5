<?php

use API\Middleware\AccessTokenAuthMiddleware;
use API\Middleware\ConfigMiddleware;
use API\v1\Controller\MemberController;
use Slim\Routing\RouteCollectorProxy;


$app->group('/members', function (RouteCollectorProxy $group) {
    $group->get('/me', [MemberController::class, 'getMe'])->add(new AccessTokenAuthMiddleware());
    $group->get('/{mb_id}', [MemberController::class, 'getMember'])->add(new AccessTokenAuthMiddleware());
    $group->post('', [MemberController::class, 'createMember']);
    $group->put('', [MemberController::class, 'updateMember'])->add(new AccessTokenAuthMiddleware());
    $group->put('/images', [MemberController::class, 'updateMemberImages'])->add(new AccessTokenAuthMiddleware());
    $group->put('/{mb_id}/email-certification/change', [MemberController::class, 'changeCertificationEmail'])->add(new AccessTokenAuthMiddleware());
    $group->delete('', [MemberController::class, 'deleteMember'])->add(new AccessTokenAuthMiddleware());
})->add(new ConfigMiddleware());
