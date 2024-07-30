<?php

namespace API\v1\Controller;

use API\Service\BoardService;
use API\Service\BoardNewService;
use API\v1\Model\PageParameters;
use API\v1\Model\Request\BoardNew\SearchRequest;
use API\v1\Model\Response\BoardNew\BoardNew;
use API\v1\Model\Response\BoardNew\BoardNewsResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BoardNewController
{
    private BoardService $board_service;
    private BoardNewService $service;

    public function __construct(
        BoardService $board_service,
        BoardNewService $service,
    ) {
        $this->service = $service;
        $this->board_service = $board_service;
    }

    /**
     * @OA\Get(
     *      path="/api/v1/board-new",
     *      summary="최신 게시글 목록 조회",
     *      tags={"최신글"},
     *      description="최신 게시글 목록을 조회합니다.",
     *      @OA\Parameter(ref="#/components/parameters/gr_id"),
     *      @OA\Parameter(ref="#/components/parameters/view"),
     *      @OA\Parameter(ref="#/components/parameters/mb_id"),
     *      @OA\Parameter(ref="#/components/parameters/page"),
     *      @OA\Parameter(ref="#/components/parameters/per_page"),
     *      @OA\Response(response="200", description="최신 게시글 목록 조회 성공", @OA\JsonContent(ref="#/components/schemas/BoardNewsResponse")),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function getBoardNews(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');

        // 검색 조건 및 페이징 처리
        $query_params = $request->getQueryParams();
        $search_params = new SearchRequest($query_params);
        $page_params = new PageParameters($query_params, $config, $config['cf_new_rows']);

        $total_records = $this->service->fetchTotalCount((array)$search_params);
        $total_page = ceil($total_records / $page_params->per_page);

        $fetch_board_news = $this->service->fetchBoardNews((array)$search_params, (array)$page_params);
        $board_news = [];
        foreach ($fetch_board_news as $i => $new) {
            $this->board_service->setWriteTable($new['bo_table']);
            $write = $this->board_service->fetchWriteById($new['wr_id']);
            if ($write['wr_is_comment']) {
                $comment = $this->board_service->fetchWriteById($write['wr_parent']);
                $new['wr_subject'] = $comment['wr_subject'];
            } else {
                $new['wr_subject'] = $write['wr_subject'];
            }
            $new['wr_is_comment'] = $write['wr_is_comment'];
            $new['num'] = $total_records - ($page_params->page - 1) * $config['cf_new_rows'] - $i;
            $new['mb_name'] = $write['wr_name'];
            $board_news[] = new BoardNew($new);
        }

        $response_data = new BoardNewsResponse([
            "total_records" => $total_records,
            "total_pages" => $total_page,
            "current_page" => $page_params->page,
            "board_news" => $board_news,
        ]);
        return api_response_json($response, $response_data);
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/board-new",
     *      summary="최신 게시글 일괄 삭제",
     *      tags={"최신글"},
     *      description="
최신 게시글을 일괄로 삭제한다.
- 최고관리자만 가능
",
     *      @OA\Parameter(ref="#/components/parameters/page"),
     *      @OA\Parameter(ref="#/components/parameters/per_page"),
     *      @OA\Response(response="200", description="최신 게시글 삭제 성공", @OA\JsonContent(ref="#/components/schemas/Board")),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function deleteBoardNews(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');

        // 검색 조건 및 페이징 처리
        $query_params = $request->getQueryParams();
        $page_params = new PageParameters($query_params, $config);

        return api_response_json($response, ["message" => "test"]);
    }
}
