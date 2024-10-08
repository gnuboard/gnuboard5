<?php

namespace API\Middleware;

use API\Exceptions\HttpNotFoundException;
use API\Service\BoardFileService;
use API\Service\BoardPermission;
use API\Service\BoardService;
use API\Service\CommentService;
use API\Service\ConfigService;
use API\Service\GroupService;
use API\Service\WriteService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
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
    private WriteService $write_service;

    public function __construct(
        GroupService $group_service,
        BoardService $board_service,
        BoardPermission $board_permission,
        BoardFileService $file_service,
        CommentService $comment_service,
        WriteService $write_service
    ) {
        $this->group_service = $group_service;
        $this->board_service = $board_service;
        $this->board_permission = $board_permission;
        $this->file_service = $file_service;
        $this->comment_service = $comment_service;
        $this->write_service = $write_service;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $config = ConfigService::getConfig();

        // route_context 사용하여 경로 매개변수 가져오기
        $route_context = RouteContext::fromRequest($request)->getRoute();
        if ($route_context === null) {
            throw new HttpNotFoundException($request, 'url 을 찾을 수 없습니다.');
        }
        $route_arguments = $route_context->getArguments();
        $bo_table = $route_arguments['bo_table'] ?? null;
        $board = $this->board_service->getBoard($bo_table);

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
        $this->write_service->setBoard($board);

        return $handler->handle($request);
    }
}
