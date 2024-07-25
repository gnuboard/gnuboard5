<?php

namespace API\Middleware;

use API\Service\BoardService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

/**
 * Comment Middleware
 */
class CommentMiddleware
{
    private BoardService $board_service;

    public function __construct(BoardService $board_service)
    {
        $this->board_service = $board_service;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // RouteContext를 사용하여 경로 매개변수 가져오기
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $routeArguments = $route->getArguments();
        $comment_id = $routeArguments['comment_id'] ?? null;

        $comment = $this->board_service->fetchWriteById((int)$comment_id);

        if (!$comment) {
            throw new HttpNotFoundException($request, '존재하지 않는 댓글입니다.');
        }

        $request = $request->withAttribute('comment', $comment);

        return $handler->handle($request);
    }
}
