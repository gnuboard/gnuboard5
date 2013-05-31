<?php
$sub_menu = "400490";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

check_token();

$mb_id = $_POST['mb_id'];
$ml_point = $_POST['ml_point'];
$ml_content = $_POST['ml_content'];

$mb = get_member($mb_id);

if (!$mb['mb_id'])
    alert('존재하는 회원아이디가 아닙니다.', './mileagelist.php?'.$qstr);

if (($ml_point < 0) && ($ml_point * (-1) > $mb['mb_mileage']))
    alert('포인트를 깎는 경우 현재 마일리지보다 작으면 안됩니다.', './mileagelist.php?'.$qstr);

insert_mileage($mb_id, $ml_point, $ml_content, '', '');

goto_url('./mileagelist.php?'.$qstr);
?>
