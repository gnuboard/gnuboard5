<?php
include_once('./_common.php');
<<<<<<< HEAD

if (!$is_member) {
    alert_close("상품문의는 회원만 작성이 가능합니다.");
}

$iq_id = escape_trim($_REQUEST['iq_id']);
$iq_subject = escape_trim($_REQUEST['iq_subject']);
$iq_question = escape_trim($_REQUEST['iq_question']);
$iq_answer = escape_trim($_REQUEST['iq_answer']);
$hash = escape_trim($_REQUEST['hash']);

if ($w == "" || $w == "u") {
    $iq_name     = $member['mb_name'];
    $iq_password = $member['mb_password'];

    if (!$iq_subject) alert("제목을 입력하여 주십시오.");
    if (!$iq_question) alert("질문을 입력하여 주십시오.");
=======
include_once(G4_GCAPTCHA_PATH.'/gcaptcha.lib.php');

if ($w == '' || $w == 'u')
{
    if (!chk_captcha()) {
        alert('자동등록방지 숫자가 틀렸습니다.');
    }

    if (!$is_member)
    {
        if (!trim($_POST['iq_name'])) alert('이름을 입력하여 주십시오.');
        if (!trim($_POST['iq_password'])) alert('패스워드를 입력하여 주십시오.');
    }
    else
    {
        $iq_name = $member['mb_name'];
        $iq_password = $member['mb_password'];
    }

    $iq_password = sql_password($iq_password);

    if (!trim($_POST['iq_subject'])) alert('제목을 입력하여 주십시오.');
    if (!trim($_POST['iq_question'])) alert('내용을 입력하여 주십시오.');
>>>>>>> 8ba2a84198461168008549042bbfc2d01e738d03
}

$url = "./item.php?it_id=$it_id";

<<<<<<< HEAD
if ($w == "")
{
=======
if ($w == '')
{
    $sql = " select max(iq_id) as max_iq_id from {$g4['shop_item_qa_table']} ";
    $row = sql_fetch($sql);
    $max_iq_id = $row['max_iq_id'];

    $sql = " select max(iq_id) as max_iq_id from {$g4['shop_item_qa_table']}
              where it_id = '$it_id'
                and mb_id = '{$member['mb_id']}' ";
    $row = sql_fetch($sql);
    if ($row['max_iq_id'] && $row['max_iq_id'] == $max_iq_id)
        alert('같은 상품에 대하여 계속해서 질문 하실 수 없습니다.');

>>>>>>> 8ba2a84198461168008549042bbfc2d01e738d03
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

<<<<<<< HEAD
    alert_opener("상품문의가 등록 되었습니다.", $url);
}
else if ($w == "u")
=======
    alert_opener('상품문의가 등록되었습니다.', $url);
}
else if ($w == 'u')
>>>>>>> 8ba2a84198461168008549042bbfc2d01e738d03
{
    $sql = " select iq_password from {$g4['shop_item_qa_table']} where iq_id = '$iq_id' ";
    $row = sql_fetch($sql);
    if ($row['iq_password'] != $iq_password)
<<<<<<< HEAD
        alert("패스워드가 틀리므로 수정하실 수 없습니다.");
=======
        alert('패스워드가 틀리므로 수정하실 수 없습니다.');
>>>>>>> 8ba2a84198461168008549042bbfc2d01e738d03

    $sql = " update {$g4['shop_item_qa_table']}
                set iq_subject = '$iq_subject',
                    iq_question = '$iq_question'
              where iq_id = '$iq_id' ";
    sql_query($sql);

<<<<<<< HEAD
    alert_opener("상품문의가 수정 되었습니다.", $url);
}
else if ($w == "d")
{
    if (!$is_admin) 
=======
    alert_opener('상품문의가 수정되었습니다.', $url);
}
else if ($w == 'd')
{
    if ($is_member)
>>>>>>> 8ba2a84198461168008549042bbfc2d01e738d03
    {
        $sql = " select count(*) as cnt from {$g4['shop_item_qa_table']} where mb_id = '{$member['mb_id']}' and iq_id = '$iq_id' ";
        $row = sql_fetch($sql);
        if (!$row['cnt'])
<<<<<<< HEAD
            alert("자신의 상품문의만 삭제하실 수 있습니다.");
    }

    //$sql = " delete from {$g4['shop_item_qa_table']} where mb_id = '{$member['mb_id']}' and iq_id = '$iq_id' ";
    $sql = " delete from {$g4['shop_item_qa_table']} where iq_id = '$iq_id' and md5(concat(iq_id,iq_time,iq_ip)) = '{$hash}' ";
    sql_query($sql);

    alert("상품문의가 삭제 되었습니다.", $url);
=======
            die('자신의 상품문의만 삭제하실 수 있습니다.');
    }
    else
    {
        $iq_password = sql_password($iq_password);

        $sql = " select iq_password from {$g4['shop_item_qa_table']} where iq_id = '$iq_id' ";
        $row = sql_fetch($sql);
        if ($row['iq_password'] != $iq_password)
            die('패스워드가 틀리므로 삭제하실 수 없습니다.');
    }

    $sql = " delete from {$g4['shop_item_qa_table']} where mb_id = '{$member['mb_id']}' and iq_id = '$iq_id' ";
    sql_query($sql);

    goto_url($url);
>>>>>>> 8ba2a84198461168008549042bbfc2d01e738d03
}
?>
