<?php

namespace API\v1\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class ConfigController
{
    /**
     * @OA\Get(
     *     path="/api/v1/config/html",
     *     summary="HTML 설정 조회",
     *     tags={"환경설정"},
     *     description="HTML을 구성하는데 필요한 설정 정보를 조회합니다.",
     *     @OA\Response(
     *          response="200",
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="cf_title", type="string", description="사이트 제목"),
     *              @OA\Property(property="cf_add_meta", type="string", description="추가 메타태그"),
     *              @OA\Property(property="cf_add_script", type="string", description="추가 스크립트"),
     *              @OA\Property(property="cf_analytics", type="string", description="분석코드"),
     *              @OA\Examples(
     *                  example="result",
     *                  value={
     *                      "cf_title": "string",
     *                      "cf_add_meta": "string",
     *                      "cf_add_script": "string",
     *                      "cf_analytics": "string"
     *                  }
     *              ),
     *          )
     *     ),
     *     @OA\Response(
     *          response="500",
     *          description="서버 오류",
     *     ),
     * )
     */
    public function getHtmlConfig(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $config = $request->getAttribute('config');

        $select = array('cf_title', 'cf_add_meta', 'cf_add_script', 'cf_analytics');
        $html_config = generate_select_array($config, $select);

        return api_response_json($response, $html_config);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/config/policy",
     *     summary="회원가입약관 조회",
     *     tags={"환경설정", "회원"},
     *     description="회원가입 약관을 조회합니다.<br> - 회원가입 약관 <br> - 개인정보 수집 및 허용 약관",
     *     @OA\Response(response="200", description="")
     * )
     */
    public function getPolicyConfig(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $config = $request->getAttribute('config');

        $select = array('cf_stipulation', 'cf_privacy');
        $policy_config = generate_select_array($config, $select);

        return api_response_json($response, $policy_config);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/config/member",
     *     summary="회원가입 설정 조회",
     *     tags={"환경설정", "회원"},
     *     description="회원가입에 필요한 환경설정 정보를 조회합니다.",
     *     @OA\Response(response="200", description="")
     * )
     */
    public function getMemberConfig(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $config = $request->getAttribute('config');

        $select = array(
            'cf_use_email_certify', 'cf_use_homepage', 'cf_req_homepage', 'cf_use_tel', 'cf_req_tel',
            'cf_use_hp', 'cf_req_hp', 'cf_use_addr', 'cf_req_addr', 'cf_use_signature', 'cf_use_profile',
            'cf_icon_level', 'cf_member_img_width', 'cf_member_img_height', 'cf_member_img_size', 'cf_member_icon_width',
            'cf_member_icon_height', 'cf_member_icon_size', 'cf_open_modify', 'cf_use_recommend'
        );
        $policy_config = generate_select_array($config, $select);

        return api_response_json($response, $policy_config);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/config/memo",
     *     summary="쪽지 발송 소진 포인트 조회",
     *     tags={"환경설정"},
     *     description="쪽지 발송 시, 1건당 소모되는 포인트 설정 정보를 조회합니다.",
     *     @OA\Response(response="200", description="")
     * )
     */
    public function getMemoConfig(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $config = $request->getAttribute('config');

        $select = array('cf_memo_send_point');
        $memo_config = generate_select_array($config, $select);

        return api_response_json($response, $memo_config);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/config/board",
     *     summary="게시판 설정 조회",
     *     tags={"환경설정"},
     *     description="게시판에 사용되는 설정 정보를 조회합니다.",
     *     @OA\Response(response="200", description="")
     * )
     */
    public function getBoardConfig(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $config = $request->getAttribute('config');

        $select = array(
            'cf_use_point', 'cf_point_term', 'cf_use_copy_log', 'cf_cut_name', 'cf_new_rows',
            'cf_read_point', 'cf_write_point', 'cf_comment_point', 'cf_download_point',
            'cf_write_pages', 'cf_mobile_pages', 'cf_link_target', 'cf_bbs_rewrite',
            'cf_delay_sec', 'cf_filter', 'cf_possible_ip', 'cf_intercept_ip'
        );
        $board_config = generate_select_array($config, $select);

        return api_response_json($response, $board_config);
    }
}