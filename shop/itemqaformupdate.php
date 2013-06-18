<?php
include_once('./_common.php');

if (!$is_member) {
    alert_close("상품문의는 회원만 작성이 가능합니다.");
}

$iq_id = escape_trim($_POST['iq_id']);
$iq_subject = escape_trim($_POST['iq_subject']);
$iq_question = escape_trim(stripslashes($_POST['iq_question']));
$iq_answer = escape_trim(stripslashes($_POST['iq_answer']));
$hash = escape_trim($_POST['hash']);

if ($w == "" || $w == "u") {
    $iq_name     = $member['mb_name'];
    $iq_password = $member['mb_password'];

    if (!$iq_subject) alert("제목을 입력하여 주십시오.");
    if (!$iq_question) alert("질문을 입력하여 주십시오.");
}

$url = "./item.php?it_id=$it_id&amp;_=".get_token()."#sit_qa";

if ($w == "")
{
    $sql = "insert {$g4['shop_item_qa_table']}
               set it_id = '$it_id',
                   mb_id = '{$member['mb_id']}',
                   iq_name  = '$iq_name',
                   iq_password  = '$iq_password',
                   iq_subject  = '$iq_subject',
                   iq_question = '$iq_question',
                   iq_time = '".G4_TIME_YMDHIS."',
                   iq_ip = '$REMOTE_ADDR' ";
    sql_query($sql);

    alert_opener("상품문의가 등록 되었습니다.", $url);
}
else if ($w == "u")
{
    if (!$is_amdin) 
    {
        $sql = " select count(*) as cnt from {$g4['shop_item_qa_table']} where mb_id = '{$member['mb_id']}' and iq_id = '$iq_id' ";
        $row = sql_fetch($sql);
        if (!$row['cnt'])
            alert("자신의 상품문의만 수정하실 수 있습니다.");
    }

    $sql = " update {$g4['shop_item_qa_table']}
                set iq_subject = '$iq_subject',
                    iq_question = '$iq_question'
              where iq_id = '$iq_id' ";
    sql_query($sql);

    alert_opener("상품문의가 수정 되었습니다.", $url);
}
else if ($w == "d")
{
    if (!$is_admin) 
    {
        $sql = " select iq_answer from {$g4['shop_item_qa_table']} where mb_id = '{$member['mb_id']}' and iq_id = '$iq_id' ";
        $row = sql_fetch($sql);
        if (!$row)
            alert("자신의 상품문의만 삭제하실 수 있습니다.");

        if ($row['iq_answer'])
            alert("답변이 있는 상품문의는 삭제하실 수 없습니다.");
    }

    //$sql = " delete from {$g4['shop_item_qa_table']} where mb_id = '{$member['mb_id']}' and iq_id = '$iq_id' ";
    $sql = " delete from {$g4['shop_item_qa_table']} where iq_id = '$iq_id' and md5(concat(iq_id,iq_time,iq_ip)) = '{$hash}' ";
    sql_query($sql);

    alert("상품문의가 삭제 되었습니다.", $url);
}
?>
