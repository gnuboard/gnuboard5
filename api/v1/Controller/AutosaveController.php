<?php

namespace API\v1\Controller;

use API\Service\AutosaveService;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * @OA\Tag(name="자동 임시저장", description="자동 저장 API")
 */
class AutosaveController
{

    private $autosave_service;

    public function __construct()
    {
        $this->autosave_service = new AutosaveService();
    }

    /**
     * @OA\Get (
     *     path="/api/v1/autosaves",
     *     summary="임시저장된 글 목록 조회",
     *     tags={"자동 임시저장"},
     * security={{"bearerAuth": {}}},
     * @OA\Response (
     *     response="200",
     *     description="임시저장된 글 목록 조회 성공",
     *     @OA\JsonContent(ref="#/components/schemas/AutosaveListResponse")
     * )
     * )
     */
    public function index(Request $request, Response $response)
    {
        $body = $request->getParsedBody();
        $member = $request->getAttribute('member');
        $mb_id = $member['mb_id'];
        $page = (int)($body['page'] ?? 1);
        $per_page = (int)($body['per_page'] ?? 10);

        if ($page < 1) {
            $page = 1;
        }

        if ($per_page < 1) {
            $per_page = 10;
        }

        if ($per_page > 100) {
            return api_response_json($response, ['message' => '한번에 100개 이상 조회할 수 없습니다.'], 404);
        }

        $response_data = $this->autosave_service->fetch_autosaves($mb_id, $page, $per_page);
        if (!$response_data) {
            return api_response_json($response, ['message' => '임시저장된 글이 없습니다.'], 404);
        }
        return api_response_json($response, $response_data);
    }

    /**
     * 임시저장된 글 조회
     * @OA\Get (
     *     path="/api/v1/autosaves/{as_id}",
     *     summary="임시저장된 글 조회",
     *     tags={"자동 임시저장"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter (
     *      name="as_id",
     *      in="path",
     *      description="임시저장 아이디",
     *      required=true,
     *      @OA\Schema(type="integer")
     *    ),
     *     @OA\Response (
     *      response="200",
     *      description="임시저장된 글 조회 성공",
     *     @OA\JsonContent(ref="#/components/schemas/Autosave"))
     * )
     */
    public function show(Request $request, Response $response)
    {
        $params = $request->getQueryParams();
        $member = $request->getAttribute('member');
        $mb_id = $member['mb_id'];
        $as_id = $params['as_id'] ?? '';

        if (!$as_id) {
            return api_response_json($response, ['message' => '임시저장 아이디가 필요합니다.'], 422);
        }

        $response_data = $this->autosave_service->fetch_autosave($mb_id, $as_id);
        if (!$response_data) {
            return api_response_json($response, ['message' => '임시저장된 글이 없습니다.'], 404);
        }
        return api_response_json($response, $response_data);
    }

    /**
     * 회원의 임시저장된글 카운트
     *     
     */
    public function get_count()
    {
        
    }
}