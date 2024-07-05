<?php

namespace API\v1\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class MemberController
{
    /**
     * @OA\Post(
     *      path="/api/v1/members",
     *      summary="회원가입",
     *      tags={"회원"},
     *      description="회원가입을 진행합니다.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="mb_id", type="string", description="아이디", example="test"),
     *                  @OA\Property(property="mb_password", type="string", description="비밀번호", example="test1234"),
     *                  @OA\Property(property="mb_password_re", type="string", description="비밀번호 확인", example="test1234"),
     *                  @OA\Property(property="mb_name", type="string", description="이름", example="홍길동"),
     *                  @OA\Property(property="mb_nick", type="string", description="닉네임", example="홍길동"),
     *                  @OA\Property(property="mb_email", type="string", description="이메일", example=""),
     *                  @OA\Property(property="mb_recommend", type="string", description="추천인", example=""),
     *                  @OA\Property(property="mb_hp", type="string", description="휴대폰 번호", example=""),
     *                  @OA\Property(property="mb_zip", type="string", description="우편번호", example="")
     *              )
     *          )
     *      ),
     *      @OA\Response(response="200", description="")
     * )
     */
    public function createMember(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        global $g5;

        $config = $request->getAttribute('config');
        $data = $request->getParsedBody();

        // 아이디 유효성 검사
        if ($msg = empty_mb_id($data['mb_id'])) {
            api_response_json($response, array("message" => $msg), 422);
        }
        if ($msg = valid_mb_id($data['mb_id'])) {
            api_response_json($response, array("message" => $msg), 422);
        }
        if ($msg = count_mb_id($data['mb_id'])) {
            api_response_json($response, array("message" => $msg), 422);
        }
        if ($msg = reserve_mb_id($data['mb_id'])) {
            api_response_json($response, array("message" => $msg), 403);
        }
        if ($msg = exist_mb_id($data['mb_id'])) {
            api_response_json($response, array("message" => $msg), 409);
        }
        $data['mb_id'] = $mb_id = strtolower($data['mb_id']);

        // 비밀번호 유효성 검사 및 암호화
        if ($data['mb_password'] != $data['mb_password_re']) {
            api_response_json($response, array("message" => "비밀번호가 일치하지 않습니다."), 422);
        }
        $data['mb_password'] = get_encrypt_string($data['mb_password']);
        unset($data['mb_password_re']);

        // 이름 유효성 검사
        $tmp_mb_name = iconv('UTF-8', 'UTF-8//IGNORE', $data['mb_name']);
        if ($tmp_mb_name != $data['mb_name']) {
            api_response_json($response, array("message" => "이름을 올바르게 입력해 주십시오."), 422);
        }
        if ($msg = empty_mb_name($data['mb_name'])) {
            api_response_json($response, array("message" => $msg), 422);
        }

        // 닉네임 유효성 검사
        $tmp_mb_nick = iconv('UTF-8', 'UTF-8//IGNORE', $data['mb_nick']);
        if ($tmp_mb_nick != $data['mb_nick']) {
            api_response_json($response, array("message" => "닉네임을 올바르게 입력해 주십시오."), 422);
        }
        if ($msg = empty_mb_nick($data['mb_nick'])) {
            api_response_json($response, array("message" => $msg), 422);
        }
        if ($msg = valid_mb_nick($data['mb_nick'])) {
            api_response_json($response, array("message" => $msg), 422);
        }
        if ($msg = reserve_mb_nick($data['mb_nick'])) {
            api_response_json($response, array("message" => $msg), 403);
        }
        if ($msg = exist_mb_nick($data['mb_nick'], $mb_id)) {
            api_response_json($response, array("message" => $msg), 409);
        }

        // 이메일 유효성 검사
        $mb_email = get_email_address($data['mb_email']);
        if ($msg = valid_mb_email($mb_email)) {
            api_response_json($response, array("message" => $msg), 422);
        }
        if ($msg = empty_mb_email($mb_email)) {
            api_response_json($response, array("message" => $msg), 422);
        }
        if ($msg = prohibit_mb_email($mb_email)) {
            api_response_json($response, array("message" => $msg), 403);
        }
        if ($msg = exist_mb_email($mb_email, $mb_id)) {
            api_response_json($response, array("message" => $msg), 409);
        }
        $data['mb_email'] = $mb_email;

        // 추천인 유효성 검사
        if ($config['cf_use_recommend']) {
            $data['mb_recommend'] = $recommand = strtolower($data['mb_recommend']);
            if (!exist_mb_id($recommand)) {
                api_response_json($response, array("message" => "추천인이 존재하지 않습니다."), 404);
            }
            if ($mb_id == strtolower($recommand)) {
                api_response_json($response, array("message" => "본인을 추천인으로 등록할 수 없습니다."), 403);
            }
        }

        // 휴대폰 번호 유효성 검사
        if ($config['cf_req_hp'] && ($config['cf_use_hp'] || $config['cf_cert_hp'] || $config['cf_cert_simple'])) {
            if ($msg = valid_mb_hp($data['mb_hp'])) {
                api_response_json($response, array("message" => $msg), 422);
            }
        }
        $data['mb_hp'] = hyphen_hp_number($data['mb_hp']);

        // 본인확인 유효성 검사
        // TODO: Session 사용으로 인해 변경이 필요함 (임시 주석처리)
        if ($config['cf_cert_use']) {
            /*
            // 본인확인 필수
            if ($config['cf_cert_req']) {
                $post_cert_no = isset($_POST['cert_no']) ? trim($_POST['cert_no']) : '';
                if($post_cert_no !== get_session('ss_cert_no') || ! get_session('ss_cert_no'))
                    api_response_json($response, array("message" => "회원가입을 위해서는 본인확인을 해주셔야 합니다."), 403);
            }
            // 중복체크
            if (get_session('ss_cert_type') && get_session('ss_cert_dupinfo')) {
                // 중복체크
                $sql = " select mb_id from {$g5['member_table']} where mb_id <> '{$data['mb_id']}' and mb_dupinfo = '".get_session('ss_cert_dupinfo')."' ";
                $row = sql_fetch($sql);
                if (!empty($row['mb_id'])) {
                    api_response_json($response, array("message" => "입력하신 본인확인 정보로 가입된 내역이 존재합니다."), 404);
                }
            }
            */
        }

        // 우편번호 분리
        $data['mb_zip1'] = substr($data['mb_zip'], 0, 3);
        $data['mb_zip2'] = substr($data['mb_zip'], 4, 3);
        unset($data['mb_zip']);

        // 기타 기본 가입정보 설정
        $data['mb_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['mb_level'] = $config['cf_register_level'] ?? 1;
        // 이메일 인증을 사용하지 않는다면 이메일 인증시간을 바로 넣는다
        if (!$config['cf_use_email_certify']) {
            $data['mb_email_certify'] = G5_TIME_YMDHIS;
        }

        // 회원가입 처리
        $query = "";
        foreach ($data as $key => $value) {
            $query .= "{$key} = '{$value}', ";
        }

        $sql = "INSERT INTO {$g5['member_table']} SET {$query} mb_datetime = NOW()";
        sql_query($sql);

        // 회원가입 포인트 부여
        $register_point = $config['cf_register_point'] ?? 0;
        insert_point($data['mb_id'], $register_point, '회원가입 축하', '@member', $data['mb_id'], '회원가입');

        // 추천인 포인트 부여
        if ($config['cf_use_recommend'] && $recommand) {
            $recommand_point = $config['cf_recommend_point'] ?? 0;
            insert_point($recommand, $recommand_point, "{$mb_id}의 추천인", '@member', $recommand, "{$mb_id} 추천");
        }

        // 인증메일 발송
        if ($config['cf_use_email_certify']) {
            $subject = "[{$config['cf_title']}] 인증확인 메일입니다.";

            // 어떠한 회원정보도 포함되지 않은 일회용 난수를 생성하여 인증에 사용
            $mb_md5 = md5(pack('V*', rand(), rand(), rand(), rand()));
            sql_query(" update {$g5['member_table']} set mb_email_certify2 = '$mb_md5' where mb_id = '$mb_id' ");

            $certify_href = G5_BBS_URL . "/email_certify.php?mb_id={$mb_id}&amp;mb_md5={$mb_md5}";

            ob_start();
            include_once('./register_form_update_mail3.php');
            $content = ob_get_contents();
            ob_end_clean();

            $content = run_replace('register_form_update_mail_certify_content', $content, $mb_id);

            mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $mb_email, $subject, $content, 1);

            // 가입축하메일 발송
        } elseif ($config['cf_email_mb_member']) {
            $subject = "[{$config['cf_title']}] 회원가입을 축하드립니다.";

            ob_start();
            include_once './register_form_update_mail1.php';
            $content = ob_get_contents();
            ob_end_clean();

            $content = run_replace('register_form_update_mail_mb_content', $content, $mb_id);

            mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $mb_email, $subject, $content, 1);
        }

        // 최고관리자님께 메일 발송
        if ($config['cf_email_mb_super_admin']) {
            $subject = run_replace('register_form_update_mail_admin_subject', '[' . $config['cf_title'] . '] ' . $mb_nick . ' 님께서 회원으로 가입하셨습니다.', $mb_id, $mb_nick);

            ob_start();
            include_once('./register_form_update_mail2.php');
            $content = ob_get_contents();
            ob_end_clean();

            $content = run_replace('register_form_update_mail_admin_content', $content, $mb_id);

            mailer($mb_nick, $mb_email, $config['cf_admin_email'], $subject, $content, 1);
        }

        $result = array(
            "message" => "회원가입이 완료되었습니다.",
            "mb_id" => $data['mb_id'],
            "mb_name" => $data['mb_name'],
            "mb_nick" => $data['mb_nick']
        );

        return api_response_json($response, $result);
    }


    // 회원정보 수정
    public function updateMember(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
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
    }

    // 회원 이미지 수정
    public function updateMemberImages(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $member = $request->getAttribute('member');
        $data = $request->getParsedBody();

        // 이미지 파일 업로드
        // 이미지 경로 저장

        return api_response_json($response, array("message" => "회원 이미지가 수정되었습니다."));
    }

    // 회원 인증 이메일 변경
    public function changeCertificationEmail(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        global $g5;

        $config = $request->getAttribute('config');
        $member = $request->getAttribute('member');
        $mb_id = $args['mb_id'];

        // 비밀번호 확인
        // 메일인증을 이미 진행한 회원인지 확인
        // 이미 가입된 이메일인지 확인
        // 사용이 금지된 이메일인지 확인

        // 이메일 및 인증코드 변경

        // 인증메일 재전송

        return api_response_json($response, array("message" => "인증메일이 재전송되었습니다."));
    }

    // 회원탈퇴
    public function deleteMember(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        global $g5;

        $config = $request->getAttribute('config');
        $member = $request->getAttribute('member');

        if ($config['cf_admin'] == $member['mb_id']) {
            api_response_json($response, array("message" => "최고 관리자는 탈퇴할 수 없습니다."), 403);
        }

        // 회원탈퇴일을 저장
        $date = date("Ymd");
        $sql = " update {$g5['member_table']} set mb_leave_date = '{$date}', mb_memo = '" . date('Ymd', G5_SERVER_TIME) . " 탈퇴함\n" . sql_real_escape_string($member['mb_memo']) . "', mb_certify = '', mb_adult = 0, mb_dupinfo = '' where mb_id = '{$member['mb_id']}' ";
        sql_query($sql);

        run_event('member_leave', $member);

        //소셜로그인 해제
        if (function_exists('social_member_link_delete')) {
            social_member_link_delete($member['mb_id']);
        }

        return api_response_json($response, array("message" => "회원탈퇴가 완료되었습니다."));
    }

    // 현재 로그인 회원정보 조회
    public function getMe(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $member = $request->getAttribute('member');
        $mb_id = $member['mb_id'];

        $select = array(
            'mb_id', 'mb_nick', 'mb_email', 'mb_point', 'mb_profile', 'mb_name', 'mb_memo_cnt', 'mb_scrap_cnt',
            'mb_1', 'mb_2', 'mb_3', 'mb_4', 'mb_5', 'mb_6', 'mb_7', 'mb_8', 'mb_9', 'mb_10'
        );
        $member_response = generate_select_array($member, $select);
        $mb_dir = substr($mb_id, 0, 2);
        $member_response['mb_icon_path'] = G5_DATA_PATH . '/member/' . $mb_dir . '/' . get_mb_icon_name($mb_id) . '.gif';
        $member_response['mb_image_path'] = G5_DATA_PATH . '/member_image/' . $mb_dir . '/' . get_mb_icon_name($mb_id) . '.gif';

        return api_response_json($response, $member_response);
    }

    // 회원정보 조회
    public function getMember(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        global $g5;

        $mb_id = $args['mb_id'];

        // 회원정보 공개여부 체크

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
    }
}
