<?php

namespace API\v1\Routers;

use API\Middleware\AccessTokenAuthMiddleware;
use API\Middleware\BoardMiddleware;
use API\Middleware\CommentMiddleware;
use API\Middleware\ConfigMiddleware;
use API\Middleware\OptionalAccessTokenAuthMiddleware;
use API\Middleware\WriteMiddleware;
use API\v1\Controller\BoardController;
use Slim\Routing\RouteCollectorProxy;


$app->group('/v1/boards/{bo_table}', function (RouteCollectorProxy $group){
    $group->get('', [BoardController::class, 'getBoard']);

    $group->group('/writes', function (RouteCollectorProxy $group) {
        $group->get('', [BoardController::class, 'getWrites']);
        $group->post('', [BoardController::class, 'createWrite']);

        $group->group('/{wr_id}', function (RouteCollectorProxy $group) {
            $group->get('', [BoardController::class, 'getWrite']);
            $group->post('', [BoardController::class, 'getSecretWrite']);
            $group->put('', [BoardController::class, 'updateWrite']);
            $group->delete('', [BoardController::class, 'deleteWrite']);

            $group->post('/files', [BoardController::class, 'uploadFiles']);
            $group->get('/files/{bf_no}', [BoardController::class, 'downloadFile']);

            $group->post('/comments', [BoardController::class, 'createComment']);
            $group->get('/comments', [BoardController::class, 'getComments']);
            $group->group('/comments/{comment_id}', function (RouteCollectorProxy $group) {
                $group->post('', [BoardController::class, 'getComment']);
                $group->put('', [BoardController::class, 'updateComment']);
                $group->delete('', [BoardController::class, 'deleteComment']);
            })->add(CommentMiddleware::class);
        })
        ->add(WriteMiddleware::class);
    })
    ->add(OptionalAccessTokenAuthMiddleware::class);

    $group->post('/writes/{wr_id}/{good_type}', [BoardController::class, 'goodWrite'])
        ->add(WriteMiddleware::class)
        ->add(AccessTokenAuthMiddleware::class);
})
->add(BoardMiddleware::class)
->add(ConfigMiddleware::class);