<?php

namespace API\v1\Controller;

use API\v1\Model\Response\Config\BoardConfigResponse;
use API\v1\Model\Response\Config\HtmlConfigResponse;
use API\v1\Model\Response\Config\MemberConfigResponse;
use API\v1\Model\Response\Config\MemoConfigResponse;
use API\v1\Model\Response\Config\PolicyConfigResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ConfigController
{
    /**
     * @OA\Get(
     *     path="/api/v1/config/html",
     *     summary="HTML 설정 조회",
     *     tags={"환경설정"},
     *     description="HTML을 구성하는데 필요한 설정 정보를 조회합니다.",
     *     @OA\Response(response="200", description="기본환경설정 조회 성공", @OA\JsonContent(ref="#/components/schemas/HtmlConfigResponse")),
     *     @OA\Response(response="404", ref="#/components/responses/404"),
     *     @OA\Response(response="500", ref="#/components/responses/500")
     * )
     */
    public function getHtmlConfig(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');
        $data = new HtmlConfigResponse($config);

        return api_response_json($response, $data);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/config/policy",
     *     summary="회원가입 약관 조회",
     *     tags={"환경설정", "회원"},
     *     description="회원가입 약관을 조회합니다.",
     *     @OA\Response(response="200", description="약관 조회 성공", @OA\JsonContent(ref="#/components/schemas/PolicyConfigResponse")),
     *     @OA\Response(response="404", ref="#/components/responses/404"),
     *     @OA\Response(response="500", ref="#/components/responses/500")
     * )
     */
    public function getPolicyConfig(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');
        $data = new PolicyConfigResponse($config);

        return api_response_json($response, $data);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/config/member",
     *     summary="회원가입 설정 조회",
     *     tags={"환경설정", "회원"},
     *     description="회원가입에 필요한 기본환경설정 정보를 조회합니다.",
     *     @OA\Response(response="200", description="회원가입 설정 조회 성공", @OA\JsonContent(ref="#/components/schemas/MemberConfigResponse")),
     *     @OA\Response(response="404", ref="#/components/responses/404"),
     *     @OA\Response(response="500", ref="#/components/responses/500")
     * )
     */
    public function getMemberConfig(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');
        $data = new MemberConfigResponse($config);

        return api_response_json($response, $data);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/config/memo",
     *     summary="쪽지 발송 소진 포인트 조회",
     *     tags={"환경설정", "쪽지"},
     *     description="쪽지 발송 시, 1건당 소모되는 포인트 설정 정보를 조회합니다.",
     *     @OA\Response(response="200", description="소진 포인트 조회 성공", @OA\JsonContent(ref="#/components/schemas/MemoConfigResponse")),
     *     @OA\Response(response="404", ref="#/components/responses/404"),
     *     @OA\Response(response="500", ref="#/components/responses/500")
     * )
     */
    public function getMemoConfig(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');
        $data = new MemoConfigResponse($config);

        return api_response_json($response, $data);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/config/board",
     *     summary="게시판 설정 조회",
     *     tags={"환경설정", "게시판"},
     *     description="게시판에 사용되는 설정 정보를 조회합니다.",
     *     @OA\Response(response="200", description="게시판설정 조회 성공", @OA\JsonContent(ref="#/components/schemas/BoardConfigResponse")),
     *     @OA\Response(response="404", ref="#/components/responses/404"),
     *     @OA\Response(response="500", ref="#/components/responses/500")
     * )
     */
    public function getBoardConfig(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');
        $data = new BoardConfigResponse($config);

        return api_response_json($response, $data);
    }
}
