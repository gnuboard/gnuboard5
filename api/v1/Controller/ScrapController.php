<?php

namespace API\v1\Controller;

use API\Exceptions\HttpBadRequestException;
use API\Exceptions\HttpConflictException;
use API\Exceptions\HttpForbiddenException;
use API\Exceptions\HttpNotFoundException;
use API\Exceptions\HttpUnprocessableEntityException;
use API\Service\BoardNewService;
use API\Service\BoardPermission;
use API\Service\BoardService;
use API\Service\CommentService;
use API\Service\MemberService;
use API\Service\PointService;
use API\Service\ScrapService;
use API\Service\WriteService;
use API\v1\Model\PageParameters;
use API\v1\Model\Request\Scrap\CreateScrapRequest;
use API\v1\Model\Response\Scrap\CreateScrapPageResponse;
use API\v1\Model\Response\Scrap\ScrapsResponse;
use API\v1\Model\Response\Scrap\Scrap;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ScrapController
{
    private BoardService $board_service;
    private BoardNewService $board_new_service;
    private BoardPermission $board_permission;
    private CommentService $comment_service;
    private MemberService $member_service;
    private PointService $point_service;
    private ScrapService $scrap_service;
    private WriteService $write_service;

    public function __construct(
        BoardService $board_service,
        BoardNewService $board_new_service,
        BoardPermission $board_permission,
        CommentService $comment_service,
        MemberService $member_service,
        ScrapService $scrap_service,
        PointService $point_service,
        WriteService $write_service
    ) {
        $this->board_service = $board_service;
        $this->board_new_service = $board_new_service;
        $this->board_permission = $board_permission;
        $this->comment_service = $comment_service;
        $this->member_service = $member_service;
        $this->scrap_service = $scrap_service;
        $this->point_service = $point_service;
        $this->write_service = $write_service;
    }

    /**
     * @OA\Get(
     *      path="/api/v1/member/scraps",
     *      summary="회원 스크랩 목록 조회",
     *      tags={"스크랩"},
     *      security={{"Oauth2Password": {}}},
     *      description="회원 스크랩 목록을 조회합니다.",
     *      @OA\Parameter(ref="#/components/parameters/page"),
     *      @OA\Parameter(ref="#/components/parameters/per_page"),
     *      @OA\Response(response="200", description="스크랩 목록 조회 성공", @OA\JsonContent(ref="#/components/schemas/ScrapsResponse")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500")
     * )
     */
    public function getScraps(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');
        $member = $request->getAttribute('member');

        try {
            // 검색 조건 및 페이징 처리
            $query_params = $request->getQueryParams();
            $page_params = new PageParameters($query_params, $config);

            $total_records = $this->scrap_service->fetchTotalScrapCount($member['mb_id']);
            $total_page = ceil($total_records / $page_params->per_page);

            // 스크랩 목록 조회
            $fetch_scraps = $this->scrap_service->fetchScraps($member['mb_id'], (array)$page_params);
            $scraps = array_map(function ($scrap) {
                // 게시판 정보 및 게시글 정보 조회
                // TODO: 추후에 조인이 가능하도록 수정해야할 필요가 있음.
                $board = $this->board_service->getBoard($scrap['bo_table']);
                if ($board) {
                    $this->write_service->setBoard($board);
                    $write = $this->write_service->fetchWrite((int)$scrap['wr_id']);
                    $scrap['bo_subject'] = $board['bo_subject'];
                    $scrap['wr_subject'] = $write['wr_subject'];
                }
                return new Scrap($scrap);
            }, $fetch_scraps);

            $response_data = new ScrapsResponse([
                'total_records' => $total_records,
                'total_pages' => $total_page,
                'scraps' => $scraps
            ]);

            return api_response_json($response, $response_data);
        } catch (Exception $e) {
            
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/member/scraps/{bo_table}/{wr_id}",
     *      summary="회원 스크랩 등록 페이지 정보 조회",
     *      tags={"스크랩"},
     *      security={{"Oauth2Password": {}}},
     *      description="
    스크랩 등록 페이지의 정보를 조회합니다.
    - 게시판 정보 및 게시글 정보를 조회합니다.
    ",
     *      @OA\PathParameter(name="bo_table", description="게시판 ID", @OA\Schema(type="string")),
     *      @OA\PathParameter(name="wr_id", description="게시글 ID", @OA\Schema(type="integer")),
     *      @OA\Response(response="200", description="스크랩 페이지 정보 조회 성공", @OA\JsonContent(ref="#/components/schemas/CreateScrapPageResponse")),
     *      @OA\Response(response="400", ref="#/components/responses/400"),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="409", ref="#/components/responses/409"),
     *      @OA\Response(response="500", ref="#/components/responses/500")
     * )
     */
    public function createPage(Request $request, Response $response): Response
    {
        $board = $request->getAttribute('board');
        $write = $request->getAttribute('write');
        $member = $request->getAttribute('member');

        try {
            if ($write['wr_is_comment']) {
                throw new HttpBadRequestException($request, '코멘트는 스크랩 할 수 없습니다.');
            }
            if ($this->scrap_service->existsScrap($member['mb_id'], $board['bo_table'], $write['wr_id'])) {
                throw new HttpConflictException($request, '이미 스크랩하신 글 입니다.');
            }

            $response_data = new CreateScrapPageResponse([
                'board' => $board,
                'write' => $write
            ]);
            return api_response_json($response, $response_data);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/member/scraps/{bo_table}/{wr_id}",
     *      summary="회원 스크랩 등록",
     *      tags={"스크랩"},
     *      security={{"Oauth2Password": {}}},
     *     description="
    회원 스크랩을 등록합니다.
    - 댓글을 작성하면 댓글도 함께 등록됩니다.
    ",
     *      @OA\PathParameter(name="bo_table", description="게시판 ID", @OA\Schema(type="string")),
     *      @OA\PathParameter(name="wr_id", description="게시글 ID", @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateScrapRequest"),
     *          )
     *      ),
     *      @OA\Response(response="200", description="스크랩 등록 성공", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *      @OA\Response(response="400", ref="#/components/responses/400"),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="409", ref="#/components/responses/409"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500")
     * )
     */
    public function create(Request $request, Response $response): Response
    {
        $board = $request->getAttribute('board');
        $write = $request->getAttribute('write');
        $member = $request->getAttribute('member');

        try {
            if ($write['wr_is_comment']) {
                throw new HttpBadRequestException($request, '코멘트는 스크랩 할 수 없습니다.');
            }
            if ($this->scrap_service->existsScrap($member['mb_id'], $board['bo_table'], $write['wr_id'])) {
                throw new HttpConflictException($request, '이미 스크랩하신 글 입니다.');
            }

            $request_body = $request->getParsedBody() ?? [];
            $request_data = new CreateScrapRequest($request_body);

            // 댓글 등록
            if ($request_data->wr_content) {
                $this->board_permission->createComment($member, $write);

                $comment_id = $this->comment_service->createCommentData($write, $request_data, $member);
                $this->write_service->updateWrite($write['wr_id'], ['wr_comment' => $write['wr_comment'] + 1, 'wr_last' => G5_TIME_YMDHIS]);

                $this->board_new_service->insert($board['bo_table'], $comment_id, $write['wr_id'], $member['mb_id']);
                $this->board_service->increaseCommentCount();

                $this->point_service->addPoint($member['mb_id'], $board['bo_comment_point'], "{$board['bo_subject']} {$write['wr_id']}-{$comment_id} 댓글쓰기", $board['bo_table'], $comment_id, '댓글');
            }

            $this->scrap_service->createScrap($member['mb_id'], $board['bo_table'], $write['wr_id']);

            $scrap_count = $this->scrap_service->fetchTotalScrapCount($member['mb_id']);
            $this->member_service->updateMember($member['mb_id'], ['mb_scrap_cnt' => $scrap_count]);

            return api_response_json($response, ['message' => '스크랩이 추가되었습니다.']);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/member/scraps/{ms_id}",
     *      summary="회원 스크랩 삭제",
     *      tags={"스크랩"},
     *      security={{"Oauth2Password": {}}},
     *      description="
    회원 스크랩을 삭제합니다.
    ",
     *      @OA\PathParameter(name="ms_id", description="스크랩 ID", @OA\Schema(type="integer")),
     *      @OA\Response(response="200", description="스크랩 삭제 성공", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *      @OA\Response(response="400", ref="#/components/responses/400"),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="500", ref="#/components/responses/500")
     * )
     */
    public function delete(Request $request, Response $response): Response
    {
        $member = $request->getAttribute('member');

        try {
            $scrap = $this->scrap_service->fetchScrapById($request->getAttribute('ms_id'));

            if (!$scrap) {
                throw new HttpNotFoundException($request, '존재하지 않는 스크랩 입니다.');
            }
            if ($scrap['mb_id'] !== $member['mb_id']) {
                throw new HttpForbiddenException($request, '본인의 스크랩만 삭제할 수 있습니다.');
            }

            $this->scrap_service->deleteScrap($scrap['ms_id']);

            $scrap_count = $this->scrap_service->fetchTotalScrapCount($member['mb_id']);
            $this->member_service->updateMember($member['mb_id'], ['mb_scrap_cnt' => $scrap_count]);

            return api_response_json($response, ['message' => '스크랩이 삭제되었습니다.']);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
