<?php

namespace API\v1\Routers;

use API\Middleware\ConfigMiddleware;
use API\v1\Controller\FaqController;
use Slim\Routing\RouteCollectorProxy;

/**
 * @var \Slim\App $app
 */

$app->group('/v1/faqs', function (RouteCollectorProxy $group) {
    $group->get('', [FaqController::class, 'index']); // 카테고리 (html 제외)
    $group->get('/category/{ca_id}', [FaqController::class, 'show']); //개별 FAQ 분류 매뉴
})->add(ConfigMiddleware::class);