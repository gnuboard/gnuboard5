<?php
$sub_menu = "900100";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

check_demo();

check_admin_token();

$g5['title'] = "SMS 기본설정";

// 회신번호 체크
if(!check_vaild_callback($cf_phone))
    alert('회신번호가 올바르지 않습니다.');

$userinfo = get_icode_userinfo($cf_icode_id, $cf_icode_pw);

if ($userinfo['code'] == '202')
    alert('아이코드 아이디와 패스워드가 맞지 않습니다.');

$res = sql_fetch("select * from ".$g5['sms5_config_table']." limit 1");

if (!$res)
    $sql = "insert into ";
else
    $sql = "update ";

$sql .= $g5['sms5_config_table']." set cf_phone='$cf_phone' ";

sql_query($sql);

// 아이코드 설정
$sql = " update {$g5['config_table']}
            set cf_sms_use              = '$cf_sms_use',
                cf_icode_id             = '$cf_icode_id',
                cf_icode_pw             = '$cf_icode_pw',
                cf_icode_server_ip      = '$cf_icode_server_ip',
                cf_icode_server_port    = '$cf_icode_server_port' ";
sql_query($sql);

goto_url("./config.php");
?>