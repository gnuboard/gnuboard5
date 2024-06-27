<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;


$app->group('/members', function (RouteCollectorProxy $group) {
    /**
     * 회원가입 처리
     */
    $group->post('', function (Request $request, Response $response) {
        global $g5;

        $data = $request->getParsedBody();

        // 유효성 검사
        // 아이디
        // 이름
        // 닉네임
        // 이메일
        // 추천인

        // 비밀번호 확인 및 암호화
        if ($data['mb_password'] != $data['mb_password_re']) {
            $result = array(
                "message" => "비밀번호가 일치하지 않습니다."
            );
            $result_json = json_encode($result, JSON_UNESCAPED_UNICODE);

            $response->getBody()->write($result_json);
            return $response->withStatus(422)->withAddedHeader('Content-Type', 'application/json');
        }
        $data['mb_password'] = get_encrypt_string($data['mb_password']);
        unset($data['mb_password_re']);

        // 우편번호 분리
        $data['mb_zip1'] = substr($data['mb_zip'], 0, 3);
        $data['mb_zip2'] = substr($data['mb_zip'], 4, 3);
        unset($data['mb_zip']);

        // 기타 기본 가입정보 설정
        $data['mb_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['mb_level'] = 1;  // 설정에서 불러와야 함.

        $query = "";
        foreach ($data as $key => $value) {
            $query .= "{$key} = '{$value}', ";
        }

        $sql = "INSERT INTO {$g5['member_table']} SET {$query} mb_datetime = NOW()";
        sql_query($sql);

        $result = array(
            "message" => "회원가입이 완료되었습니다.",
            "mb_id" => $data['mb_id'],
            "mb_name" => $data['mb_name'],
            "mb_nick" => $data['mb_nick']
        );

        return api_response_json($response, $result);
    });

    /**
     * 회원정보 수정
     */
    $group->put('', function (Request $request, Response $response, array $args) {
        global $g5;

        $mb_id = 'test';  // TODO: JWT 토큰에서 추출해야함
        $data = $request->getParsedBody();

        // 유효성 검사
        // 아이디
        // 이름
        // 닉네임
        // 이메일
        // 추천인

        // 비밀번호 확인 및 암호화
        if ($data['mb_password'] != $data['mb_password_re']) {
            $result = array(
                "message" => "비밀번호가 일치하지 않습니다."
            );
            $result_json = json_encode($result, JSON_UNESCAPED_UNICODE);

            $response->getBody()->write($result_json);
            return $response->withStatus(422)->withAddedHeader('Content-Type', 'application/json');
        }
        $data['mb_password'] = get_encrypt_string($data['mb_password']);
        unset($data['mb_password_re']);

        // 우편번호 분리
        $data['mb_zip1'] = substr($data['mb_zip'], 0, 3);
        $data['mb_zip2'] = substr($data['mb_zip'], 4, 3);
        unset($data['mb_zip']);

        // 기타 기본 가입정보 설정
        $data['mb_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['mb_level'] = 1;  // 설정에서 불러와야 함.

        $query = "";
        foreach ($data as $key => $value) {
            $query .= "{$key} = '{$value}', ";
        }

        $sql = "UPDATE {$g5['member_table']} SET {$query} mb_datetime = NOW() WHERE mb_id = '{$mb_id}'";
        sql_query($sql);

        $result = array(
            "message" => "회원정보가 수정되었습니다.",
            "mb_id" => $data['mb_id'],
            "mb_name" => $data['mb_name'],
            "mb_nick" => $data['mb_nick']
        );

        return api_response_json($response, $result);
    });

    /**
     * 회원탈퇴
     * - 실제로 데이터가 삭제되지 않고, 탈퇴 처리만 진행됩니다.
     */
    $group->delete('', function (Request $request, Response $response, array $args) {
        global $g5;

        $mb_id = 'test';  // TODO: JWT 토큰에서 추출해야함

        // 비밀번호 확인
        // 회원탈퇴 처리

        return api_response_json($response, array("message" => "회원탈퇴가 완료되었습니다."));
    });

    /**
     * 회원 아이콘&이미지 수정
     */
    $group->put('/images', function (Request $request, Response $response) {
        global $g5;

        $mb_id = 'test';  // TODO: JWT 토큰에서 추출해야함

        $data = $request->getParsedBody();

        // 이미지 파일 업로드
        // 이미지 경로 저장

        return api_response_json($response, array("message" => "회원 이미지가 수정되었습니다."));
    });


    // 이메일 인증
    $group->group('/{mb_id}/email-certification', function (RouteCollectorProxy $group) {
        /**
         * 회원가입 메일 인증 처리
         * - '관리자 > 기본환경설정'에서 메일인증을 사용하지 않을 경우, 이 API는 사용되지 않습니다.
         */
        $group->put('', function (Request $request, Response $response, array $args) {
            global $g5;

            $mb_id = $args['mb_id'];
            $mb_md5 = $request->getQueryParams()['mb_md5'] ?? null;

            # 회원유효성 검사 (회원, 탈퇴여부, 차단여부,)
            # 인증코드 유효성 검사 (mb_eamil_certify2 == mb_md5)

            return api_response_json($response, array("message" => "메일인증 처리를 완료 하였습니다."));
        });

        /**
         * 인증 이메일 변경
         * - 메일인증을 처리하지 않은 회원의 메일을 변경하고 인증메일을 재전송합니다.
         */
        $group->put('/change', function (Request $request, Response $response, array $args) {
            global $g5;

            $mb_id = $args['mb_id'];

            # 비밀번호 확인
            # 메일인증을 이미 진행한 회원인지 확인
            # 이미 가입된 이메일인지 확인
            # 사용이 금지된 이메일인지 확인

            # 이메일 및 인증코드 변경

            # 인증메일 재전송

            return api_response_json($response, array("message" => "인증메일이 재전송되었습니다."));
        });
    });

    /**
     * 현재 로그인 회원정보 조회
     */
    $group->get('/me', function (Request $request, Response $response) {
        global $g5;

        $mb_id = 'admin';  // TODO: 로그인한 회원 아이디로 변경
        $sql = "SELECT * FROM {$g5['member_table']} WHERE mb_id = '{$mb_id}'";
        $member = sql_fetch($sql);

        $select = array(
            'mb_id', 'mb_nick', 'mb_email', 'mb_point', 'mb_profile', 'mb_name', 'mb_memo_cnt', 'mb_scrap_cnt',
            'mb_1', 'mb_2', 'mb_3', 'mb_4', 'mb_5', 'mb_6', 'mb_7', 'mb_8', 'mb_9', 'mb_10'
        );
        $member_response = generate_select_array($member, $select);
        $mb_dir = substr($mb_id, 0, 2);
        $member_response['mb_icon_path'] = G5_DATA_PATH . '/member/' . $mb_dir . '/' . get_mb_icon_name($mb_id) . '.gif';
        $member_response['mb_image_path'] = G5_DATA_PATH . '/member_image/' . $mb_dir . '/' . get_mb_icon_name($mb_id) . '.gif';

        return api_response_json($response, $member_response);
    });

    /**
     * 회원정보 조회
     */
    $group->get('/{mb_id}', function (Request $request, Response $response, array $args) {
        global $g5;

        $mb_id = $args['mb_id'];

        $sql = "SELECT * FROM {$g5['member_table']} WHERE mb_id = '{$mb_id}'";
        $member = sql_fetch($sql);

        $select = array(
            'mb_id', 'mb_nick', 'mb_email', 'mb_point', 'mb_profile',
            'mb_1', 'mb_2', 'mb_3', 'mb_4', 'mb_5', 'mb_6', 'mb_7', 'mb_8', 'mb_9', 'mb_10'
        );
        $member_response = generate_select_array($member, $select);
        $mb_dir = substr($mb_id, 0, 2);
        $member_response['mb_icon_path'] = G5_DATA_PATH . '/member/' . $mb_dir . '/' . get_mb_icon_name($mb_id) . '.gif';
        $member_response['mb_image_path'] = G5_DATA_PATH . '/member_image/' . $mb_dir . '/' . get_mb_icon_name($mb_id) . '.gif';

        return api_response_json($response, $member_response);
    });

});