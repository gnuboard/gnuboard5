<?php

namespace API\Service;

use API\Database\Db;
use Exception;


class MemberService
{
    private string $table;
    private MailService $mail_service;

    public function __construct(MailService $mail_service)
    {
        $this->mail_service = $mail_service;
        $this->table = $GLOBALS['g5']['member_table'];
    }

    /**
     * 회원가입
     * @param object $data 회원가입 데이터
     * @return false|string 회원번호
     * @throws Exception 회원가입 실패시 Exception 발생
     */
    public function createMember(object $data)
    {
        $config = ConfigService::getConfig();

        if ($this->fetchMemberById($data->mb_id)) {
            throw new Exception('이미 사용중인 회원아이디 입니다.', 409);
        }
        if ($this->existsMemberByNick($data->mb_nick, $data->mb_id)) {
            throw new Exception('이미 사용중인 닉네임 입니다.', 409);
        }
        if ($this->existsMemberByEmail($data->mb_email, $data->mb_id)) {
            throw new Exception('이미 사용중인 이메일 입니다.', 409);
        }
        if ($config['cf_use_recommend'] && $data->mb_recommend) {
            if (!$this->fetchMemberById($data->mb_recommend)) {
                throw new Exception('추천인이 존재하지 않습니다.', 404);
            }
        }

        return $this->insertMember((array)$data);
    }

    /**
     * 회원정보 갱신
     * @param string $mb_id 회원아이디
     * @param object $data 갱신 데이터
     * @return void
     * @throws Exception 갱신 실패시 Exception 발생
     */
    public function updateMemberProfile(string $mb_id, object $data): void
    {
        // 닉네임 변경 허용이 안되면 mb_nick 프로퍼티가 없다.
        if (isset($data->mb_nick)) {
            if ($this->existsMemberByNick($data->mb_nick, $mb_id)) {
                throw new Exception('이미 사용중인 닉네임 입니다.', 409);
            }
        }
        if ($this->existsMemberByEmail($data->mb_email, $mb_id)) {
            throw new Exception('이미 사용중인 이메일 입니다.', 409);
        }

        $this->updateMember($mb_id, (array)$data);
    }

    /**
     * 회원탈퇴
     * - 실제로 삭제하지 않고 탈퇴일자 및 회원메모를 업데이트한다.
     * @param array $member
     * @return void
     * @throws Exception
     */
    public function leaveMember(array $member)
    {
        $update_data = [
            'mb_leave_date' => date('Ymd'),
            'mb_memo' => date('Ymd', G5_SERVER_TIME) . " 탈퇴함\n" . addslashes($member['mb_memo']),
            'mb_certify' => '',
            'mb_adult' => 0,
            'mb_dupinfo' => ''
        ];
        $this->updateMember($member['mb_id'], $update_data);

        // Hook - 회원탈퇴
        run_event('api_member_leave_after', $member);
    }

    /**
     * 회원정보 조회 검증
     * @param array $member 회원정보
     * @param array $login_member 로그인 회원정보
     * @return void
     * @throws Exception 조회 실패시 Exception 발생
     */
    public function verifyMemberProfile(array $member, array $login_member): void
    {
        if (!$member) {
            throw new Exception('회원정보가 존재하지 않습니다.', 404);
        }
        if ($login_member['mb_id'] != $member['mb_id']) {
            if (!$login_member['mb_open']) {
                throw new Exception('자신의 정보를 공개하지 않으면 다른분의 정보를 조회할 수 없습니다.', 403);
            }
            if (!$member['mb_open']) {
                throw new Exception('해당 회원은 정보공개를 하지 않았습니다.', 403);
            }
        }
    }

    /**
     * 인증 이메일 변경정보 검증
     * @param array|bool $member 회원정보
     * @param object $data 변경정보
     * @return void
     * @throws Exception 변경정보 검증 실패시 Exception 발생
     */
    public function verifyEmailCertification($member, object $data): void
    {
        if (!$member) {
            throw new Exception('회원정보가 존재하지 않습니다.', 404);
        }
        if (!check_password($data->password, $member['mb_password'])) {
            throw new Exception('비밀번호가 일치하지 않습니다.', 403);
        }
        if (substr($member['mb_email_certify'], 0, 1) != '0') {
            throw new Exception('이미 메일인증 하신 회원입니다.', 409);
        }
        if ($this->existsMemberByEmail($data->email, $member['mb_id'])) {
            throw new Exception('이미 사용중인 이메일 입니다.', 409);
        }
    }

    /**
     * 비밀번호 변경메일 발송 검증
     * @param string $email 이메일
     * @return array
     * @throws Exception 검증 실패시 Exception 발생
     */
    public function verifyPasswordResetEmail(string $email): array
    {
        $members = $this->fetchAllMemberByEmail($email);
        $count = count($members);

        if ($count > 1) {
            throw new Exception('동일한 메일주소가 2개 이상 존재합니다. 관리자에게 문의하여 주십시오.', 409);
        }

        if ($count == 0) {
            throw new Exception('입력한 정보로 등록된 회원을 찾을 수 없습니다.', 404);
        }

        return $members[0];
    }

    /**
     * 인증 메일 발송
     * @param string $mb_id 받는 회원아이디
     * @param string $mb_name 받는 회원이름
     * @param string $email 받는 이메일
     * @param string $nonce 인증용 난수
     * @param bool $is_register 회원가입인지 재인증인지 여부
     * @return void
     */

    public function sendAuthMail($mb_id, $mb_name, $email, $nonce, $is_register = false)
    {
        $config = ConfigService::getConfig();
        // 인증메일 발송
        $subject = "[{$config['cf_title']}] 인증확인 메일입니다.";


        $certify_href = G5_BBS_URL . '/email_certify.php?mb_id=' . $mb_id . '&amp;mb_md5=' . $nonce;
        if ($is_register) {
            $w = '';
        } else {
            $w = 'u';
        }
        ob_start();

        //$mb_name, $w 사용 

        include_once G5_BBS_PATH . '/register_form_update_mail3.php';
        $content = ob_get_clean();

        $this->mail_service->send($config['cf_admin_email_name'], $config['cf_admin_email'], $email, $subject, $content, 1);
    }

    /**
     * 회원에게 가입메일 발송
     * @param array $member
     * @return void
     */
    public function sendRegisterMail(array $member)
    {
        $config = ConfigService::getConfig();
        $subject = "[{$config['cf_title']}] 회원가입을 축하드립니다.";

        ob_start();
        //$mb_name
        $mb_id = $member['mb_id'];
        $mb_nick = $member['mb_nick'];
        $mb_recommend = $member['mb_recommend'] ?? '';
        $mb_name = $member['mb_name'];
        include_once G5_BBS_PATH . '/register_form_update_mail1.php';
        $content = ob_get_clean();

        $content = run_replace('api_register_form_update_mail_mb_content', $content, $member['mb_id']);

        $this->mail_service->send($config['cf_admin_email_name'], $config['cf_admin_email'], $member['mb_email'], $subject, $content, 1);
    }

    /**
     * 최고관리자에게 회원이 가입했음을 알리는 메일 발송
     * @param array $member 가입한 회원
     * @return void
     */
    public function sendRegisterMailForSuperAdmin($member)
    {
        $config = ConfigService::getConfig();
        $subject = run_replace('api_register_form_update_mail_admin_subject', '[' . $config['cf_title'] . '] ' . $member['mb_nick'] . ' 님께서 회원으로 가입하셨습니다.', $member['mb_id'],
            $member['mb_nick']);

        ob_start();
        include_once(G5_BBS_PATH . '/register_form_update_mail2.php');
        $content = ob_get_clean();

        $content = run_replace('api_register_form_update_mail_admin_content', $content, $member['mb_id']);

        $this->mail_service->send($member['mb_nick'], $member['mb_email'], $config['cf_admin_email'], $subject, $content, 1);
    }

    /**
     * 임시비밀번호 메일 발송
     * @param array $member 회원정보
     * @param string $mb_nonce 인증용 난수
     * @param string $change_password 변경될 비밀번호
     * @return void
     */
    public function sendMailResetPassword(array $member, string $mb_nonce, string $change_password)
    {
        // 인증 링크 생성
        $href = G5_BBS_URL . '/password_lost_certify.php?mb_no=' . $member['mb_no'] . '&amp;mb_nonce=' . $mb_nonce;

        $config = ConfigService::getConfig();
        $subject = "[{$config['cf_title']}  요청하신 회원정보 찾기 안내 메일입니다.";

        $content = '<div style="margin:30px auto;width:600px;border:10px solid #f7f7f7">';
        $content .= '<div style="border:1px solid #dedede">';
        $content .= '<h1 style="padding:30px 30px 0;background:#f7f7f7;color:#555;font-size:1.4em">';
        $content .= '회원정보 찾기 안내';
        $content .= '</h1>';
        $content .= '<span style="display:block;padding:10px 30px 30px;background:#f7f7f7;text-align:right">';
        $content .= '<a href="' . G5_URL . '" target="_blank">' . $config['cf_title'] . '</a>';
        $content .= '</span>';
        $content .= '<p style="margin:20px 0 0;padding:30px 30px 30px;border-bottom:1px solid #eee;line-height:1.7em">';
        $content .= addslashes($member['mb_name']) . ' (' . addslashes($member['mb_nick']) . ')' . ' 회원님은 ' . G5_TIME_YMDHIS . ' 에 회원정보 찾기 요청을 하셨습니다.<br>';
        $content .= '저희 사이트는 관리자라도 회원님의 비밀번호를 알 수 없기 때문에, 비밀번호를 알려드리는 대신 새로운 비밀번호를 생성하여 안내 해드리고 있습니다.<br>';
        $content .= '아래에서 변경될 비밀번호를 확인하신 후, <span style="color:#ff3061"><strong>비밀번호 변경</strong> 링크를 클릭 하십시오.</span><br>';
        $content .= '비밀번호가 변경되었다는 인증 메세지가 출력되면, 홈페이지에서 회원아이디와 변경된 비밀번호를 입력하시고 로그인 하십시오.<br>';
        $content .= '로그인 후에는 정보수정 메뉴에서 새로운 비밀번호로 변경해 주십시오.';
        $content .= '</p>';
        $content .= '<p style="margin:0;padding:30px 30px 30px;border-bottom:1px solid #eee;line-height:1.7em">';
        $content .= '<span style="display:inline-block;width:100px">회원아이디</span> ' . $member['mb_id'] . '<br>';
        $content .= '<span style="display:inline-block;width:100px">변경될 비밀번호</span> <strong style="color:#ff3061">' . $change_password . '</strong>';
        $content .= '</p>';
        $content .= '<a href="' . $href . '" target="_blank" style="display:block;padding:30px 0;background:#484848;color:#fff;text-decoration:none;text-align:center">비밀번호 변경</a>';
        $content .= '</div>';
        $content .= '</div>';

        $content = run_replace('api_reset_mail_content', $content, $href, $member, $mb_nonce, $change_password);

        $this->mail_service->send($config['cf_admin_email_name'], $config['cf_admin_email'], $member['mb_email'], $subject, $content, 1);
    }

    // ========================================
    // Database Queries
    // ========================================

    /**
     * 이메일로 회원정보 목록 조회
     * @param string $mb_email 이메일
     * @return array|false
     */
    public function fetchAllMemberByEmail(string $mb_email)
    {
        $query = "SELECT * FROM {$this->table} WHERE mb_email = :mb_email";

        $stmt = Db::getInstance()->run($query, ['mb_email' => $mb_email]);

        return $stmt->fetchAll();
    }

    /**
     * 회원정보 조회
     * @param string $mb_id 회원아이디
     * @return array|false
     */
    public function fetchMemberById(string $mb_id)
    {
        static $cache = [];
        if (isset($cache[$mb_id])) {
            return $cache[$mb_id];
        }

        $query = "SELECT * FROM `{$this->table}` WHERE mb_id = :mb_id";
        $stmt = Db::getInstance()->run($query, ['mb_id' => $mb_id]);
        $cache[$mb_id] = $stmt->fetch();
        return $cache[$mb_id];
    }

    /**
     * 닉네임 중복여부 확인
     * @param string $mb_nick 닉네임
     * @param string $mb_id 회원아이디
     * @return bool
     */
    public function existsMemberByNick(string $mb_nick, string $mb_id): bool
    {
        $query = "SELECT COUNT(*) as cnt
                    FROM {$this->table}
                    WHERE mb_nick = :mb_nick
                    AND mb_id <> :mb_id";

        $stmt = Db::getInstance()->run($query, [
            'mb_nick' => $mb_nick,
            'mb_id' => $mb_id
        ]);

        return $stmt->fetchColumn() > 0;
    }

    /**
     * 회원아이디 중복여부 확인
     * @param string $mb_id
     * @return bool
     */
    public function existsMemberById(string $mb_id): bool
    {
        $query = "SELECT EXISTS(SELECT 1 FROM {$this->table} WHERE mb_id = :mb_id) as exist";
        $stmt = Db::getInstance()->run($query, ['mb_id' => $mb_id]);
        return $stmt->fetchColumn() == 1;
    }

    /**
     * 이메일 중복여부 확인
     * @param string $mb_email 이메일
     * @param string $mb_id 회원아이디
     * @return bool
     */
    public function existsMemberByEmail(string $mb_email, string $mb_id): bool
    {
        $query = "SELECT count(*) as cnt
                    FROM {$this->table}
                    WHERE mb_email = :mb_email
                    AND mb_id <> :mb_id";

        $stmt = Db::getInstance()->run($query, [
            'mb_email' => $mb_email,
            'mb_id' => $mb_id
        ]);

        return $stmt->fetchColumn() > 0;
    }

    /**
     *
     * 중복되는 회원아이디가 있을 경우 숫자를 붙여서 재귀적으로 쿼리한다.
     * @param $mb_id
     * @return string|callable
     * @throws Exception
     * @example  소셜가입등에서 소셜 연결 끊기이후 다시 연동시에 id 가 중복되므로 사용해야됩니다.
     */
    public function existsMemberIdRecursive($mb_id)
    {
        static $count = 0;

        $mb_id_add = ($count > 0) ? $mb_id . (string)$count : $mb_id;

        if (!$this->existsMemberById($mb_id_add)) {
            return $mb_id_add;
        }

        if ($count >= 400) {
            throw new \RuntimeException('다른 아이디로 가입해주세요.', 400);
        }

        $count++;
        return $this->existsMemberIdRecursive($mb_id);
    }


    /**
     *  중복되는 닉네임이 있을 경우 숫자를 붙여서 재귀적으로 쿼리한다.
     * @param $mb_nick
     * @return string|callable
     * @example  소셜가입등에서 소셜 연결 끊기이후 다시 연결시에 id 가 중복되므로 사용해야됩니다.
     */
    public function existsMemberNicknameRecursive($mb_nick)
    {
        static $count = 0;

        $mb_nick_add = ($count > 0) ? $mb_nick . (string)$count : $mb_nick;

        if (!$this->existsMemberByNick($mb_nick_add, ' ')) {
            return $mb_nick_add;
        }

        if ($count >= 200) {
            throw new \RuntimeException('닉네임으로 지정할 수 없습니다.', 400);
        }

        $count++;
        return $this->existsMemberNicknameRecursive($mb_nick);
    }

    /**
     * 회원가입 처리
     * @param array $data 회원가입 데이터
     * @return string|false 회원 테이블 mb_no 번호
     */
    public function insertMember(array $data)
    {
        $insert_id = Db::getInstance()->insert($this->table, $data);

        return $insert_id;
    }

    /**
     * 회원정보 수정 처리
     * @param string $mb_id 회원아이디
     * @param array $data 수정할 데이터
     * @return int 수정된 행 갯수
     */
    public function updateMember(string $mb_id, array $data): int
    {
        $update_count = Db::getInstance()->update($this->table, $data, ['mb_id' => $mb_id]);

        return $update_count;
    }
}
