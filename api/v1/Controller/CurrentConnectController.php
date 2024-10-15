<?php

namespace API\v1\Controller;

use API\Service\ConfigService;
use API\Service\CurrentConnectService;
use API\v1\Model\Request\CurrentConnect\CurrentConnectCreateRequest;
use API\v1\Model\Response\CurrentConnect\CurrentConnectListResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CurrentConnectController
{

    private CurrentConnectService $current_connect_service;

    public function __construct(CurrentConnectService $current_connect_service)
    {
        $this->current_connect_service = $current_connect_service;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/members/current-connect",
     *     tags={"현재 접속자"},
     *     summary="현재 접속자 목록 조회",
     *     description="현재 접속자 목록 조회",
     *     @OA\Parameter(
     *       name="page",
     *       in="query",
     *       description="페이지 번호",
     *       required=false,
     *       @OA\Schema(type="integer")
     *    ),
     *    @OA\Parameter(
     *      name="show_only_member",
     *      in="query",
     *      description="회원만 보기 여부 Y/N",
     *      required=false,
     *      @OA\Schema(type="string")
     *    ),
     *    @OA\Response(response="200",
     *      description="현재 접속자 목록 조회 성공",
     *      @OA\JsonContent(ref="#/components/schemas/CurrentConnectListResponse")
     *    )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function index(Request $request, Response $response, array $args)
    {
        $config = ConfigService::getConfig();
        $page = $request->getQueryParams()['page'] ?? 1;
        $show_only_member = $request->getQueryParams()['show_only_member'] ?? false;
        $is_show_only_member = strtolower($show_only_member) === 'y';
        $current_connect = $this->current_connect_service->fetchCurrentConnect($is_show_only_member, $page);
        $total_records = $this->current_connect_service->fetchTotalCount($is_show_only_member);
        $per_pages = $config['cf_page_rows'] ?: 10;

        $response_data = new CurrentConnectListResponse([
            'logins' => $current_connect,
            'total_records' => $total_records,
            'total_pages' => ceil($total_records / $per_pages)
        ]);
        return api_response_json($response, $response_data);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/members/current-connect",
     *     tags={"현재 접속자"},
     *     summary="현재 접속자 생성",
     *     description="현재 접속자 생성",
     *     security={{"Oauth2Password": {}}},
     *     @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *         mediaType="application/json",
     *           @OA\Schema(ref="#/components/schemas/CurrentConnectCreateRequest"),
     *        )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="성공",
     *         @OA\JsonContent(ref="#/components/schemas/CurrentConnectListResponse")
     *     )
     * )
     *
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function create(Request $request, Response $response, array $args)
    {
        $member = $request->getAttribute('member');
        $config = ConfigService::getConfig();
        if (is_super_admin($config, $member['mb_id'])) {
            return api_response_json($response, ['message' => '최고관리자는 추가하지 않습니다.']);
        }

        $request_data = new CurrentConnectCreateRequest($request->getParsedBody());
        $this->current_connect_service->createConnectInfo($member['mb_id'], (array)$request_data);

        return api_response_json($response, ['message' => '현재 접속자 정보에 추가되었습니다.']);
    }


}