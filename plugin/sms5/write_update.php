<?php
include_once("./_common.php");

$g5['title'] = "문자전송중";

if (!($token && get_session("ss_token") == $token))
    die("올바른 방법으로 사용해 주십시오.");

if (!$sms5['cf_member'])
    die("문자전송이 허용되지 않았습니다. 사이트 관리자에게 문의하여 주십시오.");

if (!$is_member)
    die("로그인 해주세요.");

if ($member['mb_level'] < $sms5['cf_level'])
    alert("회원 {$sms5['cf_level']}레벨 이상만 문자전송이 가능합니다.");

if (!trim($mh_reply))
    alert('보내는 번호를 입력해주세요.');

if (!trim($mh_message))
    alert('메세지를 입력해주세요.');

if ($is_admin != 'super')
{
    $mh_reply = get_hp($mh_reply, 0);
    if (!$mh_reply)
        alert("보내는 번호가 올바르지 않습니다.");
}
else
{
    $mh_reply = str_replace("-", "", $mh_reply);;
    if (!check_string($mh_reply, G5_NUMERIC))
        alert("보내는 번호가 올바르지 않습니다.");
}

$mh_hp = explode(',', $mh_hp);

if ($mb_id) {
    $mb = get_member($mb_id);
    if (!$mb['mb_sms'] || !$mb['mb_open']) {
        alert("정보를 공개하지 않았습니다.");
    }
    if( $mb['mb_hp'] ){
        array_push( $mh_hp, $mb['mb_hp'] );
    }
}

if (!count($mh_hp))
    alert('받는 번호를 입력해주세요.');

// 핸드폰 번호만 걸러낸다.
$tmp = array();
for ($i=0; $i<count($mh_hp); $i++)
{
    $hp = trim($mh_hp[$i]);
    $hp = get_hp($hp);

    if ($hp)
        $tmp[]['bk_hp'] = get_hp($hp, 0);
}
$mh_hp = $tmp;

$total = count($mh_hp);

// 건수 제한
if ($sms5['cf_day_count'] > 0 && $is_admin != 'super') {
    $row = sql_fetch(" select count(*) as cnt from {$g5['sms5_member_history_table']} where mb_id='{$member['mb_id']}' and date_format(mh_datetime, '%Y-%m-%d') = '".G5_TIME_YMD."' ");
    if ($row['cnt'] + $total > $sms5['cf_day_count']) {
        alert("하루에 보낼수 있는 문자갯수(".number_format($sms5['cf_day_count']).")를 초과하였습니다.");
    }
}

// 포인트 검사
if ($sms5['cf_point'] > 0 && $is_admin != 'super') {
    $minus_point = $sms5['cf_point'] * $total;
    if ($minus_point > $member['mb_point'])
        alert("보유하신 포인트(".number_format($member['mb_point']).")가 없거나 모자라서 문자전송(".number_format($minus_point).")이 불가합니다.\\n\\n포인트를 적립하신 후 다시 시도 해 주십시오.");
} else
    $minus_point = 0;

// 예약전송
if ($mh_by && $mh_bm && $mh_bd && $mh_bh && $mh_bi) {
    $mh_booking = "$mh_by-$mh_bm-$mh_bd $mh_bh:$mh_bi:00";
    $booking = $mh_by.$mh_bm.$mh_bd.$mh_bh.$mh_bi;
} else {
    $mh_booking = '';
    $booking = '';
}

$SMS = new SMS5;
$SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);

$result = $SMS->Add($mh_hp, $mh_reply, '', '', $mh_message, $booking, $total);

$is_success = null;

if ($result)
{
    $result = $SMS->Send();

    if ($result) //SMS 서버에 접속했습니다.
    {
        foreach ($SMS->Result as $result)
        {
            list($hp, $code) = explode(":", $result);

            if (substr($code,0,5) == "Error")
            {
                $is_success = false;

                switch (substr($code,6,2)) {
                    case '02':	 // "02:형식오류"
                        $mh_log = "형식이 잘못되어 전송이 실패하였습니다.";
                        break;
                    case '23':	 // "23:인증실패,데이터오류,전송날짜오류"
                        $mh_log = "데이터를 다시 확인해 주시기바랍니다.";
                        break;
                    case '97':	 // "97:잔여코인부족"
                        $mh_log = "잔여코인이 부족합니다.";
                        break;
                    case '98':	 // "98:사용기간만료"
                        $mh_log = "사용기간이 만료되었습니다.";
                        break;
                    case '99':	 // "99:인증실패"
                        $mh_log = "인증 받지 못하였습니다. 계정을 다시 확인해 주세요.";
                        break;
                    default:	 // "미 확인 오류"
                        $mh_log = "알 수 없는 오류로 전송이 실패하었습니다.";
                        break;
                }
            }
            else
            {
                $is_success = true;
                $mh_log = "문자전송:".get_hp($hp, 1);
            }

            $hp = get_hp($hp, 1);
            $log = array_shift($SMS->Log);
            sql_query("insert into {$g5['sms5_member_history_table']} set mb_id='{$member['mb_id']}', mh_reply='$mh_reply', mh_hp='$hp', mh_datetime='".G5_TIME_YMDHIS."', mh_booking='$mh_booking', mh_log='$mh_log', mh_ip='".$_SERVER['REMOTE_ADDR']."'");

            if ($is_admin == 'super')
                $sms5['cf_point'] = 0;

            if ($is_success)
                insert_point($member['mb_id'], (-1) * $sms5['cf_point'], "$mh_log");

        }
        $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
    }
    else alert("에러: SMS 서버와 통신이 불안정합니다.");
}
else alert("에러: SMS 데이터 입력도중 에러가 발생하였습니다.");

alert_close("$total 건의 문자메세지 전송을 완료하였습니다.");
?>