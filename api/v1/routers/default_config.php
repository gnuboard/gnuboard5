<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;


$app->group('/config', function (RouteCollectorProxy $group) {
    // HTML 설정 조회
    $group->get('/html', function (Request $request, Response $response, $args) {
        $config = $request->getAttribute('config');

        $select = array('cf_title', 'cf_add_meta', 'cf_add_script', 'cf_analytics');
        $html_config = generate_select_array($config, $select);

        return api_response_json($response, $html_config);
    });

    // 회원가입 약관 및 개인정보처리방침 조회
    $group->get('/policy', function (Request $request, Response $response) {
        $config = $request->getAttribute('config');

        $select = array('cf_stipulation', 'cf_privacy');
        $policy_config = generate_select_array($config, $select);

        return api_response_json($response, $policy_config);
    });

    // 회원가입 설정 조회
    $group->get('/member', function (Request $request, Response $response) {
        $config = $request->getAttribute('config');

        $select = array(
            'cf_use_email_certify', 'cf_use_homepage', 'cf_req_homepage', 'cf_use_tel', 'cf_req_tel',
            'cf_use_hp', 'cf_req_hp', 'cf_use_addr', 'cf_req_addr', 'cf_use_signature', 'cf_use_profile',
            'cf_icon_level', 'cf_member_img_width', 'cf_member_img_height', 'cf_member_img_size', 'cf_member_icon_width',
            'cf_member_icon_height', 'cf_member_icon_size', 'cf_open_modify', 'cf_use_recommend'
        );
        $policy_config = generate_select_array($config, $select);

        return api_response_json($response, $policy_config);
    });

    // 쪽지 발송 포인트 조회
    $group->get('/memo', function (Request $request, Response $response) {
        $config = $request->getAttribute('config');

        $select = array('cf_memo_send_point');
        $memo_config = generate_select_array($config, $select);

        return api_response_json($response, $memo_config);
    });

    // 게시판 설정 조회
    $group->get('/board', function (Request $request, Response $response) {
        $config = $request->getAttribute('config');

        $select = array(
            'cf_use_point', 'cf_point_term', 'cf_use_copy_log', 'cf_cut_name', 'cf_new_rows',
            'cf_read_point', 'cf_write_point', 'cf_comment_point', 'cf_download_point',
            'cf_write_pages', 'cf_mobile_pages', 'cf_link_target', 'cf_bbs_rewrite',
            'cf_delay_sec', 'cf_filter', 'cf_possible_ip', 'cf_intercept_ip'
        );
        $board_config = generate_select_array($config, $select);

        return api_response_json($response, $board_config);
    });
})->add($config_mw);
