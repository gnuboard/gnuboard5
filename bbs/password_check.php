<?php
include_once('./_common.php');

g5_require_board_access($board, true);
if ($w !== 's' && $w !== 'sc') alert('w 값이 제대로 넘어오지 않았습니다.');
$wr = get_write($write_table, $wr_id);
if (empty($wr['wr_id']) || !g5_is_secret_write($wr) ||
    ($w === 's' && !empty($wr['wr_is_comment'])) ||
    ($w === 'sc' && empty($wr['wr_is_comment']))) {
    alert('비밀글 인증 대상이 올바르지 않습니다.');
}
if ($w === 'sc') {
    g5_require_board_read($board, get_write($write_table, $wr['wr_parent']));
}

// 회원 비밀번호 대체 검증은 원본 게시물의 인증 서명을 변경하지 않는다.
$password_hash = $wr['wr_password'];
if (!$password_hash && $wr['mb_id']) {
    $mb = get_member($wr['mb_id']);
    $password_hash = $mb['mb_password'] ?? '';
}
if (!$password_hash || !check_password($wr_password, $password_hash)) {
    $password_write = $wr;
    $password_write['wr_password'] = $password_hash;
    run_event('password_is_wrong', 'bbs', $password_write, $qstr);
    alert('비밀번호가 틀립니다.');
}

g5_grant_secret_auth($bo_table, $wr);
$return_id = $w === 'sc' ? (int)$wr['wr_parent'] : (int)$wr['wr_id'];
goto_url(get_pretty_url($bo_table, $return_id));
