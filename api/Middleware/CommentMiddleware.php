<?php

namespace API\Middleware;

use API\Exceptions\HttpNotFoundException;
use API\Service\WriteService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Routing\RouteContext;

/**
 * Comment Middleware
 */
class CommentMiddleware
{
    private WriteService $write_service;

    public function __construct(WriteService $write_service)
    {
        $this->write_service = $write_service;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // route_context 를 사용하여 경로 매개변수 가져오기
        $route_context = RouteContext::fromRequest($request)->getRoute();
        if ($route_context === null) {
            throw new HttpNotFoundException($request, 'url을 찾을 수 없습니다.');
        }
        $comment_id = $route_context->getArguments()['comment_id'] ?? null;
        $comment = $this->write_service->fetchWrite((int)$comment_id);
        if (!$comment) {
            throw new HttpNotFoundException($request, '존재하지 않는 댓글입니다.');
        }

        $request = $request->withAttribute('comment', $comment);

        return $handler->handle($request);
    }
}
