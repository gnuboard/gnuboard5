<?php

namespace API\v1\Controller;

use API\Exceptions\HttpBadRequestException;
use API\Exceptions\HttpNotFoundException;
use API\Exceptions\HttpUnprocessableEntityException;
use API\Service\AutosaveService;
use API\v1\Model\Request\Autosave\CreateAutosaveRequest;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class AutosaveController
{

    private AutosaveService $autosave_service;

    public function __construct()
    {
        $this->autosave_service = new AutosaveService();
    }

    /**
     * @OA\Get (
     *     path="/api/v1/autosaves",
     *     summary="임시저장된 글 목록 조회",
     *     tags={"자동 임시저장"},
     *     security={{"Oauth2Password": {}}},
     *     @OA\Response (
     *       response="200",
     *       description="임시저장된 글 목록 조회 성공",
     *       @OA\JsonContent(ref="#/components/schemas/AutosaveListResponse")
     *     )
     * )
     */
    public function index(Request $request, Response $response)
    {
        $request_body = $request->getParsedBody();
        $member = $request->getAttribute('member');
        $mb_id = $member['mb_id'];
        $page = (int)($request_body['page'] ?? 1);
        $per_page = (int)($request_body['per_page'] ?? 10);

        if ($page < 1) {
            $page = 1;
        }

        if ($per_page < 1) {
            $per_page = 10;
        }

        if ($per_page > 100) {
            throw new HttpBadRequestException($request, '한번에 100개 이상 조회할 수 없습니다.');
        }

        $response_data = $this->autosave_service->getAutosaves($mb_id, $page, $per_page);
        if (!$response_data) {
            throw new HttpNotFoundException($request, '임시저장된 글이 없습니다.');
        }
        return api_response_json($response, $response_data);
    }

    /**
     * 임시저장 글 저장
     * @OA\Post (
     *   path="/api/v1/autosaves",
     *   summary="임시저장 글 저장",
     *   tags={"자동 임시저장"},
     *   security={{"Oauth2Password": {}}},
     *   @OA\RequestBody(
     *    required=true,
     *     @OA\MediaType(
     *     mediaType="application/json",
     *     @OA\Schema(ref="#/components/schemas/CreateAutosaveRequest")
     *     )
     *   ),
     *   @OA\Response (
     *    response="200",
     *    description="임시저장 성공",
     *    @OA\JsonContent(
     *      @OA\Property(property="message", type="string"),
     *      @OA\Property(property="as_id", type="string")
     *    )
     *   )
     * )
     */
    public function save(Request $request, Response $response)
    {
        $member = $request->getAttribute('member');
        $request_body = $request->getParsedBody();
        $data = new createAutosaveRequest($request_body);
        $result = $this->autosave_service->createAutosave($member['mb_id'], (array)$data);

        if (!$result) {
            throw new HttpBadRequestException($request, '임시저장에 실패했습니다.');
        }

        return api_response_json($response, ['message' => '임시저장되었습니다.', 'as_id' => $result]);
    }

    /**
     * 임시저장 글 삭제
     * @OA\Delete (
     * path="/api/v1/autosaves/{as_id}",
     * summary="임시저장 글 삭제",
     * tags={"자동 임시저장"},
     * security={{"Oauth2Password": {}}},
     * @OA\Parameter (
     *   name="as_id",
     *   in="path",
     *   description="임시저장 아이디",
     *   required=true,
     *   @OA\Schema(type="integer")
     * ),
     * @OA\Response (
     *   response="200",
     *   description="임시저장 글 삭제 성공",
     *   @OA\JsonContent(
     *     @OA\Property(property="message", type="string")
     *   )
     *  )
     * )
     */
    public function delete(Request $request, Response $response, array $args)
    {
        $member = $request->getAttribute('member');
        $as_id = $args['as_id'] ?? '';

        if (!$as_id) {
            throw new HttpUnprocessableEntityException($request, '임시저장 아이디가 필요합니다.');
        }

        $result = $this->autosave_service->deleteAutosave($member['mb_id'], $as_id);

        if (!$result) {
            throw new HttpBadRequestException($request, '임시저장 글 삭제에 실패했습니다.');
        }

        return api_response_json($response, ['message' => '임시저장 글이 삭제되었습니다.']);
    }

    /**
     * 임시저장된 글 조회
     * @OA\Get (
     *     path="/api/v1/autosaves/{as_id}",
     *     summary="임시저장된 글 조회",
     *     tags={"자동 임시저장"},
     *     security={{"Oauth2Password": {}}},
     *     @OA\Parameter (
     *       name="as_id",
     *       in="path",
     *       description="임시저장 아이디",
     *       required=true,
     *       @OA\Schema(type="integer")
     *     ),
     *     @OA\Response (
     *       response="200",
     *       description="임시저장된 글 조회 성공",
     *       @OA\JsonContent(ref="#/components/schemas/Autosave")
     *     )
     * )
     */
    public function show(Request $request, Response $response)
    {
        $member = $request->getAttribute('member');
        $as_id = $request->getAttribute('as_id');
        $mb_id = $member['mb_id'];

        if (!$as_id) {
            throw new HttpUnprocessableEntityException($request, '임시저장 아이디가 필요합니다.');
        }

        $response_data = $this->autosave_service->fetchAutosave($mb_id, $as_id);
        if (!$response_data) {
            throw new HttpNotFoundException($request, '임시저장된 글이 없습니다.');
        }
        return api_response_json($response, $response_data);
    }

    /**
     * 회원의 임시저장된글 카운트
     * @OA\Get (
     *   path="/api/v1/autosaves/count",
     *   summary="임시저장된 글 갯수 조회",
     *   tags={"자동 임시저장"},
     *   security={{"Oauth2Password": {}}},
     *   @OA\Response (
     *     response="200",
     *     description="임시저장된 글 갯수 조회 성공",
     *       @OA\JsonContent(
     *         @OA\Property(property="count", type="integer")
     *       )
     *   )
     * )
     *
     */
    public function getCount(Request $request, Response $response)
    {
        $member = $request->getAttribute('member');
        $count = $this->autosave_service->getCount($member['mb_id']);
        return api_response_json($response, ['count' => $count]);
    }

}