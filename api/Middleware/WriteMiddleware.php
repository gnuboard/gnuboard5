<?php

namespace API\Middleware;

use API\Service\BoardService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

/**
 * Write Middleware
 */
class WriteMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $board = $request->getAttribute('board');

        // RouteContext를 사용하여 경로 매개변수 가져오기
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $routeArguments = $route->getArguments();
        $wr_id = $routeArguments['wr_id'] ?? null;

        $board_service = new BoardService($board);

        $write = $board_service->fetchWriteById((int)$wr_id);

        if (!$write) {
            throw new HttpNotFoundException($request, '존재하지 않는 게시글입니다.');
        }

        $request = $request->withAttribute('write', $write);

        return $handler->handle($request);
    }
}
