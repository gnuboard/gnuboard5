<?php

use API\Middleware\AccessTokenAuthMiddleware;
use API\Middleware\ConfigMiddleware;
use API\v1\Controller\MemberController;
use Slim\Routing\RouteCollectorProxy;


$app->group('/members', function (RouteCollectorProxy $group) {
    $group->get('/me', [MemberController::class, 'getMe'])->add(new AccessTokenAuthMiddleware());
    $group->get('/{mb_id}', [MemberController::class, 'getMember'])->add(new AccessTokenAuthMiddleware());
    $group->post('', [MemberController::class, 'createMember']);
    $group->put('/{mb_id}/email-certification/change', [MemberController::class, 'changeCertificationEmail']);
    
})->add(new ConfigMiddleware());

$app->group('/member', function (RouteCollectorProxy $group) {
    $group->put('', [MemberController::class, 'updateMember']);
    $group->post('/images', [MemberController::class, 'updateMemberImages']);
    $group->delete('', [MemberController::class, 'deleteMember']);
})
->add(new ConfigMiddleware())
->add(new AccessTokenAuthMiddleware());