<?php
include_once('./_common.php');

// 봇의 메일 링크 크롤링을 방지합니다.
if(function_exists('check_mail_bot')){ check_mail_bot($_SERVER['REMOTE_ADDR']); }

$mb_id  = isset($_GET['mb_id']) ? trim($_GET['mb_id']) : '';
$mb_md5 = isset($_GET['mb_md5']) ? trim($_GET['mb_md5']) : '';
$esc_mb_id = sql_real_escape_string($mb_id);

$sql = " select mb_id, mb_datetime, mb_email_certify2, mb_leave_date, mb_intercept_date from {$g5['member_table']} where mb_id = '{$esc_mb_id}' ";
$row = sql_fetch($sql);
if (!$row['mb_id'])
    alert('존재하는 회원이 아닙니다.', G5_URL);

if ( $row['mb_leave_date'] || $row['mb_intercept_date'] ){
    alert('탈퇴 또는 차단된 회원입니다.', G5_URL);
}

if ($mb_md5)
{
    $valid_minutes = isset($config['cf_email_certify_minutes']) ? (int) $config['cf_email_certify_minutes'] : 60;

    if (is_valid_email_certify_token($mb_md5, $row['mb_email_certify2'], $row['mb_datetime'], $valid_minutes))
    {
        // 인증 링크는 한번만 처리되도록 인증과 동시에 토큰을 폐기한다.
        $esc_mb_md5 = sql_real_escape_string($mb_md5);
        sql_query(" update {$g5['member_table']} set mb_email_certify = '".G5_TIME_YMDHIS."', mb_email_certify2 = '' where mb_id = '{$esc_mb_id}' and mb_email_certify2 = '{$esc_mb_md5}' ");

        if (get_sql_affected_rows() <= 0) {
            alert('이미 처리되었거나 올바르지 않은 메일인증 요청입니다.', G5_URL);
        }

        alert("메일인증 처리를 완료 하였습니다.\\n\\n지금부터 {$mb_id} 아이디로 로그인 가능합니다.", G5_URL);
    }
    else
    {
        if ($mb_md5 === $row['mb_email_certify2']) {
            sql_query(" update {$g5['member_table']} set mb_email_certify2 = '' where mb_id = '{$esc_mb_id}' ");
            alert('메일인증 유효시간이 만료되었습니다. 인증메일을 다시 요청해 주십시오.', G5_URL);
        }

        alert('메일인증 요청 정보가 올바르지 않습니다.', G5_URL);
    }
}

alert('제대로 된 값이 넘어오지 않았습니다.', G5_URL);
