<?php

namespace API\v1\Controller;

use API\Exceptions\HttpBadRequestException;
use API\Exceptions\HttpNotFoundException;
use API\Exceptions\HttpConflictException;
use API\Exceptions\HttpForbiddenException;
use API\Service\MailService;
use API\Service\MemberImageService;
use API\Service\MemberService;
use API\Service\PointService;
use API\Service\Social\SocialService;
use API\v1\Model\Request\Member\ChangeCertificationEmailRequest;
use API\v1\Model\Request\Member\CreateMemberRequest;
use API\v1\Model\Request\Member\SearchPasswordResetMailReqeust;
use API\v1\Model\Request\Member\UpdateMemberRequest;
use API\v1\Model\Response\Member\CreateMemberResponse;
use API\v1\Model\Response\Member\GetMemberResponse;
use API\v1\Model\Response\Member\GetMemberMeResponse;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MemberController
{
    private MemberService $member_service;
    private MemberImageService $image_service;
    private PointService $point_service;
    private SocialService $social_service;
    private MailService $mail_service;

    public function __construct(
        MemberService $member_service,
        MemberImageService $image_service,
        PointService $point_service,
        SocialService $social_service,
        MailService $mail_service
    ) {
        $this->social_service = $social_service;
        $this->member_service = $member_service;
        $this->image_service = $image_service;
        $this->point_service = $point_service;
        $this->mail_service = $mail_service;
    }

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
     *     @OA\Response(response="404", ref="#/components/responses/404"),
     *     @OA\Response(response="409", ref="#/components/responses/409"),
     *     @OA\Response(response="422", ref="#/components/responses/422"),
     *     @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function create(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');
        $request_body = $request->getParsedBody();

        try {
            $data = new CreateMemberRequest($config, $request_body);

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

            $this->member_service->createMember($data);
        } catch (Exception $e) {
            if ($e->getCode() === 404) {
                throw new HttpNotFoundException($request, $e->getMessage());
            }

            if ($e->getCode() === 409) {
                throw new HttpConflictException($request, $e->getMessage());
            }

            throw $e;
        }

        // 회원가입 포인트 부여
        $register_point = $config['cf_register_point'] ?? 0;
        $this->point_service->addPoint($data->mb_id, $register_point, '회원가입 축하', '@member', $data->mb_id, '회원가입');

        // 추천인 포인트 부여
        if ($config['cf_use_recommend'] && $data->mb_recommend) {
            $recommand_point = $config['cf_recommend_point'] ?? 0;
            $this->point_service->addPoint($data->mb_recommend, $recommand_point, "{$data->mb_id}의 추천인", '@member', $data->mb_recommend, "{$data->mb_id} 추천");
        }

        // 인증메일 발송
        if ($config['cf_use_email_certify']) {
            $subject = "[{$config['cf_title']}] 인증확인 메일입니다.";

            // 어떠한 회원정보도 포함되지 않은 일회용 난수를 생성하여 인증에 사용
            $mb_md5 = md5(pack('V*', rand(), rand(), rand(), rand()));

            $this->member_service->updateMember($data->mb_id, ['mb_email_certify2' => $mb_md5]);

            $certify_href = G5_BBS_URL . "/email_certify.php?mb_id={$data->mb_id}&amp;mb_md5={$mb_md5}";
            $w = '';
            ob_start();
            include_once(__DIR__ . '../../../../bbs/register_form_update_mail3.php');
            $content = ob_get_clean();

            $content = run_replace('api_register_form_update_mail_certify_content', $content, $data->mb_id);

            $this->mail_service->send($config['cf_admin_email_name'], $config['cf_admin_email'], $data->mb_email, $subject, $content, 1);
            
            // 가입축하메일 발송
        } elseif ($config['cf_email_mb_member']) {
            $subject = "[{$config['cf_title']}] 회원가입을 축하드립니다.";

            ob_start();
            //$mb_name
            $mb_id = $data->mb_id;
            $mb_nick = $data->mb_nick;
            $mb_recommend = $data->mb_recommend ?? '';
            $mb_name = $data->mb_name;
            include_once __DIR__ . '../../../../bbs/register_form_update_mail1.php';
            $content = ob_get_clean();

            $content = run_replace('api_register_form_update_mail_mb_content', $content, $data->mb_id);

            $this->mail_service->send($config['cf_admin_email_name'], $config['cf_admin_email'], $data->mb_email, $subject, $content, 1);
        }

        // 최고관리자님께 메일 발송
        if ($config['cf_email_mb_super_admin']) {
            $subject = run_replace('api_register_form_update_mail_admin_subject', '[' . $config['cf_title'] . '] ' . $data->mb_nick . ' 님께서 회원으로 가입하셨습니다.', $data->mb_id,
                $data->mb_nick);

            ob_start();
            include_once(__DIR__ . '../../../../bbs/register_form_update_mail2.php');
            $content = ob_get_clean();

            $content = run_replace('api_register_form_update_mail_admin_content', $content, $data->mb_id);

            $this->mail_service->send($data->mb_nick, $data->mb_email, $config['cf_admin_email'], $subject, $content, 1);
        }

        $response_data = new CreateMemberResponse('회원가입이 완료되었습니다.', $data);
        return api_response_json($response, $response_data);
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
     *      @OA\Response(response="200", description="인증 이메일 갱신 성공", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="409", ref="#/components/responses/409"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function changeCertificationEmail(Request $request, Response $response, array $args): Response
    {
        $config = $request->getAttribute('config');
        $mb_id = $args['mb_id'];
        $request_body = $request->getParsedBody();

        try {
            $data = new ChangeCertificationEmailRequest($config, $request_body);
            $member = $this->member_service->fetchMemberById($mb_id);
            if (!$member) {
                throw new HttpBadRequestException($request, '회원정보가 존재하지 않습니다.');
            }

            $this->member_service->verifyEmailCertification($member, $data);

            // 인증메일 발송
            $subject = "[{$config['cf_title']}] 인증확인 메일입니다.";

            // 어떠한 회원정보도 포함되지 않은 일회용 난수를 생성하여 인증에 사용
            $mb_md5 = md5(pack('V*', rand(), rand(), rand(), rand()));

            $certify_href = G5_BBS_URL . '/email_certify.php?mb_id=' . $mb_id . '&amp;mb_md5=' . $mb_md5;
            $w = 'u';
            $mb_name = $member['mb_name'];
            ob_start();
            include_once(__DIR__ . '../../../../bbs/register_form_update_mail3.php');
            $content = ob_get_clean();

            $this->mail_service->send($config['cf_admin_email_name'], $config['cf_admin_email'], $data->email, $subject, $content, 1);

            $this->member_service->updateMember($mb_id, ['mb_email' => $data->email, 'mb_email_certify2' => $mb_md5]);

            return api_response_json($response, array('message' => "{$data->email} 주소로 인증메일이 재전송되었습니다."));
        } catch (Exception $e) {
            if ($e->getCode() === 403) {
                throw new HttpForbiddenException($request, $e->getMessage());
            }

            if ($e->getCode() === 404) {
                throw new HttpNotFoundException($request, $e->getMessage());
            }

            if ($e->getCode() === 409) {
                throw new HttpConflictException($request, $e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/members/me",
     *      summary="현재 로그인 회원정보 조회 (내 프로필)",
     *      tags={"회원"},
     *      security={{"Oauth2Password": {}}},
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
    public function getMe(Request $request, Response $response): Response
    {
        $member = $request->getAttribute('member');

        $member['mb_icon_path'] = $this->image_service->getMemberImagePath($member['mb_id'], 'icon');
        $member['mb_image_path'] = $this->image_service->getMemberImagePath($member['mb_id'], 'image');

        $response_data = new GetMemberMeResponse($member);

        return api_response_json($response, $response_data);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/members/{mb_id}",
     *      summary="회원정보 조회",
     *      tags={"회원"},
     *      security={{"Oauth2Password": {}}},
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
    public function getMember(Request $request, Response $response, array $args): Response
    {
        $mb_id = $args['mb_id'];
        $login_member = $request->getAttribute('member');
        $member = $this->member_service->fetchMemberById($mb_id);

        $this->member_service->verifyMemberProfile($member, $login_member);

        $member['mb_icon_path'] = $this->image_service->getMemberImagePath($mb_id, 'icon');
        $member['mb_image_path'] = $this->image_service->getMemberImagePath($mb_id, 'image');

        $response_data = new GetMemberResponse($member);

        return api_response_json($response, $response_data);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/member",
     *      summary="회원정보 수정",
     *      tags={"회원"},
     *      security={{"Oauth2Password": {}}},
     *      description="JWT 토큰을 통해 인증된 회원 정보를 수정합니다.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateMemberRequest"),
     *          )
     *      ),
     *      @OA\Response(response="200", description="회원정보 갱신 성공", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="409", ref="#/components/responses/409"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function update(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');
        $member = $request->getAttribute('member');
        $request_body = $request->getParsedBody();

        try {
            $data = new UpdateMemberRequest($config, $member, $request_body);

            $this->member_service->updateMemberProfile($member['mb_id'], $data);

            return api_response_json($response, array('message' => '회원정보가 수정되었습니다.'));
        } catch (Exception $e) {
            if ($e->getCode() === 409) {
                throw new HttpConflictException($request, $e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * 회원 아이콘/이미지 수정
     *
     * @OA\Post(
     *      path="/api/v1/member/images",
     *      summary="회원 아이콘&이미지 수정",
     *      tags={"회원"},
     *      security={{"Oauth2Password": {}}},
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
     *     @OA\Response(response="404", ref="#/components/responses/404"),
     *     @OA\Response(response="422", ref="#/components/responses/422"),
     *     @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function updateImages(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');
        $member = $request->getAttribute('member');

        $request_data = $request->getParsedBody();
        $uploaded_files = $request->getUploadedFiles();

        try {
            if ($request_data['del_mb_img']) {
                $this->image_service->deleteMemberImage($member['mb_id'], 'image');
            }
            if ($request_data['del_mb_icon']) {
                $this->image_service->deleteMemberImage($member['mb_id'], 'icon');
            }
            if (isset($uploaded_files['mb_img'])) {
                $this->image_service->updateMemberImage($config, $member['mb_id'], 'image', $uploaded_files['mb_img']);
            }
            if (isset($uploaded_files['mb_icon'])) {
                $this->image_service->updateMemberImage($config, $member['mb_id'], 'icon', $uploaded_files['mb_icon']);
            }

            return api_response_json($response, array('message' => '회원 아이콘/이미지가 수정되었습니다.'));
        } catch (Exception $e) {
            if ($e->getCode() === 400) {
                throw new HttpBadRequestException($request, $e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/member",
     *      summary="회원탈퇴",
     *      tags={"회원"},
     *      security={{"Oauth2Password": {}}},
     *      description="JWT 토큰을 통해 인증된 회원을 탈퇴합니다.
    - 실제로 데이터가 삭제되지 않고, 탈퇴 처리만 진행됩니다.
    ",
     *      @OA\Response(response="200", description="회원탈퇴 성공", @OA\JsonContent(ref="#/components/schemas/BaseResponse")),
     *      @OA\Response(response="401", ref="#/components/responses/401"),
     *      @OA\Response(response="403", ref="#/components/responses/403"),
     *      @OA\Response(response="404", ref="#/components/responses/404"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function leave(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');
        $member = $request->getAttribute('member');

        try {
            if (is_super_admin($config, $member['mb_id'])) {
                throw new HttpForbiddenException($request, '최고 관리자는 탈퇴할 수 없습니다.');
            }

            $this->social_service->leaveMember($member['mb_id']);
            $this->member_service->leaveMember($member);

            return api_response_json($response, array('message' => '회원탈퇴가 완료되었습니다.'));
        } catch (Exception $e) {
            throw $e;
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
     *      @OA\Response(response="409", ref="#/components/responses/409"),
     *      @OA\Response(response="422", ref="#/components/responses/422"),
     *      @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function searchPasswordResetMail(Request $request, Response $response): Response
    {
        $config = $request->getAttribute('config');

        try {
            $request_body = $request->getParsedBody();
            $request_data = new SearchPasswordResetMailReqeust($request_body);

            $member = $this->member_service->verifyPasswordResetEmail($request_data->mb_email);
            if (is_super_admin($config, $member['mb_id'])) {
                throw new HttpForbiddenException($request, '관리자 아이디는 접근 불가합니다.');
            }

            // TODO: 메일관련 공통 함수로 변경이 필요하다.
            // 임시비밀번호 발급
            $change_password = rand(100000, 999999);
            $mb_lost_certify = get_encrypt_string($change_password);
            $mb_nonce = md5(pack('V*', rand(), rand(), rand(), rand()));  // 일회용 난수 생성

            $this->member_service->updateMember($member['mb_id'], ['mb_lost_certify' => "{$mb_nonce} {$mb_lost_certify}"]);

            send_reset_password_mail($config, $member, $mb_nonce, $change_password);

            run_event('api_password_lost2_after', $member, $mb_nonce, $mb_lost_certify);

            return api_response_json($response, ['message' => '비밀번호를 변경할 수 있는 링크가 ' . $request_data->mb_email . ' 메일로 발송되었습니다.']);
        } catch (Exception $e) {
            if ($e->getCode() === 404) {
                throw new HttpNotFoundException($request, $e->getMessage());
            }

            if ($e->getCode() === 409) {
                throw new HttpConflictException($request, $e->getMessage());
            }

            throw $e;
        }
    }
}
