<?php

namespace API\Middleware;

use API\Service\BoardFileService;
use API\Service\BoardPermission;
use API\Service\BoardService;
use API\Service\CommentService;
use API\Service\GroupService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

/**
 * Board Middleware
 */
class BoardMiddleware
{
    private GroupService $group_service;
    private BoardService $board_service;
    private BoardPermission $board_permission;
    private BoardFileService $file_service;
    private CommentService $comment_service;

    public function __construct(
        GroupService $group_service,
        BoardService $board_service,
        BoardPermission $board_permission,
        BoardFileService $file_service,
        CommentService $comment_service
    ) {
        $this->group_service = $group_service;
        $this->board_service = $board_service;
        $this->board_permission = $board_permission;
        $this->file_service = $file_service;
        $this->comment_service = $comment_service;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $config = $request->getAttribute('config');

        // RouteContext를 사용하여 경로 매개변수 가져오기
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        $routeArguments = $route->getArguments();
        $bo_table = $routeArguments['bo_table'] ?? null;
        $board = $this->board_service->fetchBoardByTable($bo_table);

        if (!$board) {
            throw new HttpNotFoundException($request, '존재하지 않는 게시판입니다.');
        }

        $group = $this->group_service->fetchGroup($board['gr_id']);

        $request = $request->withAttribute('board', $board);
        $request = $request->withAttribute('group', $group);

        // 의존성 주입 클래스 설정
        $this->board_service->setBoard($board);
        $this->board_permission->setConfig($config);
        $this->board_permission->setBoard($board);
        $this->board_permission->setGroup($group);
        $this->file_service->setBoard($board);
        $this->comment_service->setBoard($board);

        return $handler->handle($request);
    }
}
