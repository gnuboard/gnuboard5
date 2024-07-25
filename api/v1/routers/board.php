<?php

use API\Middleware\BoardMiddleware;
use API\Middleware\CommentMiddleware;
use API\Middleware\ConfigMiddleware;
use API\Middleware\OptionalAccessTokenAuthMiddleware;
use API\Middleware\WriteMiddleware;
use API\v1\Controller\BoardController;
use Slim\Routing\RouteCollectorProxy;


$app->group('/boards/{bo_table}', function (RouteCollectorProxy $group){
    $group->get('', [BoardController::class, 'getBoard']);

    $group->group('/writes', function (RouteCollectorProxy $group) {
        $group->get('', [BoardController::class, 'getWrites']);
        $group->post('', [BoardController::class, 'createWrite']);

        $group->group('/{wr_id}', function (RouteCollectorProxy $group) {
            $group->get('', [BoardController::class, 'getWrite']);
            $group->put('', [BoardController::class, 'updateWrite']);
            $group->delete('', [BoardController::class, 'deleteWrite']);

            $group->post('/files', [BoardController::class, 'uploadFiles']);
            $group->get('/files/{bf_no}', [BoardController::class, 'downloadFile']);

            $group->post('/comments', [BoardController::class, 'createComment']);
            $group->get('/comments', [BoardController::class, 'getComments']);
            $group->put('/comments/{comment_id}', [BoardController::class, 'updateComment'])
                ->add(CommentMiddleware::class);
            $group->delete('/comments/{comment_id}', [BoardController::class, 'deleteComment'])
                ->add(CommentMiddleware::class);
        })
        ->add(WriteMiddleware::class);
    });
})
->add(OptionalAccessTokenAuthMiddleware::class)
->add(BoardMiddleware::class)
->add(ConfigMiddleware::class);