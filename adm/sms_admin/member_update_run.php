<?php
$sub_menu = "900200";
include_once("./_common.php");
@include_once(G5_PLUGIN_PATH."/sms5/JSON.php");

if(empty($config['cf_sms_use'])){
    if( $mtype == "json" ){
        die("{\"error\":\"환경 설정의 SMS 사용에서 아이코드를 사용설정해 주셔야 실행할수 있습니다.\"}");
    } else {
        die("환경 설정의 SMS 사용에서 아이코드를 사용설정해 주셔야 실행할수 있습니다.");
    }
}

if( !function_exists('json_encode') ) {
    function json_encode($data) {
        $json = new Services_JSON();
        return( $json->encode($data) );
    }
}

if( $mtype == "json" ){
    ajax_auth_check($auth[$sub_menu], "w");
} else {
    auth_check($auth[$sub_menu], "w");
}

$count      = 0;
$hp_yes     = 0;
$hp_no      = 0;
$hp_empty   = 0;
$leave      = 0;
$receipt    = 0;

// 회원 데이터 마이그레이션
$qry = sql_query("select mb_id, mb_name, mb_hp, mb_sms, mb_leave_date from ".$g5['member_table']." order by mb_datetime");
while ($res = sql_fetch_array($qry))
{
    if ($res['mb_leave_date'] != '')
        $leave++;
    else if ($res['mb_hp'] == '')
        $hp_empty++;
    else if (is_hp($res['mb_hp']))
        $hp_yes++ ;
    else
        $hp_no++;

    $hp = get_hp($res['mb_hp']);

    if ($hp == '') $bk_receipt = 0; else $bk_receipt = $res['mb_sms'];

    $field = "mb_id='{$res['mb_id']}', bk_name='".addslashes($res['mb_name'])."', bk_hp='{$hp}', bk_receipt='{$bk_receipt}', bk_datetime='".G5_TIME_YMDHIS."'";

    $res2 = sql_fetch("select * from {$g5['sms5_book_table']} where mb_id='{$res['mb_id']}'");
    if ($res2) // 기존에 등록되어 있을 경우 업데이트
    {
        $res3 = sql_fetch("select count(*) as cnt from {$g5['sms5_book_table']} where mb_id='{$res2['mb_id']}'");
        $mb_count = $res3['cnt'];

        // 회원이 삭제되었다면 휴대폰번호 DB 에서도 삭제한다.
        if ($res['mb_leave_date'])
        {
            sql_query("delete from {$g5['sms5_book_table']} where mb_id='{$res2['mb_id']}'");

            $sql = "update {$g5['sms5_book_group_table']} set bg_count = bg_count - $mb_count, bg_member = bg_member - $mb_count";
            if ($res2['bk_receipt'] == 1)
                $sql .= ", bg_receipt = bg_receipt - $mb_count";
            else
                $sql .= ", bg_reject = bg_reject - $mb_count";
            $sql .= " where bg_no='{$res2['bg_no']}'";

            sql_query($sql);
        }
        else
        {
            if ($bk_receipt != $res2['bk_receipt']) {
                if ($bk_receipt == 1)
                    $sql_sms = "bg_receipt = bg_receipt + $mb_count, bg_reject = bg_reject - $mb_count";
                else
                    $sql_sms = "bg_receipt = bg_receipt - $mb_count, bg_reject = bg_reject + $mb_count";

                sql_query("update {$g5['sms5_book_group_table']} set $sql_sms where bg_no='{$res2['bg_no']}'");
            }

            if ($bk_receipt) $receipt++;

            sql_query("update {$g5['sms5_book_table']} set $field where mb_id='{$res['mb_id']}'");
        }
    }
    else if ($res['mb_leave_date'] == '') // 기존에 등록되어 있지 않을 경우 추가 (삭제된 회원이 아닐 경우)
    {
        if ($bk_receipt == 1) {
            $sql_sms = "bg_receipt = bg_receipt + 1";
            $receipt++;
        } else {
            $sql_sms = "bg_reject = bg_reject + 1";
        }

        sql_query("insert into {$g5['sms5_book_table']} set $field, bg_no=1");
        sql_query("update {$g5['sms5_book_group_table']} set bg_count = bg_count + 1, bg_member = bg_member + 1, $sql_sms where bg_no=1");
    }

    $count++;
}

sql_query("update {$g5['sms5_config_table']} set cf_datetime='".G5_TIME_YMDHIS."'");

$msg = '';

$msg .= '<p>회원정보를 휴대폰번호 DB로 업데이트 하였습니다.</p>';
$msg .= '<dl id="sms_mbup">';
$msg .= '<dt>총 회원 수</dt><dd>'.number_format($count).'명</dd>';
$msg .= '<dt>삭제된 회원</dt><dd>'.number_format($leave).'명</dd>';
$msg .= '<dt><span style="gray">휴대폰번호 없음</span></dt><dd>'.number_format($hp_empty).' 명</dd>';
$msg .= '<dt><span style="color:blue;">휴대폰번호 정상</span></dt><dd>'.number_format($hp_yes).' 명</span>&nbsp;';
$msg .= '(<span style="color:blue;">수신</span>'.number_format($receipt).' 명';
$msg .= ' / ';
$msg .= '<span style="color:red;">거부</span>'.number_format($hp_yes-$receipt).' 명)</dd>';
$msg .= '<dt><span style="color:red;">휴대폰번호 오류</span></dt><dd>'.number_format($hp_no).' 명</span></dd>';
$msg .= '</dl>';
$msg .= '<p>프로그램의 실행을 끝마치셔도 좋습니다.</p>';

if( $mtype == "json" ){
    $json_msg = array();
    $json_msg['datetime'] = G5_TIME_YMDHIS;
    $json_msg['res_msg'] = $msg;
    die( json_encode($json_msg) );
} else {
    die( $msg );
}
?>