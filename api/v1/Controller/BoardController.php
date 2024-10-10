<?php

namespace API\v1\Controller;

use API\Exceptions\HttpBadRequestException;
use API\Exceptions\HttpForbiddenException;
use API\Exceptions\HttpNotFoundException;
use API\Service\BoardService;
use API\Service\BoardFileService;
use API\Service\BoardGoodService;
use API\Service\BoardNewService;
use API\Service\CommentService;
use API\Service\BoardPermission;
use API\Service\EncryptionService;
use API\Service\MemberImageService;
use API\Service\PointService;
use API\Service\ScrapService;
use API\Service\ThumbnailService;
use API\Service\WriteService;
use API\v1\Model\PageParameters;
use API\v1\Model\Request\Board\CreateWriteRequest;
use API\v1\Model\Request\Board\UpdateWriteRequest;
use API\v1\Model\Request\Comment\CreateCommentRequest;
use API\v1\Model\Request\Comment\UpdateCommentRequest;
use API\v1\Model\Request\Board\SearchRequest;
use API\v1\Model\Request\Board\UploadFileRequest;
use API\v1\Model\Response\Board\Board;
use API\v1\Model\Response\Board\CreateWriteResponse;
use API\v1\Model\Response\Board\GetWritesResponse;
use API\v1\Model\Response\Write\CommentResponse;
use API\v1\Model\Response\Write\FileResponse;
use API\v1\Model\Response\Write\GetCommentsResponse;
use API\v1\Model\Response\Write\GoodWriteResponse;
use API\v1\Model\Response\Write\NeighborWrite;
use API\v1\Model\Response\Write\Thumbnail;
use API\v1\Model\Response\Write\Write;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;
use Slim\Psr7\Stream;

class BoardController
{
    private BoardService $board_service;
    private BoardGoodService $board_good_service;
    private BoardNewService $board_new_service;
    private BoardPermission $board_permission;
    private BoardFileService $file_service;
    private CommentService $comment_service;
    private PointService $point_service;
    private ScrapService $scrap_service;
    private WriteService $write_service;

    private MemberImageService $image_service;

    public function __construct(
        BoardService $board_service,
        BoardGoodService $board_good_service,
        BoardNewService $board_new_service,
        BoardPermission $board_permission,
        BoardFileService $file_service,
        CommentService $comment_service,
        PointService $point_service,
        ScrapService $scrap_service,
        WriteService $write_service,
        MemberImageService $image_service
    ) {
        $this->board_service = $board_service;
        $this->board_good_service = $board_good_service;
        $this->board_new_service = $board_new_service;
        $this->board_permission = $board_permission;
        $this->file_service = $file_service;
        $this->comment_service = $comment_service;
        $this->point_service = $point_service;
        $this->scrap_service = $scrap_service;
        $this->write_service = $write_service;
        $this->image_service = $image_service;
    }

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
    public function getBoard(Request $request, Response $response): Response
    {
        $board = $request->getAttribute('board');

        $response_data = new Board($board);

        return api_response_json($response, $response_data);
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
     * @throws Exception
     */
    public function getWrites(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');
        $board = $request->getAttribute('board');
        $member = $request->getAttribute('member');

        try {
            // 권한 체크
            $this->board_permission->readWrites($member);

            // 검색 조건 및 페이징 처리
            $query_params = $request->getQueryParams();
            $page_rows = (int)($board['bo_page_rows'] ?? 0);
            $mobile_page_rows = (int)($board['bo_mobile_page_rows'] ?? 0);
            $page_params = new PageParameters($query_params, $config, $page_rows, $mobile_page_rows);
            $search_params = new SearchRequest($this->write_service, $config, $query_params);

            $total_records = $this->write_service->fetchTotalCount((array)$search_params);
            $total_page = ceil($total_records / $page_params->per_page);

            /**
             * - 공지사항을 별도로 출력합니다.
             * TODO: 목록에 필요한 데이터를 반환하도록 변경이 필요함
             * - images/normal_files => 파일갯수
             */
            // 공지글 목록 조회
            $notice_writes = [];
            if (!$search_params->is_search) {
                $fetch_notice_writes = $this->write_service->getNotice();
                $notice_writes = array_map(fn($notice_write) => new Write($notice_write), $fetch_notice_writes);
            }
            // 게시글 목록 조회
            $search_params = (array)$search_params;
            $get_writes = $this->write_service->getWrites($board, $search_params, (array)$page_params);
            $writes = array_map(fn($write) => new Write($write), $get_writes);

            // 게시글 목록 응답 데이터
            $response_data = new GetWritesResponse([
                'total_records' => $total_records,
                'total_pages' => $total_page,
                'current_page' => $page_params->page,
                'is_mobile' => $page_params->is_mobile,
                'categories' => $this->board_service->getCategories(),
                'board' => new Board($board),
                'notice_writes' => $notice_writes,
                'writes' => $writes,
                'prev_spt' => $this->write_service->getPrevSearchPart($search_params),
                'next_spt' => $this->write_service->getNextSearchPart($search_params),
            ]);

            return api_response_json($response, $response_data);
        } catch (Exception $e) {
            if ($e->getCode() === 403) {
                throw new HttpForbiddenException($request, $e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/boards/{bo_table}/writes/{wr_id}",
     *      summary="게시글 조회",
     *      tags={"게시판"},
     *      security={{"Oauth2Password": {}}},
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
     * @throws Exception
     */
    public function getWrite(Request $request, Response $response): Response
    {
        $board = $request->getAttribute('board');
        $write = $request->getAttribute('write');
        $member = $request->getAttribute('member');
        $params = $request->getQueryParams();

        // 권한 체크
        try {
            $this->board_permission->readWrite($member, $write);
            if (strpos($write['wr_option'], 'secret') !== false) {
                $thumb = [];
            } else {
                $thumb = $this->write_service->getBoardThumbnail($write, $board['bo_gallery_width'], $board['bo_gallery_height']);
            }

            $fetch_prev = $this->write_service->fetchPrevWrite($write, $params) ?: [];
            $fetch_next = $this->write_service->fetchNextWrite($write, $params) ?: [];
            $prev = new NeighborWrite($board['bo_table'], $fetch_prev);
            $next = new NeighborWrite($board['bo_table'], $fetch_next);
            $write['wr_email'] = EncryptionService::encrypt($write['wr_email']);
            $write['wr_ip'] = preg_replace('/([0-9]+).([0-9]+).([0-9]+).([0-9]+)/', G5_IP_DISPLAY, $write['wr_ip']);
            $write['wr_content'] = ThumbnailService::getThumbnailHtml($write['wr_content'], $board['bo_image_width']);

            $write_data = array_merge($write, array(
                'mb_icon_path' => $this->image_service->getMemberImagePath($write['mb_id'], 'icon'),
                'mb_image_path' => $this->image_service->getMemberImagePath($write['mb_id'], 'image'),
                'images' => (new FileResponse($this->file_service->getFilesByType((int)$write['wr_id'], 'image')))->files ?? [],
                'normal_files' => (new FileResponse($this->file_service->getFilesByType((int)$write['wr_id'], 'file')))->files ?? [],
                'thumbnail' => new Thumbnail($thumb),
                'prev' => $prev,
                'next' => $next
            ));

            $this->point_service->addPoint($member['mb_id'], $board['bo_read_point'], "{$board['bo_subject']} {$write['wr_id']} 글읽기", $board['bo_table'], $write['wr_id'], '읽기');

            $write = new Write($write_data);
            return api_response_json($response, $write);
        } catch (Exception $e) {
            if ($e->getCode() === 403) {
                throw new HttpForbiddenException($request, $e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/boards/{bo_table}/writes/{wr_id}",
     *      summary="게시글 조회 (비회원 비밀글)",
     *      tags={"게시판"},
     *      security={{"Oauth2Password": {}}},
     *      description="게시판의 게시글 1개를 조회합니다.",
     *       @OA\PathParameter(name="bo_table", description="게시판 코드", @OA\Schema(type="string")),
     *       @OA\PathParameter(name="wr_id", description="글 번호", @OA\Schema(type="integer")),
     *       @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *          @OA\Property(property="wr_password", type="string", description="게시글 비밀번호"),
     *         ),
     *     ),
     *      @OA\Response(response="200", description="게시판 비밀글 조회 성공", @OA\JsonContent(ref="#/components/schemas/Write")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="422", ref="#/components/responses/422")
     * )
     * @throws Exception
     */
    public function getSecretWrite(Request $request, Response $response): Response
    {
        $board = $request->getAttribute('board');
        $write = $request->getAttribute('write');
        $member = $request->getAttribute('member');
        $params = $request->getParsedBody();

        // 권한 체크
        try {
            $password = $params['wr_password'] ?? '';
            $this->board_permission->readWrite($member, $write, $password);
            $thumb = [];
            $fetch_prev = $this->write_service->fetchPrevWrite($write, $params) ?: [];
            $fetch_next = $this->write_service->fetchNextWrite($write, $params) ?: [];
            $prev = new NeighborWrite($board['bo_table'], $fetch_prev);
            $next = new NeighborWrite($board['bo_table'], $fetch_next);

            $write['wr_email'] = EncryptionService::encrypt($write['wr_email']);
            $write['wr_ip'] = preg_replace('/([0-9]+).([0-9]+).([0-9]+).([0-9]+)/', G5_IP_DISPLAY, $write['wr_ip']);
            $write['wr_content'] = ThumbnailService::getThumbnailHtml($write['wr_content'], $board['bo_image_width']);

            $write_data = array_merge($write, array(
                'mb_icon_path' => $this->image_service->getMemberImagePath($write['mb_id'], 'icon'),
                'mb_image_path' => $this->image_service->getMemberImagePath($write['mb_id'], 'image'),
                'images' => (new FileResponse($this->file_service->getFilesByType((int)$write['wr_id'], 'image')))->files ?? [],
                'normal_files' => (new FileResponse($this->file_service->getFilesByType((int)$write['wr_id'], 'file')))->files ?? [],
                'thumbnail' => new Thumbnail($thumb),
                'prev' => $prev,
                'next' => $next
            ));
            $this->point_service->addPoint($member['mb_id'], $board['bo_read_point'], "{$board['bo_subject']} {$write['wr_id']} 글읽기", $board['bo_table'], $write['wr_id'], '읽기');

            $write = new Write($write_data);
            return api_response_json($response, $write);
        } catch (Exception $e) {
            if ($e->getCode() === 403) {
                throw new HttpForbiddenException($request, $e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/boards/{bo_table}/writes/{wr_id}/comments",
     *      summary="댓글 조회",
     *      tags={"게시판"},
     *      security={{"Oauth2Password": {}}},
     *      description="게시글 1건의 댓글 목록을 조회합니다.",
     *      @OA\PathParameter(name="bo_table", description="게시판 코드", @OA\Schema(type="string")),
     *      @OA\PathParameter(name="wr_id", description="글 번호", @OA\Schema(type="integer")),
     *      @OA\Parameter(ref="#/components/parameters/page"),
     *      @OA\Parameter(ref="#/components/parameters/per_page"),
     *      @OA\Response(response="200", description="게시판 글 조회 성공", @OA\JsonContent(ref="#/components/schemas/GetCommentsResponse")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     * )
     * @throws Exception
     */
    public function getComments(Request $request, Response $response)
    {
        $board = $request->getAttribute('board');
        $write = $request->getAttribute('write');
        $member = $request->getAttribute('member');
        $config = $request->getAttribute('config');
        $query_params = $request->getQueryParams();

        try {
            // 권한 체크
            $this->board_permission->readWrites($member);

            // 검색 조건 및 페이징 처리
            $page_rows = (int)($query_params['per_page'] ?? $board['bo_page_rows']);
            $page_rows = $page_rows >= 100 ? 100 : $page_rows;
            $mobile_page_rows = (int)($board['bo_mobile_page_rows'] ?? 0);
            $page_params = new PageParameters($query_params, $config, $page_rows, $mobile_page_rows);
            $page = $page_params->page;
            $per_page = $page_params->per_page;

            $comments = $this->comment_service->getComments($write['wr_id'], $member['mb_id'], $page, $per_page);
            $total_count = $this->comment_service->fetchTotalRecords($write['wr_id']);

            $response_data = new GetCommentsResponse([
                'current_page' => $page,
                'total_pages' => ceil($total_count / $per_page),
                'total_records' => $total_count,
                'comments' => $comments,
            ]);

            return api_response_json($response, $response_data);
        } catch (Exception $e) {
            if ($e->getCode() === 403) {
                throw new HttpForbiddenException($request, $e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/boards/{bo_table}/writes/{wr_id}/comments/{comment_id}",
     *      summary="비밀 댓글 1건 조회",
     *      tags={"게시판"},
     *      security={{"Oauth2Password": {}}},
     *      description="게시글의 댓글 1건을 조회합니다.",
     *      @OA\PathParameter(name="bo_table", description="게시판 코드", @OA\Schema(type="string")),
     *      @OA\PathParameter(name="wr_id", description="게시글 번호", @OA\Schema(type="integer")),
     *      @OA\PathParameter(name="comment_id", description="댓글 번호", @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *       required=false,
     *       @OA\JsonContent(
     *       @OA\Property(property="wr_password", type="string", description="게시글 비밀번호"),
     *       )
     *      ),
     *      @OA\Response(response="200", description="댓글 조회 성공", @OA\JsonContent(ref="#/components/schemas/CommentResponse")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     * )
     * @throws Exception
     */
    public function getComment(Request $request, Response $response)
    {
        $member = $request->getAttribute('member');
        $comment_id = (int)$request->getAttribute('comment_id');
        $password = $request->getParsedBody()['wr_password'] ?? null;
        $comment = $request->getAttribute('comment');
        try {
            // 권한 체크
            $this->board_permission->readComment($member, $comment, $password);
            $comments = $this->comment_service->getComment($comment_id, $member['mb_id'], $password);
            $response_data = new CommentResponse([
                'comments' => $comments,
            ]);

            return api_response_json($response, $response_data);
        } catch (Exception $e) {
            if ($e->getCode() === 403) {
                throw new HttpForbiddenException($request, $e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/boards/{bo_table}/writes",
     *      summary="게시글 작성",
     *      tags={"게시판"},
     *      security={{"Oauth2Password": {}}},
     *      description="지정된 게시판에 새 글을 작성합니다.",
     *      @OA\PathParameter(name="bo_table", description="게시판 코드", @OA\Schema(type="string")),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateWriteRequest"),
     *          )
     *      ),
     *      @OA\Response(response="200", description="게시글 작성 성공", @OA\JsonContent(ref="#/components/schemas/CreateWriteResponse")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     * @throws Exception
     */
    public function createWrite(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');
        $member = $request->getAttribute('member');
        $group = $request->getAttribute('group');
        $board = $request->getAttribute('board');

        run_event('api_create_write_before', $board, $group);

        try {

            // 데이터 검증 및 처리
            $request_body = $request->getParsedBody();
            $request_data = new CreateWriteRequest($this->board_permission, $member, $request_body);

            $secret = $request_body['secret'] ?? false;
            $is_notice = $request_body['notice'] ?? false;
            $parent_write = [];
            if ($request_data->wr_parent) {
                $parent_write = $this->write_service->fetchWrite($request_data->wr_parent);
            }

            // 권한 체크
            if ($is_notice) {
                $this->board_permission->createNotice($member);
            }
            if ($request_data->wr_parent) {
                $this->board_permission->createReply($member, $parent_write);
            } else {
                $this->board_permission->createWrite($member);
            }

            // 게시글 등록
            $wr_id = $this->write_service->createWriteData($request_data, $member, $parent_write);
            $this->write_service->updateWriteParentId($wr_id, $wr_id);

            if ($is_notice) {
                $bo_notice = $this->board_service->getBoardNoticeIds($board['bo_notice'], $wr_id, true);
                $this->board_service->updateBoard(['bo_notice' => $bo_notice]);
            }

            $this->board_new_service->insert($board['bo_table'], $wr_id, $wr_id, $member['mb_id']);
            $this->board_service->increaseWriteCount();

            // 게시글 등록 후 처리
            $this->point_service->addPoint($member['mb_id'], $board['bo_write_point'], "{$board['bo_subject']} {$wr_id} 글쓰기", $board['bo_table'], $wr_id, '쓰기');

            run_event('api_create_write_after', $board, $group, $wr_id, $parent_write);

            $response_data = new CreateWriteResponse('success', $wr_id);
            return api_response_json($response, $response_data, 201);
        } catch (Exception $e) {
            if ($e->getCode() === 403) {
                throw new HttpForbiddenException($request, $e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/boards/{bo_table}/writes/{wr_id}",
     *      summary="게시글 수정",
     *      tags={"게시판"},
     *      security={{"Oauth2Password": {}}},
     *      description="지정된 게시판의 글을 수정합니다.",
     *      @OA\PathParameter(name="bo_table", description="게시판 코드", @OA\Schema(type="string")),
     *      @OA\PathParameter(name="wr_id", description="글 번호", @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateWriteRequest"),
     *          )
     *      ),
     *      @OA\Response(response="200", description="게시글 수정 성공", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     * @throws Exception
     */
    public function updateWrite(Request $request, Response $response): Response
    {
        $group = $request->getAttribute('group');
        $board = $request->getAttribute('board');
        $write = $request->getAttribute('write');
        $member = $request->getAttribute('member');

        run_event('api_update_write_before', $board, $write['wr_id']);

        // 데이터 검증 및 처리
        try {
            $request_body = $request->getParsedBody();
            $request_data = new UpdateWriteRequest($this->board_permission, $write, $member, $request_body);

            $secret = $request_body['secret'] ?? false;
            $is_notice = $request_body['notice'] ?? false;

            // 권한 체크
            if ($is_notice) {
                $this->board_permission->createNotice($member);
            }
            if ($write['mb_id']) {
                $this->board_permission->updateWrite($member, $write);
            } else {
                $wr_password = $request_body['wr_password'] ?? '';
                $this->board_permission->updateWriteByGuest($member, $write, $wr_password);
                unset($request_data->wr_password);
            }

            // 게시글 수정
            $this->write_service->updateWriteData($write, $request_data);
            $this->write_service->updateCategoryByParentId($write['wr_id'], $request_data->ca_name);

            $bo_notice = $this->board_service->getBoardNoticeIds($board['bo_notice'], $write['wr_id'], $is_notice);
            $this->board_service->updateBoard(['bo_notice' => $bo_notice]);

            run_event('api_update_write_after', $board, $write['wr_id']);

            return api_response_json($response, array('message' => '게시글이 수정되었습니다.'));
        } catch (Exception $e) {
            if ($e->getCode() === 403) {
                throw new HttpForbiddenException($request, $e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/boards/{bo_table}/writes/{wr_id}/files",
     *      summary="게시글 파일 업로드",
     *      tags={"게시판"},
     *      security={{"Oauth2Password": {}}},
     *      description="
    파일을 업로드합니다.
    - multipart/form-data로 전송해야 합니다.
    - 게시판에 설정된 파일 업로드 제한에 따라 파일을 업로드할 수 있습니다.
    - 업로드 파일 갯수
    - 파일 크기
    - 파일 설명
    ",
     *      @OA\PathParameter(name="bo_table", description="게시판 코드", @OA\Schema(type="string")),
     *      @OA\PathParameter(name="wr_id", description="글 번호", @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              encoding={
     *                  "file_contents[]": {"explode": true},
     *                  "file_dels[]": {"explode": true}
     *              },
     *              @OA\Schema(ref="#/components/schemas/UploadFileRequest"),
     *          )
     *      ),
     *      @OA\Response(response="200", description="게시글 파일 업로드 성공", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     * @throws Exception
     */
    public function uploadFiles(Request $request, Response $response): Response
    {
        $board = $request->getAttribute('board');
        $write = $request->getAttribute('write');
        $member = $request->getAttribute('member');

        $request_data = $request->getParsedBody();
        $uploaded_data = $request->getUploadedFiles();

        try {
            // 데이터 검증 및 처리
            $upload_files = new UploadFileRequest($this->file_service, $board, $write, $uploaded_data, $request_data);

            // 권한 체크
            if ($member['mb_id']) {
                $this->board_permission->uploadFiles($member, $write);
            } else {
                $this->board_permission->uploadFilesByGuest($member, $write, $request_data['wr_password'] ?? '');
            }

            // 파일 업로드
            $this->file_service->createDirectoryIfNotExists();
            $this->file_service->deleteWriteFilesByNo($write['wr_id'], (array)$upload_files->file_dels);
            $this->file_service->uploadFiles($write['wr_id'], (array)$upload_files);

            // 게시글에 파일 갯수 갱신
            $files = $this->file_service->fetchWriteFiles($write['wr_id']);
            $this->write_service->updateWrite($write['wr_id'], ['wr_file' => count($files)]);

            return api_response_json($response, array('message' => '파일 정보가 갱신되었습니다.'));
        } catch (Exception $e) {
            if ($e->getCode() === 403) {
                throw new HttpForbiddenException($request, $e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/boards/{bo_table}/writes/{wr_id}/files/{bf_no}",
     *      summary="게시글 파일 다운로드",
     *      tags={"게시판"},
     *      security={{"Oauth2Password": {}}},
     *      description="게시글의 파일을 다운로드합니다. (0byte 인 파일이 업로드 된경우 API 는 작동하나 스웨거에서는 다운로드가 안됩니다.)",
     *      @OA\PathParameter(name="bo_table", description="게시판 코드", @OA\Schema(type="string")),
     *      @OA\PathParameter(name="wr_id", description="글 번호", @OA\Schema(type="integer")),
     *      @OA\PathParameter(name="bf_no", description="파일 번호", @OA\Schema(type="integer")),
     *      @OA\Response(
     *          response=200,
     *          description="파일 다운로드 성공",
     *          @OA\Header(
     *              header="Content-Disposition",
     *              description="첨부 파일명",
     *              @OA\Schema(type="string")
     *          ),
     *          @OA\MediaType(
     *              mediaType="application/octet-stream",
     *              @OA\Schema(
     *                  type="string",
     *                  format="binary"
     *              )
     *          )
     *      ),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function downloadFile(Request $request, Response $response): Response
    {
        $board = $request->getAttribute('board');
        $write = $request->getAttribute('write');
        $member = $request->getAttribute('member');

        // 파일 정보 조회
        $file = $this->file_service->fetchWriteFileByNo($write['wr_id'], $request->getAttribute('bf_no'));
        if (!$file) {
            throw new HttpNotFoundException($request, '파일정보가 존재하지 않습니다.');
        }
        $file_path = G5_DATA_PATH . '/file/' . $board['bo_table'] . '/' . $file['bf_file'];
        if (!file_exists($file_path)) {
            throw new HttpNotFoundException($request, '파일이 존재하지 않습니다.');
        }
        
        $this->file_service->increaseDownloadCount($write['wr_id'], $request->getAttribute('bf_no'));

        // 권한 체크
        try {
            $this->board_permission->downloadFiles($member, $write);
        } catch (Exception $e) {
            throw new HttpForbiddenException($request, $e->getMessage());
        }

        $file_size = $file['bf_filesize'];
        $file_name = $file['bf_source'];
        $encoded_file_name = rawurlencode($file_name);

        $response = $response
            ->withHeader('Content-Description', 'web site')
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $encoded_file_name . '"')
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withHeader('Pragma', 'public')
            ->withHeader('Content-Length', $file_size);
        $file = fopen($file_path, 'rb');
        
        return $response->withBody(new Stream($file));
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/boards/{bo_table}/writes/{wr_id}",
     *      summary="게시글 삭제",
     *      tags={"게시판"},
     *      security={{"Oauth2Password": {}}},
     *      description="지정된 게시판의 글을 삭제합니다.",
     *      @OA\PathParameter(name="bo_table", description="게시판 코드", @OA\Schema(type="string")),
     *      @OA\PathParameter(name="wr_id", description="글 번호", @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(@OA\Property(property="wr_password", type="string", description="게시글 비밀번호(비회원 글일 경우 필수)"))
     *           )
     *      ),
     *      @OA\Response(response="200", description="게시글 삭제 성공", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     * @throws Exception
     */
    public function deleteWrite(Request $request, Response $response): Response
    {
        $board = $request->getAttribute('board');
        $write = $request->getAttribute('write');
        $member = $request->getAttribute('member');

        try {
            // 권한 체크
            if ($write['mb_id']) {
                $this->board_permission->deleteWrite($member, $write);
            } else {
                $wr_password = $request->getParsedBody()['wr_password'] ?? '';
                $this->board_permission->deleteWriteByGuest($member, $write, $wr_password);
            }

            // 포인트&파일 삭제
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

            $this->board_new_service->deleteByWrite($board['bo_table'], $write['wr_id']);
            $this->scrap_service->deleteScrapByWrite($board['bo_table'], $write['wr_id']);
            $this->comment_service->deleteAllCommentByParent($write['wr_id']);

            $bo_notice = $this->board_service->getBoardNoticeIds($board['bo_notice'], $write['wr_id'], false);
            $this->board_service->updateBoard(['bo_notice' => $bo_notice]);

            $this->board_service->decreaseWriteAndCommentCount($count_writes, $count_comments);

            run_event('api_delete_write_after', $write, $board);

            return api_response_json($response, array('message' => '게시글이 삭제되었습니다.'));
        } catch (Exception $e) {
            if ($e->getCode() === 403) {
                throw new HttpForbiddenException($request, $e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/boards/{bo_table}/writes/{wr_id}/comments",
     *      summary="댓글 작성",
     *      tags={"게시판"},
     *      security={{"Oauth2Password": {}}},
     *      description="지정된 게시판의 글에 댓글을 작성합니다.",
     *      @OA\PathParameter(name="bo_table", description="게시판 코드", @OA\Schema(type="string")),
     *      @OA\PathParameter(name="wr_id", description="글 번호", @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateCommentRequest"),
     *          )
     *      ),
     *      @OA\Response(response="200", description="댓글 작성 성공", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     * @throws Exception
     */
    public function createComment(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');
        $board = $request->getAttribute('board');
        $group = $request->getAttribute('group');
        $write = $request->getAttribute('write');
        $member = $request->getAttribute('member');

        try {

            // 데이터 검증 및 처리
            $request_body = $request->getParsedBody();
            $request_data = new CreateCommentRequest($board, $member, $request_body);

            $parent_comment = [];
            if ($request_data->comment_id) {
                $parent_comment = $this->write_service->fetchWrite($request_data->comment_id);
                if (!$parent_comment) {
                    throw new HttpNotFoundException($request, '부모 댓글 정보가 존재하지 않습니다.');
                }
                if ($write['wr_id'] != $parent_comment['wr_parent']) {
                    throw new HttpBadRequestException($request, '부모 댓글 정보가 올바르지 않습니다.');
                }
            }
            unset($request_data->comment_id);

            // 권한 체크
            $this->board_permission->createComment($member, $write);

            // 댓글 등록
            $comment_id = $this->comment_service->createCommentData($write, $request_data, $member, $parent_comment);
            $this->write_service->updateWrite($write['wr_id'], ['wr_comment' => $write['wr_comment'] + 1, 'wr_last' => G5_TIME_YMDHIS]);

            $this->board_new_service->insert($board['bo_table'], $comment_id, $write['wr_id'], $member['mb_id']);
            $this->board_service->increaseCommentCount();

            $this->point_service->addPoint($member['mb_id'], $board['bo_comment_point'], "{$board['bo_subject']} {$write['wr_id']}-{$comment_id} 댓글쓰기", $board['bo_table'],
                $comment_id, '댓글');

            run_event('api_create_comment_after', $board, $write, $comment_id, $parent_comment);

            return api_response_json($response, ['message' => '댓글이 등록되었습니다.'], 201);
        } catch (Exception $e) {
            if ($e->getCode() === 403) {
                throw new HttpForbiddenException($request, $e->getMessage());
            }

            if ($e->getCode() === 400) {
                throw new HttpBadRequestException($request, $e->getMessage());
            }
            throw $e;
        }
    }

    /**
     * @OA\Put(
     *      path="/api/v1/boards/{bo_table}/writes/{wr_id}/comments/{comment_id}",
     *      summary="댓글 수정",
     *      tags={"게시판"},
     *      security={{"Oauth2Password": {}}},
     *      description="지정된 게시판의 글에 작성된 댓글을 수정합니다.",
     *      @OA\PathParameter(name="bo_table", description="게시판 코드", @OA\Schema(type="string")),
     *      @OA\PathParameter(name="wr_id", description="글 번호", @OA\Schema(type="integer")),
     *      @OA\PathParameter(name="comment_id", description="댓글 번호", @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateCommentRequest"),
     *          )
     *      ),
     *      @OA\Response(response="200", description="댓글 수정 성공", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     * @throws Exception
     */
    public function updateComment(Request $request, Response $response): Response
    {
        $comment = $request->getAttribute('comment');
        $member = $request->getAttribute('member');

        try {
            // 데이터 검증 및 처리
            $request_body = $request->getParsedBody();
            $request_data = new UpdateCommentRequest($member, $request_body);

            // 권한 체크
            if ($comment['mb_id']) {
                $this->board_permission->updateComment($member, $comment);
            } else {
                $wr_password = $request_body['wr_password'] ?? '';
                $this->board_permission->updateCommentByGuest($member, $comment, $wr_password);
                unset($request_data->wr_password);
            }

            // 댓글 수정
            $this->comment_service->updateCommentData($comment['wr_id'], $request_data);

            return api_response_json($response, array('message' => '댓글이 수정되었습니다.'));
        } catch (Exception $e) {
            if ($e->getCode() === 403) {
                throw new HttpForbiddenException($request, $e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/boards/{bo_table}/writes/{wr_id}/comments/{comment_id}",
     *      summary="댓글 삭제",
     *      tags={"게시판"},
     *      security={{"Oauth2Password": {}}},
     *      description="지정된 게시판의 글에 작성된 댓글을 삭제합니다.",
     *      @OA\PathParameter(name="bo_table", description="게시판 코드", @OA\Schema(type="string")),
     *      @OA\PathParameter(name="wr_id", description="글 번호", @OA\Schema(type="integer")),
     *      @OA\PathParameter(name="comment_id", description="댓글 번호", @OA\Schema(type="integer")),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(@OA\Property(property="wr_password", type="string", description="댓글 비밀번호(비회원 댓글일 경우 필수)"))
     *           )
     *      ),
     *      @OA\Response(response="200", description="댓글 삭제 성공", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     * @throws Exception
     */
    public function deleteComment(Request $request, Response $response): Response
    {
        $board = $request->getAttribute('board');
        $write = $request->getAttribute('write');
        $comment = $request->getAttribute('comment');
        $member = $request->getAttribute('member');

        // 권한 체크
        try {
            if ($comment['mb_id']) {
                $this->board_permission->deleteComment($member, $comment);
            } else {
                $wr_password = $request->getParsedBody()['wr_password'] ?? '';
                $this->board_permission->deleteCommentByGuest($member, $comment, $wr_password);
            }

            // 댓글 삭제
            $this->comment_service->deleteCommentById($comment['wr_id']);

            // 댓글 포인트 삭제
            if (!$this->point_service->removePoint($comment['mb_id'], $board['bo_table'], $comment['wr_id'], '댓글')) {
                $this->point_service->addPoint($comment['mb_id'], $board['bo_comment_point'] * (-1), "{$board['bo_subject']} {$comment['wr_parent']}-{$comment['wr_id']} 댓글삭제");
            }

            // 게시물에 대한 최근 시간을 다시 얻어 정보를 갱신한다. (wr_last, wr_comment)
            $last = $this->write_service->fetchWriteCommentLast($write);
            $this->write_service->updateWrite($write['wr_id'], ['wr_comment' => $write['wr_comment'] - 1, 'wr_last' => $last['wr_last']]);
            $this->board_new_service->deleteByComment($board['bo_table'], $comment['wr_id']);

            return api_response_json($response, array('message' => '댓글이 삭제되었습니다.'));
        } catch (Exception $e) {
            if ($e->getCode() === 403) {
                throw new HttpForbiddenException($request, $e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/boards/{bo_table}/writes/{wr_id}/{good_type}",
     *      summary="게시글 추천/비추천",
     *      tags={"게시판"},
     *      security={{"Oauth2Password": {}}},
     *      description="게시글에 대한 추천/비추천을 처리합니다.",
     *      @OA\PathParameter(name="bo_table", description="게시판 코드", @OA\Schema(type="string")),
     *      @OA\PathParameter(name="wr_id", description="글 번호", @OA\Schema(type="integer")),
     *      @OA\PathParameter(name="good_type", description="추천타입", @OA\Schema(type="string", default="good", enum={"good", "nogood"}) ),
     *      @OA\Response(response="200", description="추천/비추천 성공", @OA\JsonContent(ref="#/components/schemas/GoodWriteResponse")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     * @throws Exception
     */
    public function goodWrite(Request $request, Response $response, array $args): Response
    {
        $board = $request->getAttribute('board');
        $write = $request->getAttribute('write');
        $member = $request->getAttribute('member');
        $good_type = $args['good_type'] ?? '';

        if (!in_array($good_type, ['good', 'nogood'])) {
            throw new HttpBadRequestException($request, '추천 타입이 올바르지 않습니다.');
        }

        try {
            // 권한 체크
            $this->board_permission->goodWrite($member['mb_id'], $write, $good_type);

            // 추천/비추천 처리
            $this->board_good_service->insertGood($member['mb_id'], $board['bo_table'], $write['wr_id'], $good_type);
            $this->write_service->updateWriteGood($write['wr_id'], $good_type);

            $write = $this->write_service->fetchWrite((int)$write['wr_id']);
            if (!$write) {
                throw new HttpNotFoundException($request, '게시글 정보가 존재하지 않습니다.');
            }
            $word = $this->board_good_service->getGoodTypeWord($good_type);
            $response_data = new GoodWriteResponse([
                'message' => "해당 글을 {$word}하였습니다.",
                'good' => $write['wr_good'],
                'nogood' => $write['wr_nogood']
            ]);
            return api_response_json($response, $response_data);
        } catch (Exception $e) {
            if ($e->getCode() === 403) {
                throw new HttpForbiddenException($request, $e->getMessage());
            }

            throw $e;
        }
    }
}
