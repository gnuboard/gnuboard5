<?php

namespace Api\V1\memo;

use API\Middleware\AccessTokenAuthMiddleware;
use API\v1\Controller\MemoController;
use Slim\Routing\RouteCollectorProxy;


/**
 * @var \Slim\App<\Psr\Container\ContainerInterface> $app
 */
$app->group('/v1/member/memos', function (RouteCollectorProxy $group) {
    /**
     * 쪽지 목록 조회
     * 현재 로그인 회원의 쪽지 목록을 조회합니다.
     */
    $group->get('', [MemoController::class, 'index']);
    /**
     * 쪽지 전송
     * 현재 로그인 회원이 다른 회원에게 쪽지를 전송합니다.
     */
    $group->post('', [MemoController::class, 'send']);
    $group->get('/{me_id}', [MemoController::class, 'show']);
    $group->delete('/{me_id}', [MemoController::class, 'delete']);
})->add(AccessTokenAuthMiddleware::class);
