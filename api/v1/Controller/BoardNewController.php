<?php

namespace API\v1\Controller;

use API\Exceptions\HttpForbiddenException;
use API\Exceptions\HttpUnprocessableEntityException;
use API\Service\BoardFileService;
use API\Service\BoardService;
use API\Service\BoardNewService;
use API\Service\CommentService;
use API\Service\PointService;
use API\Service\ScrapService;
use API\Service\WriteService;
use API\v1\Model\PageParameters;
use API\v1\Model\Request\BoardNew\SearchRequest;
use API\v1\Model\Response\BoardNew\BoardNew;
use API\v1\Model\Response\BoardNew\BoardNewsResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class BoardNewController
{
    private BoardService $board_service;
    private BoardNewService $service;
    private BoardFileService $file_service;
    private CommentService $comment_service;
    private PointService $point_service;
    private ScrapService $scrap_service;
    private WriteService $write_service;

    public function __construct(
        BoardService $board_service,
        BoardNewService $service,
        BoardFileService $file_service,
        CommentService $comment_service,
        PointService $point_service,
        ScrapService $scrap_service,
        WriteService $write_service
    ) {
        $this->service = $service;
        $this->board_service = $board_service;
        $this->file_service = $file_service;
        $this->comment_service = $comment_service;
        $this->point_service = $point_service;
        $this->scrap_service = $scrap_service;
        $this->write_service = $write_service;
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
            $this->write_service->setWriteTable($new['bo_table']);
            $write = $this->write_service->fetchWrite((int)$new['wr_id']);
            if ($write['wr_is_comment']) {
                $comment = $this->write_service->fetchWrite((int)$write['wr_parent']);
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
            'total_records' => $total_records,
            'total_pages' => $total_page,
            'current_page' => $page_params->page,
            'board_news' => $board_news,
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
    - 최신 게시글로 등록된 원래 게시글/댓글도 삭제
    - 최고관리자만 가능
    ",
     *      @OA\Parameter(
     *          name="bn_ids[]", in="query",
     *          description="삭제할 최신 게시글 ID",
     *          required=true,
     *          @OA\Schema(
     *             type="array",
     *             @OA\Items(type="integer")
     *         )
     *      ),
     *      @OA\Response(response="200", description="최신 게시글 삭제 성공", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     * @throws Exception
     */
    public function deleteBoardNews(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');
        $member = $request->getAttribute('member');
        $query_params = $request->getQueryParams();
        $bn_ids = $query_params['bn_ids'] ?? [];

        try {
            if (!is_super_admin($config, $member['mb_id'])) {
                throw new HttpForbiddenException($request, '최고관리자만 가능합니다.');
            }
            if (empty($bn_ids)) {
                throw new HttpUnprocessableEntityException($request, '삭제할 최신 게시글 ID가 필요합니다.');
            }

            foreach ($bn_ids as $bn_id) {
                $board_new = $this->service->fetchById($bn_id);
                if (!$board_new) {
                    continue;
                }
                // 게시판 정보 및 게시글 정보 조회
                $board = $this->board_service->getBoard($board_new['bo_table']);
                $this->board_service->setBoard($board);
                $this->write_service->setBoard($board);
                $this->comment_service->setBoard($board);
                $this->file_service->setBoard($board);
                $write = $this->write_service->fetchWrite((int)$board_new['wr_id']);

                if ($write['wr_is_comment']) {
                    // 댓글 삭제
                    $this->comment_service->deleteCommentById($write['wr_id']);
                    // 포인트 삭제
                    if (!$this->point_service->removePoint($write['mb_id'], $board['bo_table'], $write['wr_id'], '댓글')) {
                        $this->point_service->addPoint($write['mb_id'], $board['bo_comment_point'] * (-1), "{$board['bo_subject']} {$write['wr_parent']}-{$write['wr_id']} 댓글삭제");
                    }
                    // 게시물 정보 갱신 (wr_last, wr_comment)
                    $last = $this->write_service->fetchWriteCommentLast($write);
                    $this->write_service->updateWrite($write['wr_id'], ['wr_comment' => $write['wr_comment'] - 1, 'wr_last' => $last['wr_last']]);
                    $this->service->deleteByComment($board['bo_table'], $write['wr_id']);
                } else {
                    // 포인트 및 파일 삭제
                    $count_comments = 0;
                    $count_writes = 0;
                    $all_writes = $this->write_service->fetchWritesAndComments($write['wr_id']);
                    foreach ($all_writes as $all) {
                        if ($all['wr_is_comment']) {
                            if (!$this->point_service->removePoint($all['mb_id'], $board['bo_table'], $all['wr_id'], '댓글')) {
                                $this->point_service->addPoint($all['mb_id'], $board['bo_comment_point'] * (-1), "{$board['bo_subject']} {$write['wr_id']}-{$all['wr_id']} 댓글삭제");
                            }
                            $count_comments++;
                        } else {
                            if (!$this->point_service->removePoint($all['mb_id'], $board['bo_table'], $all['wr_id'], '쓰기')) {
                                $this->point_service->addPoint($all['mb_id'], $board['bo_write_point'] * (-1), "{$board['bo_subject']} {$all['wr_id']} 글삭제");
                            }
                            $this->file_service->deleteWriteFiles($all);
                            $count_writes++;
                        }
                    }
                    // 게시글/댓글 및 최신글/스크랩 삭제
                    $this->service->deleteByWrite($board['bo_table'], $write['wr_id']);
                    $this->scrap_service->deleteScrapByWrite($board['bo_table'], $write['wr_id']);
                    $this->comment_service->deleteAllCommentByParent($write['wr_id']);
                    // 공지사항 삭제
                    $bo_notice = board_notice($board['bo_notice'], $write['wr_id'], false);
                    $this->board_service->updateBoard(['bo_notice' => $bo_notice]);
                    // 글/댓글 숫자 감소
                    $this->board_service->decreaseWriteAndCommentCount($count_writes, $count_comments);
                }
            }
        } catch (Exception $e) {
            throw $e;
        }

        return api_response_json($response, ['message' => '삭제되었습니다.']);
    }
}
