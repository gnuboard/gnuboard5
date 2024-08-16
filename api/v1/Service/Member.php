<?php

namespace API\Service\Member;

use API\Database\Db;
use Exception;

/**
 * 회원가입 처리
 * @param object $data 회원가입 데이터
 * @return int 회원번호
 * @throws Exception 회원가입 실패시 Exception 발생
 */
function createMember(object $data): int
{
    $config = getConfig();

    if (fetchMemberById($data->mb_id)) {
        throw new Exception("이미 사용중인 회원아이디 입니다.", 409);
    }
    if (existsMemberByNick($data->mb_nick, $data->mb_id)) {
        throw new Exception("이미 사용중인 닉네임 입니다.", 409);
    }
    if (existsMemberByEmail($data->mb_email, $data->mb_id)) {
        throw new Exception("이미 사용중인 이메일 입니다.", 409);
    }
    if ($config['cf_use_recommend'] && $data->mb_recommend) {
        if (!fetchMemberById($data->mb_recommend)) {
            throw new Exception("추천인이 존재하지 않습니다.", 404);
        }
    }

    return insertMember((array)$data);
}

/**
 * 회원정보 갱신 처리
 * @param string $mb_id 회원아이디
 * @param object $data 갱신 데이터
 * @return void
 * @throws Exception 갱신 실패시 Exception 발생
 */
function updateMemberProfile(string $mb_id, object $data): void
{
    // 닉네임 변경 허용이 안되면 mb_nick 프로퍼티가 없다.
    if (isset($data->mb_nick)) {
        if (existsMemberByNick($data->mb_nick, $mb_id)) {
            throw new Exception("이미 사용중인 닉네임 입니다.", 409);
        }
    }
    if (existsMemberByEmail($data->mb_email, $mb_id)) {
        throw new Exception("이미 사용중인 이메일 입니다.", 409);
    }

    updateMember($mb_id, (array)$data);
}

/**
 * 회원탈퇴
 * - 실제로 삭제하지 않고 탈퇴일자 및 회원메모를 업데이트한다.
 * @param array $member
 * @return void
 * @throws Exception
 */
function leaveMember(array $member)
{
    $update_data = [
        "mb_leave_date" => date("Ymd"),
        "mb_memo" => date('Ymd', G5_SERVER_TIME) . " 탈퇴함\n" . addslashes($member['mb_memo']),
        "mb_certify" => '',
        "mb_adult" => 0,
        "mb_dupinfo" => ''
    ];
    updateMember($member['mb_id'], $update_data);

    // Hook - 회원탈퇴
    run_event('member_leave', $member);

    //소셜로그인 해제
    if (function_exists('social_member_link_delete')) {
        social_member_link_delete($member['mb_id']);
    }
}

/**
 * 회원정보 조회 검증
 * @param array $member 회원정보
 * @param array $login_member 로그인 회원정보
 * @return void
 * @throws Exception 조회 실패시 Exception 발생
 */
function verifyMemberProfile(array $member, array $login_member): void
{
    if (!$member) {
        throw new Exception("회원정보가 존재하지 않습니다.", 404);
    }
    if ($login_member['mb_id'] != $member['mb_id']) {
        if (!$login_member['mb_open']) {
            throw new Exception("자신의 정보를 공개하지 않으면 다른분의 정보를 조회할 수 없습니다.", 403);
        }
        if (!$member['mb_open']) {
            throw new Exception("해당 회원은 정보공개를 하지 않았습니다.", 403);
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
function verifyEmailCertification($member, object $data): void
{
    if (!$member) {
        throw new Exception("회원정보가 존재하지 않습니다.", 404);
    }
    if (!check_password($data->password, $member['mb_password'])) {
        throw new Exception("비밀번호가 일치하지 않습니다.", 403);
    }
    if (substr($member["mb_email_certify"], 0, 1) != '0') {
        throw new Exception("이미 메일인증 하신 회원입니다.", 409);
    }
    if (existsMemberByEmail($data->email, $member['mb_id'])) {
        throw new Exception("이미 사용중인 이메일 입니다.", 409);
    }
}

/**
 * 비밀번호 변경메일 발송 검증
 * @param string $email 이메일
 * @return array
 * @throws Exception 검증 실패시 Exception 발생
 */
function verifyPasswordResetEmail(string $email): array
{
    $members = fetchAllMemberByEmail($email);

    $count = 0;
    if ($members !== false) {
        $count = count($members);
    }

    if ($count > 1) {
        throw new Exception("동일한 메일주소가 2개 이상 존재합니다. 관리자에게 문의하여 주십시오.", 409);
    }

    if ($count === 0) {
        throw new Exception("입력한 정보로 등록된 회원을 찾을 수 없습니다.", 404);
    }

    return $members[0];
}

/**
 * 이메일로 회원정보 목록 조회
 * @param string $mb_email 이메일
 * @return array|false
 */
function fetchAllMemberByEmail(string $mb_email)
{
    global $g5;
    $query = "SELECT * FROM {$g5['member_table']} WHERE mb_email = :mb_email";

    return Db::getInstance()->run($query, ["mb_email" => $mb_email])->fetchAll();
}

/**
 * 회원정보 조회
 * @param string $mb_id 회원아이디
 * @return array|false
 */
function fetchMemberById(string $mb_id)
{
    global $g5;
    static $cache = [];
    if (isset($cache[$mb_id])) {
        return $cache[$mb_id];
    }

    $query = "SELECT * FROM `{$g5['member_table']}` WHERE mb_id = :mb_id";
    $stmt = Db::getInstance()->run($query, ["mb_id" => $mb_id]);
    $cache[$mb_id] = $stmt->fetch();
    return $cache[$mb_id];
}

/**
 * 닉네임 중복여부 확인
 * @param string $mb_nick 닉네임
 * @param string $mb_id 회원아이디
 * @return bool
 */
function existsMemberByNick(string $mb_nick, string $mb_id): bool
{
    global $g5;
    $query = "SELECT COUNT(*) as cnt
                    FROM {$g5['member_table']}
                    WHERE mb_nick = :mb_nick
                    AND mb_id <> :mb_id";

    $stmt = Db::getInstance()->run($query, [
        "mb_nick" => $mb_nick,
        "mb_id" => $mb_id
    ]);

    return $stmt->fetchColumn() > 0;
}

/**
 * 이메일 중복여부 확인
 * @param string $mb_email 이메일
 * @param string $mb_id 회원아이디
 * @return bool
 */
function existsMemberByEmail(string $mb_email, string $mb_id): bool
{
    global $g5;

    $query = "SELECT count(*) as cnt
                    FROM `{$g5['member_table']}`
                    WHERE mb_email = :mb_email
                    AND mb_id <> :mb_id";

    $stmt = Db::getInstance()->run($query, [
        "mb_email" => $mb_email,
        "mb_id" => $mb_id
    ]);

    return $stmt->fetchColumn() > 0;
}

// ========================================
// Database Queries
// ========================================

/**
 * 회원가입 처리
 * @param array $data 회원가입 데이터
 * @return string|false 회원 테이블 mb_no 번호
 */
function insertMember(array $data): int
{
    global $g5;
    $insert_id = Db::getInstance()->insert($g5['member_table'], $data);
    return $insert_id;
}

/**
 * 회원정보 수정 처리
 * @param string $mb_id 회원아이디
 * @param array $data 수정할 데이터
 * @return int 수정된 행 갯수
 */
function updateMember(string $mb_id, array $data): int
{
    global $g5;
    return Db::getInstance()->update($g5['member_table'], ["mb_id" => $mb_id], $data);
}
