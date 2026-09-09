<?php
include_once('./_common.php');

if (!$member['mb_id'])
    alert('회원만 접근하실 수 있습니다.');

if ($is_admin == 'super')
    alert('최고 관리자는 탈퇴할 수 없습니다');

$post_mb_password = isset($_POST['mb_password']) ? trim($_POST['mb_password']) : '';

if (!($post_mb_password && check_password($post_mb_password, $member['mb_password'])))
    alert('비밀번호가 틀립니다.');

if (!g5_leave_member($member['mb_id']))
    alert('회원탈퇴를 처리하지 못했습니다. 회원 상태를 확인해 주십시오.');

// 3.09 수정 (로그아웃)
unset($_SESSION['ss_mb_id']);

if (!$url)
    $url = G5_URL;

alert(''.$member['mb_nick'].'님께서는 '. date("Y년 m월 d일") .'에 회원에서 탈퇴 하셨습니다.', $url);
