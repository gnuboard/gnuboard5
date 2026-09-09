<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 최고관리자일 때만 실행
if($config['cf_admin'] != $member['mb_id'] || $is_admin != 'super')
    return;

// 실행일 비교
if(isset($config['cf_optimize_date']) && $config['cf_optimize_date'] >= G5_TIME_YMD)
    return;

// 설정일이 지난 접속자로그 삭제
if($config['cf_visit_del'] > 0) {
    $tmp_before_date = date("Y-m-d", G5_SERVER_TIME - ($config['cf_visit_del'] * 86400));
    $sql = " delete from {$g5['visit_table']} where vi_date < '$tmp_before_date' ";
    sql_query($sql);
    if (defined('G5_USE_OPTIMIZE_DBTABLE') && G5_USE_OPTIMIZE_DBTABLE) {
        sql_query(" OPTIMIZE TABLE `{$g5['visit_table']}`, `{$g5['visit_sum_table']}` ");
    }
}

// 설정일이 지난 인기검색어 삭제
if($config['cf_popular_del'] > 0) {
    $tmp_before_date = date("Y-m-d", G5_SERVER_TIME - ($config['cf_popular_del'] * 86400));
    $sql = " delete from {$g5['popular_table']} where pp_date < '$tmp_before_date' ";
    sql_query($sql);
    if (defined('G5_USE_OPTIMIZE_DBTABLE') && G5_USE_OPTIMIZE_DBTABLE) {
        sql_query(" OPTIMIZE TABLE `{$g5['popular_table']}` ");
    }
}

// 설정일이 지난 최근게시물 삭제
if($config['cf_new_del'] > 0) {
    $sql = " delete from {$g5['board_new_table']} where (TO_DAYS('".G5_TIME_YMDHIS."') - TO_DAYS(bn_datetime)) > '{$config['cf_new_del']}' ";
    sql_query($sql);
    
    // 데이터가 많으면 처리시간 때문에 브라우저 응답이 늦을수 있음
    if (defined('G5_USE_OPTIMIZE_DBTABLE') && G5_USE_OPTIMIZE_DBTABLE) {
        sql_query(" OPTIMIZE TABLE `{$g5['board_new_table']}` ");
    }
}

// 설정일이 지난 쪽지 삭제
if($config['cf_memo_del'] > 0) {
    $sql = " delete from {$g5['memo_table']} where (TO_DAYS('".G5_TIME_YMDHIS."') - TO_DAYS(me_send_datetime)) > '{$config['cf_memo_del']}' ";
    sql_query($sql);
    if (defined('G5_USE_OPTIMIZE_DBTABLE') && G5_USE_OPTIMIZE_DBTABLE) {
        sql_query(" OPTIMIZE TABLE `{$g5['memo_table']}` ");
    }
}

// 메일 인증 기한이 지난 회원도 본인확인 정보를 해제하고 탈퇴 처리한다.
// 재발송된 토큰의 발급 시각을 사용하며 실제 변경 직전에 회원 상태를 다시 확인한다.
$email_certify_minutes = isset($config['cf_email_certify_minutes']) ? (int) $config['cf_email_certify_minutes'] : 60;
if ($config['cf_use_email_certify'] && $email_certify_minutes > 0) {
    $sql = " select mb_id, mb_email_certify, mb_email_certify2, mb_datetime, mb_leave_date, mb_memo
               from {$g5['member_table']}
              where mb_leave_date = ''
                and mb_email_certify not regexp '[1-9]'
                and mb_memo not regexp '^[0-9]{8}.*삭제함' ";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        if (is_expired_email_certify_member($row)) {
            g5_leave_member($row['mb_id'], true);
        }
    }
}

// 탈퇴회원 자동 삭제
if($config['cf_leave_day'] > 0) {
    $sql = " select mb_id from {$g5['member_table']}
                where (TO_DAYS('".G5_TIME_YMDHIS."') - TO_DAYS(mb_leave_date)) > '{$config['cf_leave_day']}'
                  and mb_memo not regexp '^[0-9]{8}.*삭제함' ";
    $result = sql_query($sql);
    while ($row=sql_fetch_array($result))
    {
        // 회원자료 삭제
        member_delete($row['mb_id']);
    }
}

// 음성 캡챠 파일 삭제
$captcha_mp3 = glob(G5_DATA_PATH.'/cache/kcaptcha-*.mp3');
if($captcha_mp3 && is_array($captcha_mp3)) {
    foreach ($captcha_mp3 as $file) {
        if (filemtime($file) + 86400 < G5_SERVER_TIME) {
            @unlink($file);
        }
    }
}

// 실행일 기록
if(isset($config['cf_optimize_date'])) {
    sql_query(" update {$g5['config_table']} set cf_optimize_date = '".G5_TIME_YMD."' ");
    
    run_event('cf_optimize_date_update', $config);
}

run_event('db_table_optimize_end', $config);
