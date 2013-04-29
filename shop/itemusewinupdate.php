<?php
include_once('./_common.php');
include_once(G4_GCAPTCHA_PATH.'/gcaptcha.lib.php');

if (!$is_member) {
    alert_close("사용후기는 회원만 평가가 가능합니다.");
}

$is_subject = trim($_REQUEST['is_subject']);
$is_content = trim($_REQUEST['is_content']);

if ($w == '' || $w == 'u') {
    if (!chk_captcha()) {
        alert('자동등록방지 숫자가 틀렸습니다.');
    }

    $is_name     = $member['mb_name'];
    $is_password = $member['mb_password'];

    if (!$is_subject) alert("제목을 입력하여 주십시오.");
    if (!$is_content) alert("내용을 입력하여 주십시오.");
}

$url = "./item.php?it_id=$it_id";

if ($w == '')
{
    $sql = " select max(is_id) as max_is_id from {$g4['shop_item_ps_table']} ";
    $row = sql_fetch($sql);
    $max_is_id = $row['max_is_id'];

    $sql = " select max(is_id) as max_is_id from {$g4['shop_item_ps_table']} where it_id = '$it_id' and mb_id = '{$member['mb_id']}' ";
    $row = sql_fetch($sql);
    if ($row['max_is_id'] && $row['max_is_id'] == $max_is_id)
        alert("같은 상품에 대하여 계속해서 평가하실 수 없습니다.");

    $sql = "insert {$g4['shop_item_ps_table']}
               set it_id = '$it_id',
                   mb_id = '{$member['mb_id']}',
                   is_score = '$is_score',
                   is_name = '$is_name',
                   is_password = '$is_password',
                   is_subject = '$is_subject',
                   is_content = '$is_content',
                   is_time = '".G4_TIME_YMDHIS."',
                   is_ip = '{$_SERVER['REMOTE_ADDR']}' ";
    if (!$default['de_item_ps_use'])
        $sql .= ", is_confirm = '1' ";
    sql_query($sql);

    if ($default['de_item_ps_use']) {
        alert_opener("평가하신 글은 관리자가 확인한 후에 표시됩니다.", $url);
    }  else {
        alert_opener("사용후기가 등록 되었습니다.", $url);
    }
}
else if ($w == 'u')
{
    $sql = " select is_password from {$g4['shop_item_ps_table']} where is_id = '$is_id' ";
    $row = sql_fetch($sql);
    if ($row['is_password'] != $is_password)
        alert("패스워드가 틀리므로 수정하실 수 없습니다.");

    $sql = " update {$g4['shop_item_ps_table']}
                set is_subject = '$is_subject',
                    is_content = '$is_content',
                    is_score = '$is_score'
              where is_id = '$is_id' ";
    sql_query($sql);

    alert_opener("사용후기가 수정 되었습니다.", $url);
}
?>