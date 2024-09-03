<?php


namespace Api\V1\Social;

use API\v1\Controller\SocialController;
use Slim\Routing\RouteCollectorProxy;
use Slim\App;

/**
 * @var App $app
 */
$app->group('/social', function (RouteCollectorProxy $group) {
    // web, webview 앱
    $group->get('/login/{provider}', [SocialController::class, 'socialLogin']);
    $group->get('/login-callback/{provider}', [SocialController::class, 'socialLoginCallback']);

    // 공통
    $group->post('/register/{provider}', [SocialController::class, 'socialRegister']);

    // 엑세스 토큰으로 하기.
    $group->post('/token-login/{provider}', [SocialController::class, 'socialLoginWithAccessToken']);
});
