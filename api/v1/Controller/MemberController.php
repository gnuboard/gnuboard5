<?php

namespace API\v1\Controller;

use API\Service\MemberService;
use API\v1\Model\Request\Member\ChangeCertificationEmailRequest;
use API\v1\Model\Request\Member\CreateMemberRequest;
use API\v1\Model\Request\Member\UpdateMemberRequest;
use API\v1\Model\Response\Member\CreateMemberResponse;
use API\v1\Model\Response\Member\GetMemberResponse;
use API\v1\Model\Response\Member\GetMemberMeResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Exception;

require_once __DIR__ . '../../../../lib/register.lib.php';
require_once __DIR__ . '../../../../lib/mailer.lib.php';


class MemberController
{
    /**
     * @OA\Post(
     *      path="/api/v1/members",
     *      summary="회원가입",
     *      tags={"회원"},
     *      description="회원 가입을 처리합니다.
#### 회원가입과 함께 처리되는 작업
- 회원가입 & 추천인 포인트 지급
- 회원가입 메일 발송 (메일발송 설정 시)
- 관리자에게 회원가입 메일 발송 (메일발송 설정 시)",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateMemberRequest"),
     *          )
     *      ),
     *     @OA\Response(response="201", description="회원가입 성공", @OA\JsonContent(ref="#/components/schemas/CreateMemberResponse")),
     *     @OA\Response(response="403", ref="#/components/responses/403"),
     *     @OA\Response(response="409", ref="#/components/responses/409"),
     *     @OA\Response(response="422", ref="#/components/responses/422"),
     *     @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function createMember(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $config = $request->getAttribute('config');
        $member_service = new MemberService($config);

        $request_body = $request->getParsedBody();
        $data = new CreateMemberRequest($request_body);

        // 아이디 유효성 검사
        if ($msg = empty_mb_id($data->mb_id)) {
            return api_response_json($response, array("message" => $msg), 422);
        }
        if ($msg = valid_mb_id($data->mb_id)) {
            return api_response_json($response, array("message" => $msg), 422);
        }
        if ($msg = count_mb_id($data->mb_id)) {
            return api_response_json($response, array("message" => $msg), 422);
        }
        if ($msg = reserve_mb_id($data->mb_id)) {
            return api_response_json($response, array("message" => $msg), 403);
        }
        if ($msg = exist_mb_id($data->mb_id)) {
            return api_response_json($response, array("message" => $msg), 409);
        }
        $data->mb_id = $mb_id = strtolower($data->mb_id);

        // 비밀번호 유효성 검사 및 암호화
        if ($data->mb_password != $data->mb_password_re) {
            return api_response_json($response, array("message" => "비밀번호가 일치하지 않습니다."), 422);
        }
        $data->mb_password = get_encrypt_string($data->mb_password);
        unset($data->mb_password_re);

        // 이름 유효성 검사
        $tmp_mb_name = iconv('UTF-8', 'UTF-8//IGNORE', $data->mb_name);
        if ($tmp_mb_name != $data->mb_name) {
            return api_response_json($response, array("message" => "이름을 올바르게 입력해 주십시오."), 422);
        }
        if ($msg = empty_mb_name($data->mb_name)) {
            return api_response_json($response, array("message" => $msg), 422);
        }

        // 닉네임 유효성 검사
        $tmp_mb_nick = iconv('UTF-8', 'UTF-8//IGNORE', $data->mb_nick);
        if ($tmp_mb_nick != $data->mb_nick) {
            return api_response_json($response, array("message" => "닉네임을 올바르게 입력해 주십시오."), 422);
        }
        if ($msg = empty_mb_nick($data->mb_nick)) {
            return api_response_json($response, array("message" => $msg), 422);
        }
        if ($msg = valid_mb_nick($data->mb_nick)) {
            return api_response_json($response, array("message" => $msg), 422);
        }
        if ($msg = reserve_mb_nick($data->mb_nick)) {
            return api_response_json($response, array("message" => $msg), 403);
        }
        if ($msg = exist_mb_nick($data->mb_nick, $mb_id)) {
            return api_response_json($response, array("message" => $msg), 409);
        }
        $data->mb_nick_date = G5_TIME_YMD;

        // 이메일 유효성 검사
        $mb_email = get_email_address($data->mb_email);
        if ($msg = valid_mb_email($mb_email)) {
            return api_response_json($response, array("message" => $msg), 422);
        }
        if ($msg = empty_mb_email($mb_email)) {
            return api_response_json($response, array("message" => $msg), 422);
        }
        if ($msg = prohibit_mb_email($mb_email)) {
            return api_response_json($response, array("message" => $msg), 403);
        }
        if ($msg = exist_mb_email($mb_email, $mb_id)) {
            return api_response_json($response, array("message" => $msg), 409);
        }
        $data->mb_email = $mb_email;

        // 추천인 유효성 검사
        if ($config['cf_use_recommend']) {
            $data->mb_recommend = $recommand = strtolower($data->mb_recommend);
            if (!exist_mb_id($recommand)) {
                return api_response_json($response, array("message" => "추천인이 존재하지 않습니다."), 404);
            }
            if ($mb_id == strtolower($recommand)) {
                return api_response_json($response, array("message" => "본인을 추천인으로 등록할 수 없습니다."), 403);
            }
        }

        // 휴대폰 번호 유효성 검사
        if ($config['cf_req_hp'] && ($config['cf_use_hp'] || $config['cf_cert_hp'] || $config['cf_cert_simple'])) {
            if ($msg = valid_mb_hp($data->mb_hp)) {
                return api_response_json($response, array("message" => $msg), 422);
            }
        }
        $data->mb_hp = hyphen_hp_number($data->mb_hp);

        // 본인확인 유효성 검사
        // TODO: Session 사용으로 인해 변경이 필요함 (임시 주석처리)
        if ($config['cf_cert_use']) {
            /*
            // 본인확인 필수
            if ($config['cf_cert_req']) {
                $post_cert_no = isset($_POST['cert_no']) ? trim($_POST['cert_no']) : '';
                if($post_cert_no !== get_session('ss_cert_no') || ! get_session('ss_cert_no'))
                    return api_response_json($response, array("message" => "회원가입을 위해서는 본인확인을 해주셔야 합니다."), 403);
            }
            // 중복체크
            if (get_session('ss_cert_type') && get_session('ss_cert_dupinfo')) {
                // 중복체크
                $sql = " select mb_id from {$g5['member_table']} where mb_id <> '{$data->mb_id}' and mb_dupinfo = '".get_session('ss_cert_dupinfo')."' ";
                $row = sql_fetch($sql);
                if (!empty($row['mb_id'])) {
                    return api_response_json($response, array("message" => "입력하신 본인확인 정보로 가입된 내역이 존재합니다."), 404);
                }
            }
            */
        }

        // 우편번호 분리
        $data->mb_zip1 = substr($data->mb_zip, 0, 3);
        $data->mb_zip2 = substr($data->mb_zip, 4, 3);
        unset($data->mb_zip);

        // 기타 기본 가입정보 설정
        $data->mb_ip = $_SERVER['REMOTE_ADDR'];
        $data->mb_level = $config['cf_register_level'] ?? 1;
        // 이메일 인증을 사용하지 않는다면 이메일 인증시간을 바로 넣는다
        if (!$config['cf_use_email_certify']) {
            $data->mb_email_certify = G5_TIME_YMDHIS;
        }

        // 회원가입 처리
        $member_service->insertMember($data);

        // 회원가입 포인트 부여
        $register_point = $config['cf_register_point'] ?? 0;
        insert_point($data->mb_id, $register_point, '회원가입 축하', '@member', $data->mb_id, '회원가입');

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

            $member_service->updateMember($mb_id, ["mb_email_certify2" => $mb_md5]);

            $certify_href = G5_BBS_URL . "/email_certify.php?mb_id={$mb_id}&amp;mb_md5={$mb_md5}";
            $w = "";
            ob_start();
            include_once(__DIR__ . '../../../../bbs/register_form_update_mail3.php');
            $content = ob_get_contents();
            ob_end_clean();

            $content = run_replace('register_form_update_mail_certify_content', $content, $mb_id);

            mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $mb_email, $subject, $content, 1);

            // 가입축하메일 발송
        } elseif ($config['cf_email_mb_member']) {
            $subject = "[{$config['cf_title']}] 회원가입을 축하드립니다.";

            ob_start();
            include_once __DIR__ . '../../../../bbs/register_form_update_mail1.php';
            $content = ob_get_contents();
            ob_end_clean();

            $content = run_replace('register_form_update_mail_mb_content', $content, $mb_id);

            mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $mb_email, $subject, $content, 1);
        }

        // 최고관리자님께 메일 발송
        if ($config['cf_email_mb_super_admin']) {
            $subject = run_replace('register_form_update_mail_admin_subject', '[' . $config['cf_title'] . '] ' . $mb_nick . ' 님께서 회원으로 가입하셨습니다.', $mb_id, $mb_nick);

            ob_start();
            include_once(__DIR__ . '../../../../bbs/register_form_update_mail2.php');
            $content = ob_get_contents();
            ob_end_clean();

            $content = run_replace('register_form_update_mail_admin_content', $content, $mb_id);

            mailer($mb_nick, $mb_email, $config['cf_admin_email'], $subject, $content, 1);
        }

        $result = new CreateMemberResponse("회원가입이 완료되었습니다.", $data);
        return api_response_json($response, $result->toArray());
    }

    /**
     * @OA\Put(
     *      path="/api/v1/members/{mb_id}/email-certification/change",
     *      summary="인증 이메일 변경",
     *      tags={"회원"},
     *      description="
메일인증을 처리하지 않은 회원의 메일을 변경하고 인증메일을 재전송합니다.
#### Request Body
- email: 변경할 이메일 주소
- password: 회원 비밀번호
",
     *      @OA\Parameter(name="mb_id", in="path", description="회원 아이디", required=true, @OA\Schema(type="string")),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/ChangeCertificationEmailRequest"),
     *          )
     *      ),
     *      @OA\Response(response="200", description="이메일 갱신 성공", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="409", ref="#/components/responses/409"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function changeCertificationEmail(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $config = $request->getAttribute('config');
        $member_service = new MemberService($config);

        $data = new ChangeCertificationEmailRequest($request->getParsedBody());

        if (empty($args['mb_id']) || empty($data->email) || empty($data->password)) {
            return api_response_json($response, array("message" => "올바른 방법으로 이용해 주십시오."), 422);
        }

        $mb_id = substr(clean_xss_tags($args['mb_id']), 0, 20);
        $mb_email = get_email_address(trim($data->email));

        $member = $member_service->fetchMemberById($mb_id);
        if (!check_password($data->password, $member['mb_password'])) {
            return api_response_json($response, array("message" => "비밀번호가 일치하지 않습니다."), 400);
        }

        if (substr($member["mb_email_certify"], 0, 1) != '0') {
            return api_response_json($response, array("message" => "이미 메일인증 하신 회원입니다."), 409);
        }

        // 이메일 검증
        if ($msg = valid_mb_email($mb_email)) {
            return api_response_json($response, array("message" => $msg), 422);
        }
        if ($msg = empty_mb_email($mb_email)) {
            return api_response_json($response, array("message" => $msg), 422);
        }
        if ($msg = prohibit_mb_email($mb_email)) {
            return api_response_json($response, array("message" => $msg), 403);
        }
        if ($msg = exist_mb_email($mb_email, $mb_id)) {
            return api_response_json($response, array("message" => $msg), 409);
        }

        // 인증메일 발송
        $subject = "[{$config['cf_title']}] 인증확인 메일입니다.";

        // 어떠한 회원정보도 포함되지 않은 일회용 난수를 생성하여 인증에 사용
        $mb_md5 = md5(pack('V*', rand(), rand(), rand(), rand()));

        $certify_href = G5_BBS_URL . '/email_certify.php?mb_id=' . $mb_id . '&amp;mb_md5=' . $mb_md5;
        $w = "u";
        $mb_name = $member['mb_name'];
        ob_start();
        include_once(__DIR__ . '../../../../bbs/register_form_update_mail3.php');
        $content = ob_get_contents();
        ob_end_clean();

        mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $mb_email, $subject, $content, 1);

        $member_service->updateMember($mb_id, ["mb_email" => $mb_email, "mb_email_certify2" => $mb_md5]);

        return api_response_json($response, array("message" => "{$mb_email}주소로 인증메일이 재전송되었습니다."));
    }

    /**
     * @OA\Get(
     *      path="/api/v1/members/me",
     *      summary="현재 로그인 회원정보 조회",
     *      tags={"회원"},
     *      description="
JWT 토큰을 통해 인증된 회원 정보를 조회합니다.
- 탈퇴 또는 차단된 회원은 조회할 수 없습니다.
- 이메일 인증이 완료되지 않은 회원은 조회할 수 없습니다.
            ",
     *      @OA\Response(response="200", description="로그인 회원정보 조회 성공", @OA\JsonContent(ref="#/components/schemas/GetMemberMeResponse")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function getMe(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $config = $request->getAttribute('config');
        $member = $request->getAttribute('member');
        $member_service = new MemberService($config);

        $member['mb_icon_path'] = $member_service->getMemberImagePath($member['mb_id'], 'icon');
        $member['mb_image_path'] = $member_service->getMemberImagePath($member['mb_id'], 'image');

        $member_response = new GetMemberMeResponse($member);

        return api_response_json($response, (array)$member_response);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/members/{mb_id}",
     *      summary="회원정보 조회",
     *      tags={"회원"},
     *      description="
회원 정보를 조회합니다.
- 자신&상대방의 정보가 공개 설정된 경우 조회 가능합니다.
            ",
     *      @OA\Parameter(name="mb_id", in="path", description="회원 아이디", required=true, @OA\Schema(type="string")),
     *      @OA\Response(response="200", description="회원정보 조회 성공", @OA\JsonContent(ref="#/components/schemas/GetMemberResponse")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function getMember(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $config = $request->getAttribute('config');
        $login_member = $request->getAttribute('member');
        $mb_id = $args['mb_id'];

        $member_service = new MemberService($config);
        $member = $member_service->fetchMemberById($mb_id);
        if (!$member) {
            return api_response_json($response, array("message" => "회원정보가 존재하지 않습니다."), 404);
        }
        // 회원정보 공개여부 체크
        if ($login_member['mb_id'] != $mb_id) {
            if (!$login_member['mb_open']) {
                return api_response_json($response, array("message" => "자신의 정보를 공개하지 않으면 다른분의 정보를 조회할 수 없습니다."), 403);
            }
            if (!$member['mb_open']) {
                return api_response_json($response, array("message" => "정보공개를 하지 않았습니다."), 403);
            }
        }

        $member['mb_icon_path'] = $member_service->getMemberImagePath($mb_id, 'icon');
        $member['mb_image_path'] = $member_service->getMemberImagePath($mb_id, 'image');

        $member_response = new GetMemberResponse($member);

        return api_response_json($response, (array)$member_response);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/member",
     *      summary="회원정보 수정",
     *      tags={"회원"},
     *      description="JWT 토큰을 통해 인증된 회원 정보를 수정합니다.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateMemberRequest"),
     *          )
     *      ),
     *      @OA\Response(response="200", description="회원정보 조회 성공", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="404", ref="#/components/responses/409"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function updateMember(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $config = $request->getAttribute('config');
        $member = $request->getAttribute('member');
        $member_service = new MemberService($config);

        $request_body = $request->getParsedBody();
        $data = new UpdateMemberRequest($request_body);

        // 닉네임 유효성 검사
        if (isset($data->mb_nick)) {
            $tmp_mb_nick = iconv('UTF-8', 'UTF-8//IGNORE', $data->mb_nick);
            if ($tmp_mb_nick != $data->mb_nick) {
                return api_response_json($response, array("message" => "닉네임을 올바르게 입력해 주십시오."), 422);
            }
            if ($msg = valid_mb_nick($data->mb_nick)) {
                return api_response_json($response, array("message" => $msg), 422);
            }
            if ($msg = reserve_mb_nick($data->mb_nick)) {
                return api_response_json($response, array("message" => $msg), 403);
            }
            if ($msg = exist_mb_nick($data->mb_nick, $member['mb_id'])) {
                return api_response_json($response, array("message" => $msg), 409);
            }

            // 닉네임 변경일수 체크
            if ($member['mb_nick_date'] < date("Y-m-d", G5_SERVER_TIME - ($config['cf_nick_modify'] * 86400))) {
                $data->mb_nick_date = G5_TIME_YMD;
            } else {
                unset($data->mb_nick);
                unset($data->mb_nick_date);
            }
        }

        // 이메일 유효성 검사
        $mb_email = get_email_address($data->mb_email);
        if ($msg = valid_mb_email($mb_email)) {
            return api_response_json($response, array("message" => $msg), 422);
        }
        if ($msg = empty_mb_email($mb_email)) {
            return api_response_json($response, array("message" => $msg), 422);
        }
        if ($msg = prohibit_mb_email($mb_email)) {
            return api_response_json($response, array("message" => $msg), 403);
        }
        if ($msg = exist_mb_email($mb_email, $member['mb_id'])) {
            return api_response_json($response, array("message" => $msg), 409);
        }
        $data->mb_email = $mb_email;

        // 비밀번호 확인 및 암호화
        if (isset($data->mb_password)) {
            if ($data->mb_password != $data->mb_password_re) {
                return api_response_json($response, array("message" => "비밀번호가 일치하지 않습니다."), 422);
            }
            $data->mb_password = get_encrypt_string($data->mb_password);
            unset($data->mb_password_re);
        }

        // 우편번호 분리
        $data->mb_zip1 = substr($data->mb_zip, 0, 3);
        $data->mb_zip2 = substr($data->mb_zip, 4, 3);
        unset($data->mb_zip);

        $member_service->updateMember($member['mb_id'], (array)$data);

        $result = array(
            "message" => "회원정보가 수정되었습니다.",
        );

        return api_response_json($response, $result);
    }

    /**
     * 회원 아이콘/이미지 수정
     * 
     * @OA\Post(
     *      path="/api/v1/member/images",
     *      summary="회원 아이콘&이미지 수정",
     *      tags={"회원"},
     *      description="JWT 토큰을 통해 인증된 회원의 아이콘 & 이미지를 수정합니다.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(property="mb_img", type="file", description="회원 이미지"),
     *                  @OA\Property(property="mb_icon", type="file", description="회원 아이콘"),
     *                  @OA\Property(property="del_mb_img", type="int", description="회원 이미지 삭제여부"),
     *                  @OA\Property(property="del_mb_icon", type="int", description="회원 아이콘 삭제여부"),
     *              )
     *          )
     *      ),
     *     @OA\Response(response="200", description="회원 아이콘/이미지 수정 성공", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *     @OA\Response(response="401", ref="#/components/responses/401"),
     *     @OA\Response(response="403", ref="#/components/responses/403"),
     *     @OA\Response(response="422", ref="#/components/responses/422"),
     *     @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function updateMemberImages(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $config = $request->getAttribute('config');
        $member = $request->getAttribute('member');
        $member_service = new MemberService($config);

        $data = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();

        try {
            if ($data['del_mb_img']) {
                $member_service->deleteMemberImage($member['mb_id'], 'image');
            }
            if ($data['del_mb_icon']) {
                $member_service->deleteMemberImage($member['mb_id'], 'icon');
            }
            $member_service->updateMemberImage($member['mb_id'], 'image', $uploadedFiles['mb_img']);
            $member_service->updateMemberImage($member['mb_id'], 'icon', $uploadedFiles['mb_icon']);

            return api_response_json($response, array("message" => "회원 아이콘/이미지가 수정되었습니다."));
        } catch (Exception $e) {
            return api_response_json($response, array("message" => $e->getMessage()), 400);
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/member",
     *      summary="회원탈퇴",
     *      tags={"회원"},
     *      description="JWT 토큰을 통해 인증된 회원을 탈퇴합니다.
- 실제로 데이터가 삭제되지 않고, 탈퇴 처리만 진행됩니다.
",
     *      @OA\Response(response="200", description="회원탈퇴 성공", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     * )
     */
    public function leaveMember(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $config = $request->getAttribute('config');
        $member = $request->getAttribute('member');
        $member_service = new MemberService($config);

        try {
            $member_service->leaveMember($member);
            return api_response_json($response, array("message" => "회원탈퇴가 완료되었습니다."));
        } catch (Exception $e) {
            return api_response_json($response, array("message" => $e->getMessage()), 403);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/members/search/password",
     *      summary="임시비밀번호 메일 발송",
     *      tags={"회원"},
     *      description="임시비밀번호로 재설정할 수 있는 링크를 메일로 발송합니다.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/SearchPasswordResetMailReqeust"),
     *          )
     *      ),
     *      @OA\Response(response="200", description="임시비밀번호 메일 발송 성공", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function searchPasswordResetMail(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // TODO: 비로그인 체크

        $config = $request->getAttribute('config');
        $member_service = new MemberService($config);

        $request_body = $request->getParsedBody();

        // 이메일 주소 유효성 검사
        $email = get_email_address(trim($request_body['mb_email']));
        if (!$email) {
            return api_response_json($response, array("message" => '메일주소 오류입니다.'), 422);
        }

        // 메일주소로 회원정보 조회 및 체크
        $members = $member_service->fetchAllMemberByEmail($email);

        switch (count($members)) {
            case 0:
                return api_response_json($response, ["message" => "존재하지 않는 회원입니다."], 404);
            case 1:
                $member = $members[0];
                break;
            default:
                return api_response_json($response, ["message" => "동일한 메일주소가 2개 이상 존재합니다. 관리자에게 문의하여 주십시오."], 409);
        }
        if (is_admin($member['mb_id'])) {
            return api_response_json($response, ["message" => '관리자 아이디는 접근 불가합니다.'], 403);
        }

        // 임시비밀번호 발급
        $change_password = rand(100000, 999999);
        $mb_lost_certify = get_encrypt_string($change_password);
        $mb_nonce = md5(pack('V*', rand(), rand(), rand(), rand()));  // 일회용 난수 생성

        $member_service->updateMember($member['mb_id'], ["mb_lost_certify" => "{$mb_nonce} {$mb_lost_certify}"]);

        // TODO: 메일발송 테스트 필요함.
        send_reset_password_mail($config, $member, $mb_nonce, $change_password);

        run_event('password_lost2_after', $member, $mb_nonce, $mb_lost_certify);

        return api_response_json($response, ["message" => '비밀번호를 변경할 수 있는 링크가 ' . $email . ' 메일로 발송되었습니다.']);
    }
}
