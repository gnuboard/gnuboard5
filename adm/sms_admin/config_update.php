<?php
$sub_menu = "900100";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

check_demo();

$g5['title'] = "SMS 기본설정";

$res = get_sock("http://www.icodekorea.com/res/userinfo.php?userid=$cf_icode_id&userpw=$cf_icode_pw");
$res = explode(';', $res);
$userinfo = array(
    'code'      => $res[0], // 결과코드
    'coin'      => $res[1], // 고객 잔액 (충전제만 해당)
    'gpay'      => $res[2], // 고객의 건수 별 차감액 표시 (충전제만 해당)
    'payment'   => $res[3]  // 요금제 표시, A:충전제, C:정액제
);

if ($userinfo['code'] == '202')
    alert('아이코드 아이디와 패스워드가 맞지 않습니다.');

if ($cf_member == 'on')
    $cf_member = 1;
else
    $cf_member = 0;

$res = sql_fetch("select * from ".$g5['sms5_config_table']." limit 1");

if (!$res)
    $sql = "insert into ";
else
    $sql = "update ";

$sql .= $g5['sms5_config_table']." set cf_phone='$cf_phone', cf_member='$cf_member', cf_level='$cf_level', cf_point='$cf_point', cf_day_count='$cf_day_count', cf_skin = '$cf_skin' ";

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