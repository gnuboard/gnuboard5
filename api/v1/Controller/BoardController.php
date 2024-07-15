<?php

namespace API\v1\Controller;

use API\Service\BoardService;
use API\Service\CommentService;
use API\Service\BoardPermission;
use API\v1\Model\PageParameters;
use API\v1\Model\Response\Board\Board;
use API\v1\Model\Response\Board\GetWritesResponse;
use API\v1\Model\Response\Write\Thumbnail;
use API\v1\Model\Response\Write\Write;
use API\v1\Model\SearchParameters;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpForbiddenException;


class BoardController
{
    /**
     * @OA\Get(
     *      path="/api/v1/boards/{bo_table}",
     *      summary="게시판 조회",
     *      tags={"게시판"},
     *      description="게시판 정보 1건을 조회합니다.",
     *      @OA\PathParameter(name="bo_table", description="게시판 코드", @OA\Schema(type="string")),
     *      @OA\Response(response="200", description="게시판 조회 성공", @OA\JsonContent(ref="#/components/schemas/Board")),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function getBoard(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $board = $request->getAttribute('board');

        $response_data = new Board($board);

        return api_response_json($response, (array)$response_data);
    }
    /**
     * @OA\Get(
     *      path="/api/v1/boards/{bo_table}/writes",
     *      summary="게시판 글 목록 조회",
     *      tags={"게시판"},
     *      description="게시판 글 목록을 조회합니다.",
     *      @OA\PathParameter(name="bo_table", description="게시판 코드", @OA\Schema(type="string")),
     *      @OA\Parameter(ref="#/components/parameters/sst"),
     *      @OA\Parameter(ref="#/components/parameters/sod"),
     *      @OA\Parameter(ref="#/components/parameters/sfl"),
     *      @OA\Parameter(ref="#/components/parameters/stx"),
     *      @OA\Parameter(ref="#/components/parameters/sca"),
     *      @OA\Parameter(ref="#/components/parameters/spt"),
     *      @OA\Parameter(ref="#/components/parameters/page"),
     *      @OA\Parameter(ref="#/components/parameters/per_page"),
     *      @OA\Parameter(ref="#/components/parameters/is_mobile"),
     *      @OA\Response(response="200", description="게시판 글 목록 조회 성공", @OA\JsonContent(ref="#/components/schemas/GetWritesResponse")),
     *      @OA\Response(response="400", ref="#/components/responses/400"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function getWrites(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $config = $request->getAttribute('config');
        $board = $request->getAttribute('board');
        $member = $request->getAttribute('member');
        $board_service = new BoardService($board);
        $board_permission = new BoardPermission($config, $board);
        
        // 권한 체크
        try {
            $board_permission->checkAccessWrites($member);
        } catch (\Exception $e) {
            throw new HttpForbiddenException($request, $e->getMessage());
        }

        // 검색 조건
        $query_params = $request->getQueryParams();
        $search_params = new SearchParameters($query_params, $board_service, $config);

        // 페이징 처리
        $page_params = new PageParameters($query_params, $board);
        $total_records = $board_service->fetchTotalWritesRecords((array)$search_params);
        $total_page = ceil($total_records / $page_params->per_page);

        /**
         * TODO: 공지글을 출력결과 수에 포함시킬 것인지 별도로 출력할 것인지 결정 필요
         * - 그누보드5는 공지글을 출력결과 수에 포함시킴 
         * - 아래는 별도로 출력하도록 개발됨
         * TODO: 목록에 필요한 데이터를 반환하도록 변경이 필요함
         * - comments => 댓글갯수, images/normal_files => 파일갯수
         */ 
        // 공지글 목록 조회
        $notice_writes = [];
        if (!$search_params->is_search) {
            $fetch_notice_writes = $board_service->fetchNoticeWrites();
            $notice_writes = array_map(fn($notice_write) => new Write($notice_write), $fetch_notice_writes);
        }
        // 게시글 목록 조회
        $fetch_writes = $board_service->fetchWrites((array)$search_params, (array)$page_params);
        $writes = array_map(fn($write) => new Write($write), $fetch_writes);

        $response_data = new GetWritesResponse([
            "total_records" => $total_records,
            "total_pages" => $total_page,
            "current_page" => $page_params->page,
            "is_mobile" => $page_params->is_mobile,
            "categories" => $board_service->getCategories(),
            "board" => new Board($board),
            "notice_writes" => $notice_writes,
            "writes" => $writes,
            "prev_spt" => $board_service->getPrevSearchPart((array)$search_params),
            "next_spt" => $board_service->getNextSearchPart((array)$search_params),
        ]);
        return api_response_json($response, (array)$response_data);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/boards/{bo_table}/writes/{wr_id}",
     *      summary="게시글 조회",
     *      tags={"게시판"},
     *      description="게시판의 게시글 1건을 조회합니다.",
     *      @OA\PathParameter(name="bo_table", description="게시판 코드", @OA\Schema(type="string")),
     *      @OA\PathParameter(name="wr_id", description="글 번호", @OA\Schema(type="integer")),
     *      @OA\Response(response="200", description="게시판 글 조회 성공", @OA\JsonContent(ref="#/components/schemas/Write")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function getWrite(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $config = $request->getAttribute('config');
        $board = $request->getAttribute('board');
        $write = $request->getAttribute('write');
        $member = $request->getAttribute('member');
        $board_service = new BoardService($board);
        $board_permission = new BoardPermission($config, $board);
        $comment_service = new CommentService($board);
        
        // 권한 체크
        try {
            $board_permission->checkAccessWrite($member, $write);
        } catch (\Exception $e) {
            throw new HttpForbiddenException($request, $e->getMessage());
        }

        $thumb = get_list_thumbnail($board['bo_table'], $write['wr_id'], $board['bo_gallery_width'], $board['bo_gallery_height'], false, true);
        $write_data = array_merge($write, array(
                "comments" => $comment_service->getComments($write['wr_id']),
                "images" => $board_service->getWriteFiles((int)$write['wr_id'], 'image'),
                "normal_files" => $board_service->getWriteFiles((int)$write['wr_id'], 'file'),
                "thumbnail" => new Thumbnail($thumb)
            ));

        $write = new Write($write_data);

        return api_response_json($response, (array)$write);
    }
}
