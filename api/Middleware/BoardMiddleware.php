<?php

namespace API\Middleware;

use API\Database\Db;
use API\Service\GroupService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

/**
 * Board Middleware
 */
class BoardMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        global $g5;

        // RouteContext를 사용하여 경로 매개변수 가져오기
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        $routeArguments = $route->getArguments();
        $bo_table = $routeArguments['bo_table'] ?? null;

        $query = "SELECT * FROM {$g5['board_table']} where bo_table = :bo_table";
        $stmt = Db::getInstance()->run($query, ['bo_table' => $bo_table]);
        $board = $stmt->fetch();

        if (!$board) {
            throw new HttpNotFoundException($request, '존재하지 않는 게시판입니다.');
        }

        $group_service = new GroupService();
        $group = $group_service->fetchGroup($board['gr_id']);

        $request = $request->withAttribute('board', $board);
        $request = $request->withAttribute('group', $group);

        return $handler->handle($request);
    }
}
