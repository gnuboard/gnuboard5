<?php


namespace Api\V1\Social;

use API\Middleware\AccessTokenAuthMiddleware;
use API\Middleware\OptionalAccessTokenAuthMiddleware;
use API\v1\Controller\SocialController;
use Slim\Routing\RouteCollectorProxy;
use Slim\App;

/**
 * @var App $app
 */
$app->group('/v1/social', function (RouteCollectorProxy $group) {
    // web, webview 앱
    $group->get('/login/{provider}', [SocialController::class, 'socialLogin']);
    $group->get('/login-callback/{provider}', [SocialController::class, 'socialLoginCallback'])->add(OptionalAccessTokenAuthMiddleware::class);

    // 공통
    $group->post('/register/{provider}', [SocialController::class, 'socialRegister']);
    $group->post('/unlink/{provider}', [SocialController::class, 'socialUnlink'])->add(AccessTokenAuthMiddleware::class);

    // 엑세스 토큰으로 하기.
    $group->post('/token-login/{provider}', [SocialController::class, 'socialLoginWithAccessToken']);
    $group->post('/token-link/{provider}', [SocialController::class, 'socialLinkWithToken'])->add(AccessTokenAuthMiddleware::class);
});
