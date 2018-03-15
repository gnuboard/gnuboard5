<?php
include_once('./_common.php');

if (!$is_member) {
    alert_close("상품문의는 회원만 작성이 가능합니다.");
}

$iq_id = (int) trim($_REQUEST['iq_id']);
$iq_subject = trim($_POST['iq_subject']);
$iq_question = trim($_POST['iq_question']);
$iq_question = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $iq_question);
$iq_answer = trim($_POST['iq_answer']);
$hash = trim($_REQUEST['hash']);
$get_editor_img_mode = $config['cf_editor'] ? false : true;

if ($w == "" || $w == "u") {
    $iq_name     = addslashes(strip_tags($member['mb_name']));
    $iq_password = $member['mb_password'];

    if (!$iq_subject) alert("제목을 입력하여 주십시오.");
    if (!$iq_question) alert("질문을 입력하여 주십시오.");
}

if($is_mobile_shop)
    $url = './iteminfo.php?it_id='.$it_id.'&info=qa';
else
    $url = "./item.php?it_id=$it_id&_=".get_token()."#sit_qa";

if ($w == "")
{
    $sql = "insert {$g5['g5_shop_item_qa_table']}
               set it_id = '$it_id',
                   mb_id = '{$member['mb_id']}',
                   iq_secret = '$iq_secret',
                   iq_name  = '$iq_name',
                   iq_email = '$iq_email',
                   iq_hp = '$iq_hp',
                   iq_password  = '$iq_password',
                   iq_subject  = '$iq_subject',
                   iq_question = '$iq_question',
                   iq_time = '".G5_TIME_YMDHIS."',
                   iq_ip = '$REMOTE_ADDR' ";
    sql_query($sql);

    $alert_msg = '상품문의가 등록 되었습니다.';
}
else if ($w == "u")
{
    if (!$is_admin)
    {
        $sql = " select count(*) as cnt from {$g5['g5_shop_item_qa_table']} where mb_id = '{$member['mb_id']}' and iq_id = '$iq_id' ";
        $row = sql_fetch($sql);
        if (!$row['cnt'])
            alert("자신의 상품문의만 수정하실 수 있습니다.");
    }

    $sql = " update {$g5['g5_shop_item_qa_table']}
                set iq_secret = '$iq_secret',
                    iq_email = '$iq_email',
                    iq_hp = '$iq_hp',
                    iq_subject = '$iq_subject',
                    iq_question = '$iq_question'
              where iq_id = '$iq_id' ";
    sql_query($sql);

    $alert_msg = '상품문의가 수정 되었습니다.';
}
else if ($w == "d")
{
    if (!$is_admin)
    {
        $sql = " select iq_answer from {$g5['g5_shop_item_qa_table']} where mb_id = '{$member['mb_id']}' and iq_id = '$iq_id' ";
        $row = sql_fetch($sql);
        if (!$row)
            alert("자신의 상품문의만 삭제하실 수 있습니다.");

        if ($row['iq_answer'])
            alert("답변이 있는 상품문의는 삭제하실 수 없습니다.");
    }

    // 에디터로 첨부된 이미지 삭제
    $sql = " select iq_question, iq_answer from {$g5['g5_shop_item_qa_table']} where iq_id = '$iq_id' and md5(concat(iq_id,iq_time,iq_ip)) = '{$hash}' ";
    $row = sql_fetch($sql);

    $imgs = get_editor_image($row['iq_question'], $get_editor_img_mode);

    for($i=0;$i<count($imgs[1]);$i++) {
        $p = parse_url($imgs[1][$i]);
        if(strpos($p['path'], "/data/") != 0)
            $data_path = preg_replace("/^\/.*\/data/", "/data", $p['path']);
        else
            $data_path = $p['path'];

        if( preg_match('/(gif|jpe?g|bmp|png)$/i', strtolower(end(explode('.', $data_path))) ) ){

            $destfile = ( ! preg_match('/\w+\/\.\.\//', $data_path) ) ? G5_PATH.$data_path : '';

            if($destfile && preg_match('/\/data\/editor\/[A-Za-z0-9_]{1,20}\//', $destfile) && is_file($destfile))
                @unlink($destfile);
        }
    }

    $imgs = get_editor_image($row['iq_answer'], $get_editor_img_mode);

    for($i=0;$i<count($imgs[1]);$i++) {
        $p = parse_url($imgs[1][$i]);
        if(strpos($p['path'], "/data/") != 0)
            $data_path = preg_replace("/^\/.*\/data/", "/data", $p['path']);
        else
            $data_path = $p['path'];

        if( preg_match('/(gif|jpe?g|bmp|png)$/i', strtolower(end(explode('.', $data_path))) ) ){

            $destfile = ( ! preg_match('/\w+\/\.\.\//', $data_path) ) ? G5_PATH.$data_path : '';

            if($destfile && preg_match('/\/data\/editor\/[A-Za-z0-9_]{1,20}\//', $destfile) && is_file($destfile))
                @unlink($destfile);
        }
    }

    $sql = " delete from {$g5['g5_shop_item_qa_table']} where iq_id = '$iq_id' and md5(concat(iq_id,iq_time,iq_ip)) = '{$hash}' ";
    sql_query($sql);

    $alert_msg = '상품문의가 삭제 되었습니다.';
}

if($w == 'd')
    alert($alert_msg, $url);
else
    alert_opener($alert_msg, $url);
?>
