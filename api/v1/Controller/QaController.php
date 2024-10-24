<?php

namespace API\v1\Controller;

use API\Exceptions\HttpBadRequestException;
use API\Exceptions\HttpForbiddenException;
use API\Service\ConfigService;
use API\Service\QaService;
use API\v1\Model\Request\Qa\QaFileUploadRequest;
use API\v1\Model\Request\Qa\QaListRequest;
use API\v1\Model\Request\Qa\QaRequest;
use API\v1\Model\Request\Qa\QaUpdateRequest;
use API\v1\Model\Response\Qa\QaConfigResponse;
use API\v1\Model\Response\Qa\QaListResponse;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class QaController
{

    private QaService $qa_service;

    public function __construct(QaService $qa_service)
    {
        $this->qa_service = $qa_service;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/qa/config",
     *     tags={"Q&A"},
     *     summary="Q&A 설정 조회",
     *     description="Q&A 설정 조회",
     *     security={{"Oauth2Password": {}}},
     *     @OA\Response(
     *       response=200,
     *       description="Q&A 설정 조회 성공",
     *       @OA\JsonContent(ref="#/components/schemas/QaConfigResponse")
     *     ),
     * )
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function getQaConfig(Request $request, Response $response)
    {
        $qa_config = $this->qa_service->fetchQaConfig();
        $response_data = new QaConfigResponse($qa_config);
        return api_response_json($response, $response_data);
    }


    /**
     * @OA\Get(
     *     path="/api/v1/qa",
     *     tags={"Q&A"},
     *     summary="Q&A 목록 조회",
     *     description="Q&A 목록 조회",
     *     security={{"Oauth2Password": {}}},
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/QaListRequest"),
     *       )
     *     ),
     *     @OA\Response(
     *       response=200,
     *       description="Q&A 목록 조회 성공",
     *       @OA\JsonContent(ref="#/components/schemas/QaListResponse")
     *     ),
     *     @OA\Response(
     *       response=401,
     *       description="로그인이 필요합니다."
     *     )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function index(Request $request, Response $response)
    {
        $member = $request->getAttribute('member');
        $request_body = $request->getParsedBody();
        $data = new QaListRequest($request_body);
        $total = $this->qa_service->fetchCountQaList($member['mb_id'], $data->sca, $data->stx, $data->sfl);
        $qa_list = $this->qa_service->getQaList($member['mb_id'], $data->sca, $data->stx, $data->sfl, $data->page, $data->per_page);

        $response_data = new QaListResponse(array_merge($qa_list, [
                'total_records' => $total,
                'total_pages' => ceil($total / $data->per_page),
            ]
        ));

        return api_response_json($response, $response_data);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/qa/{qa_id}",
     *     tags={"Q&A"},
     *     summary="Q&A 상세 조회",
     *     description="Q&A 상세 조회",
     *     security={{"Oauth2Password": {}}},
     *     @OA\Parameter(
     *       name="qa_id",
     *       in="path",
     *       description="Q&A ID"
     *     ),
     *     @OA\Response(
     *       response=200,
     *       description="Q&A 상세 조회 성공",
     *       @OA\JsonContent(ref="#/components/schemas/QaRequest")
     *     ),
     *     @OA\Response(
     *       response=401,
     *       description="로그인이 필요합니다."
     *     ),
     *     @OA\Response(
     *       response=404,
     *       description="Q&A를 찾을 수 없습니다."
     *     )
     * )
     *
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function show(Request $request, Response $response)
    {
        $qa_id = $request->getAttribute('qa_id');
        $qa = $this->qa_service->fetchQa($qa_id);

        return api_response_json($response, $qa);
    }

    /**
     * @OA\Post(
     *    path="/api/v1/qa",
     *    tags={"Q&A"},
     *    summary="Q&A 등록",
     *    description="Q&A 등록",
     *    security={{"Oauth2Password": {}}},
     *    @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *        mediaType="application/json",
     *        @OA\Schema(ref="#/components/schemas/QaRequest"),
     *      )
     *    ),
     *    @OA\Response(
     *      response=200,
     *      description="Q&A 등록 성공",
     *      @OA\JsonContent(ref="#/components/schemas/BaseResponse")
     *    ),
     *    @OA\Response(
     *      response=401,
     *      description="로그인이 필요합니다."
     *   )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function create(Request $request, Response $response)
    {
        $member = $request->getAttribute('member');
        $request_body = $request->getParsedBody();
        $request_data = new QaRequest($request_body);
        $request_data->qa_name = $member['mb_name'];
        $request_data->qa_ip = $_SERVER['REMOTE_ADDR'];

        if (!in_array($request_data->qa_category, $this->qa_service->getCategory())) {
            throw new HttpBadRequestException($request, '카테고리를 확인해주세요.');
        }

        $last_insert_id = $this->qa_service->createQa((array)$request_data);
        $this->qa_service->updateQaRelate($last_insert_id);

        return api_response_json($response, ['qa_id' => $last_insert_id]);
    }

    /**
     * @OA\Put (
     *     path="/api/v1/qa/{qa_id}",
     *     tags={"Q&A"},
     *     summary="Q&A 수정",
     *     description="Q&A 수정",
     *     security={{"Oauth2Password": {}}},
     *     @OA\Parameter(
     *     name="qa_id",
     *     in="path",
     *     description="Q&A ID"
     *     ),
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(ref="#/components/schemas/QaUpdateRequest"),
     *       )
     *     ),
     *     @OA\Response(
     *       response=200,
     *       description="Q&A 수정 성공",
     *       @OA\JsonContent(ref="#/components/schemas/BaseResponse")
     *     ),
     *     @OA\Response(
     *       response=401,
     *       description="로그인이 필요합니다."
     *     )
     * )
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function update(Request $request, Response $response)
    {
        $member = $request->getAttribute('member');
        $config = ConfigService::getConfig();
        $request_body = $request->getParsedBody();
        $request_data = new QaUpdateRequest($request_body);
        $qa_id = $request->getAttribute('qa_id');

        // 답변이 완료되면 수정 못함.
        $qa = $this->qa_service->fetchQa($qa_id);

        if (!is_super_admin($config, $member['mb_id'])) {
            if ($qa['mb_id'] !== $member['mb_id']) {
                return api_response_json($response, ['message' => '게시글을 삭제할 권한이 없습니다.']);
            }
        }

        $this->qa_service->checkIsReplied($qa_id);
        $update_data = $qa;

        foreach ((array)($request_data) as $key => $value) {
            if ($value !== null && isset($qa[$key]) && $qa[$key] !== $value) {
                if ($key === 'qa_category') {
                    if ($value == '') {
                        continue;
                    }
                    if (!in_array($value, $this->qa_service->getCategory())) {
                        throw new HttpBadRequestException($request, '카테고리를 확인해주세요.');
                    }
                }
                $update_data[$key] = $value;
            }
        }

        $this->qa_service->updateQa($qa_id, $update_data);

        return api_response_json($response, ['message' => 'success']);
    }

    /**
     *
     * @OA\Delete(
     *     path="/api/v1/qa/{qa_id}",
     *     tags={"Q&A"},
     *     summary="Q&A 삭제",
     *     description="Q&A 삭제",
     *     security={{"Oauth2Password": {}}},
     *     @OA\Parameter(
     *       name="qa_id",
     *       in="path",
     *       description="Q&A ID"
     *     ),
     *     @OA\Response(
     *       response=200,
     *       description="Q&A 삭제 성공",
     *       @OA\JsonContent(ref="#/components/schemas/BaseResponse")
     *     ),
     *     @OA\Response(
     *       response=401,
     *       description="로그인이 필요합니다."
     *     ),
     *     @OA\Response(
     *       response=404,
     *       description="Q&A를 찾을 수 없습니다."
     *     )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function delete(Request $request, Response $response)
    {
        $member = $request->getAttribute('member');
        $config = ConfigService::getConfig();
        $qa_id = $request->getAttribute('qa_id');
        $qa = $this->qa_service->fetchQa($qa_id);

        if (!is_super_admin($config, $member['mb_id'])) {
            if ($qa['mb_id'] !== $member['mb_id']) {
                return api_response_json($response, ['message' => '게시글을 삭제할 권한이 없습니다.']);
            }
        }

        $this->qa_service->deleteQa($qa);
        $this->qa_service->deleteFiles($qa);
        $this->qa_service->deleteFileRecord((array)$qa);

        return api_response_json($response, ['message' => 'success']);
    }


    /**
     * @OA\Post(
     *     path="/api/v1/qa/{qa_id}/file",
     *     tags={"Q&A"},
     *     summary="Q&A 파일 업로드",
     *     description="Q&A 파일 업로드",
     *     security={{"Oauth2Password": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/QaFileUploadRequest")
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="qa_id",
     *         in="path",
     *         required=true,
     *         description="Q&A ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Q&A 파일 업로드 성공",
     *         @OA\JsonContent(ref="#/components/schemas/BaseResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="로그인이 필요합니다."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Q&A를 찾을 수 없습니다."
     *     )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function fileUpload(Request $request, Response $response, $args)
    {
        $member = $request->getAttribute('member');
        $request_body = $request->getParsedBody();
        $request_uploaded_files = $request->getUploadedFiles();

        $request_data = new QaFileUploadRequest(array_merge($request_body, $request_uploaded_files));
        $request_data->qa_id = $args['qa_id'];

        if (!$request_body) {
            return api_response_json($response, ['message' => '파일을 업로드 할 수 없습니다.']);
        }

        $config = ConfigService::getConfig();
        $qa = $this->qa_service->fetchQa($request_data->qa_id);
        if (!$qa) {
            throw new HttpBadRequestException($request, 'Q&A를 찾을 수 없습니다.');
        }

        if (!is_super_admin($config, $member['mb_id'])) {
            if ($qa['mb_id'] !== $member['mb_id']) {
                throw new HttpForbiddenException($request, '게시글을 수정할 권한이 없습니다.');
            }
        }

        $this->qa_service->fileUpload($request_uploaded_files);
        $this->qa_service->deleteFiles((array)$request_data);
        $this->qa_service->deleteFileRecord((array)$request_data);
        $this->qa_service->recordFileUpload((array)$request_data);

        return api_response_json($response, ['message' => 'success']);
    }

}