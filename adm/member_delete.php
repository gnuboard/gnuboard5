<?php
$sub_menu = "200100";
include_once("./_common.php");

check_demo();

auth_check($auth[$sub_menu], "d");

$mb = get_member($_POST['mb_id']);

if (!$mb['mb_id'])
    alert("회원자료가 존재하지 않습니다.");
else if ($member['mb_id'] == $mb['mb_id'])
    alert("로그인 중인 관리자는 삭제 할 수 없습니다.");
else if (is_admin($mb['mb_id']) == "super")
    alert("최고 관리자는 삭제할 수 없습니다.");
else if ($mb['mb_level'] >= $member['mb_level'])
    alert("자신보다 권한이 높거나 같은 회원은 삭제할 수 없습니다.");

check_admin_token();

// 회원자료 삭제
member_delete($mb['mb_id']);

if ($url)
    goto_url("{$url}?$qstr&amp;w=u&amp;mb_id=$mb_id");
else
    goto_url("./member_list.php?$qstr");
?>
