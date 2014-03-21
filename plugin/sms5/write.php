<?php
if (!defined('_GNUBOARD_')) exit;

if( !$sms5['bo_skin'] ){
    $sms5['bo_skin'] = "basic";
}

$err = null;

if (!$mb_id){
    $err = "받는회원 아이디가 넘어오지 않았습니다.";
    alert_close($err);
}
if (!$sms5['cf_member']){
    $err = "문자전송이 허용되지 않았습니다.\\n\\n사이트 관리자에게 문의하여 주십시오.";
    alert_close($err);
}
if (!$err and !$is_member){
    $err = "로그인 해주세요.";
    alert_close($err);
}
if (!$err and $member['mb_level'] < $sms5['cf_level']){
    $err = "회원 {$sms5['cf_level']} 레벨 이상만 문자전송이 가능합니다.";
    alert_close($err);
}
// 오늘 문자를 보낸 총 건수
$row = sql_fetch(" select count(*) as cnt from {$g5['sms5_member_history_table']} where mb_id='{$member['mb_id']}' and date_format(mh_datetime, '%Y-%m-%d') = '".G5_TIME_YMD."' ");
$total = $row['cnt'];

// 건수 제한
if (!$err and $sms5['cf_day_count'] > 0 && $is_admin != 'super') {
    if ($total >= $sms5['cf_day_count']) {
        $err = "하루에 보낼수 있는 문자갯수(".number_format($sms5['cf_day_count'])." 건)를 초과하였습니다.";
        alert_close($err);
    }
}

// 포인트 검사
if (!$err and $sms5['cf_point'] > 0 && $is_admin != 'super') {
    if ($sms5['cf_point'] > $member['mb_point']) {
        $err = "보유하신 포인트(".number_format($member['mb_point'])." 포인트)가 없거나 모자라서\\n\\n문자전송(".number_format($sms5['cf_point'])." 포인트)이 불가합니다.\\n\\n포인트를 적립하신 후 다시 시도 해 주십시오.";
        alert_close($err);
    }
}

// 특정회원에게 문자 전송
if ($mb_id) {
    $mb = get_member($mb_id);
    if (!$mb['mb_hp']) alert_close("회원 휴대폰번호가 없습니다.");
    if (!$mb['mb_open']) alert_close("정보를 공개하지 않았습니다.");
    if (!$mb['mb_sms']) alert_close("SMS 수신여부가 비활성화 되어 있습니다.");
    //$hp = $mb['mb_hp'];
}

$g5['title'] = "문자전송";

$token = get_token();

$emoticon_group = array();
$qry = sql_query("select * from {$g5['sms5_form_group_table']} where fg_member = 1 order by fg_name");
while ($res = sql_fetch_array($qry)) array_push($emoticon_group, $res);

$action_url = "./write_update.php";

if( G5_IS_MOBILE ){
    $write_skin_page = "/write_mobile.skin.php";
} else {
    $write_skin_page = "/write.skin.php";
}
include_once ($sms5_skin_path.$write_skin_page);
echo PHP_EOL.'<!-- skin : '.$sms5_skin_path.' -->'.PHP_EOL;
?>