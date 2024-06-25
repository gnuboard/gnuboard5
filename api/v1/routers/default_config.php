<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;


$app->group('/config', function (RouteCollectorProxy $group) {
    // HTML 설정 조회
    $group->get('/html', function (Request $request, Response $response, $args) {
        global $g5;

        $sql = "SELECT cf_title, cf_add_meta, cf_add_script, cf_analytics FROM {$g5['config_table']}";
        $config = sql_fetch($sql);
        $config_json = json_encode($config, JSON_UNESCAPED_UNICODE);

        $response->getBody()->write($config_json);
        return $response->withAddedHeader('Content-Type', 'application/json');
    });

    // 회원가입 약관 및 개인정보처리방침 조회
    $group->get('/policy', function (Request $request, Response $response) {
        global $g5;

        $sql = "SELECT cf_stipulation, cf_privacy FROM {$g5['config_table']}";
        $policy = sql_fetch($sql);
        $policy_json = json_encode($policy, JSON_UNESCAPED_UNICODE);

        $response->getBody()->write($policy_json);
        return $response->withAddedHeader('Content-Type', 'application/json');
    });


    // 회원가입 설정 조회
    $group->get('/member', function (Request $request, Response $response) {
        global $g5;

        $sql = "SELECT 
                    cf_use_email_certify, cf_use_homepage, cf_req_homepage, cf_use_tel, cf_req_tel,
                    cf_use_hp, cf_req_hp, cf_use_addr, cf_req_addr, cf_use_signature, cf_use_profile,
                    cf_icon_level, cf_member_img_width, cf_member_img_height, cf_member_img_size,
                    cf_member_icon_width, cf_member_icon_height, cf_member_icon_size, cf_open_modify,
                    cf_use_recommend
                FROM {$g5['config_table']}";
        $policy = sql_fetch($sql);
        $policy_json = json_encode($policy, JSON_UNESCAPED_UNICODE);

        $response->getBody()->write($policy_json);
        return $response->withAddedHeader('Content-Type', 'application/json');
    });

    // 환경설정 > 쪽지 발송 시, 설정 포인트 조회
    $group->get('/memo', function (Request $request, Response $response) {
        global $g5;

        $sql = "SELECT cf_memo_send_point FROM {$g5['config_table']}";
        $policy = sql_fetch($sql);
        $policy_json = json_encode($policy, JSON_UNESCAPED_UNICODE);

        $response->getBody()->write($policy_json);
        return $response->withAddedHeader('Content-Type', 'application/json');
    });


    // 환경설정 > 게시판 설정 조회
    $group->get('/board', function (Request $request, Response $response) {
        global $g5;

        $sql = "SELECT
                    cf_use_point, cf_point_term, cf_use_copy_log, cf_cut_name, cf_new_rows,
                    cf_read_point, cf_write_point, cf_comment_point, cf_download_point,
                    cf_write_pages, cf_mobile_pages, cf_link_target, cf_bbs_rewrite,
                    cf_delay_sec, cf_filter, cf_possible_ip, cf_intercept_ip
                FROM {$g5['config_table']}";
        $board_config = sql_fetch($sql);
        $board_config_json = json_encode($board_config, JSON_UNESCAPED_UNICODE);

        $response->getBody()->write($board_config_json);
        return $response->withAddedHeader('Content-Type', 'application/json');
    });

});
