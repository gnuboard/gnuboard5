<?php

namespace API\v1\Controller;

use API\Exceptions\HttpNotFoundException;
use API\Service\BoardService;
use API\Service\GroupService;
use API\v1\Model\Response\Board\Board;
use API\v1\Model\Response\Group\Group;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GroupController
{
    private GroupService $group_service;
    private BoardService $board_service;

    public function __construct(
        GroupService $group_service,
        BoardService $board_service
    ) {
        $this->group_service = $group_service;
        $this->board_service = $board_service;
    }

    /**
     * @OA\Get(
     *      path="/api/v1/groups/{gr_id}/boards",
     *      summary="게시판그룹 목록 조회",
     *      tags={"게시판그룹"},
     *      description="게시판그룹의 모든 게시판 목록을 보여줍니다.",
     *      @OA\PathParameter(name="gr_id", description="그룹 아이디", @OA\Schema(type="string")),
     *      @OA\Response(response="200", description="게시판그룹 조회 성공", @OA\JsonContent(ref="#/components/schemas/Board")),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function getBoards(Request $request, Response $response, array $args): Response
    {
        $group = $this->group_service->fetchGroup($args['gr_id']);
        if (empty($group)) {
            throw new HttpNotFoundException($request, '게시판그룹을 찾을 수 없습니다.');
        }

        $fetch_boards = $this->board_service->fetchBoardsByGroupId($args['gr_id']);
        $boards = array_map(fn ($board) => new Board($board), $fetch_boards);

        $response_data = [
            'group' => new Group($group),
            'boards' => $boards,
        ];
        return api_response_json($response, $response_data);
    }
}