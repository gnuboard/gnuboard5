<?php
include_once('./_common.php');

if (!$is_member) {
    alert_close("상품문의는 회원만 작성이 가능합니다.");
}

$it_id       = isset($_REQUEST['it_id']) ? safe_replace_regex($_REQUEST['it_id'], 'it_id') : '';
$iq_id = isset($_REQUEST['iq_id']) ? (int) $_REQUEST['iq_id'] : 0;
$iq_subject = isset($_POST['iq_subject']) ? trim($_POST['iq_subject']) : '';
$iq_question = isset($_POST['iq_question']) ? trim($_POST['iq_question']) : '';
$iq_question = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $iq_question);
$iq_answer = isset($_POST['iq_answer']) ? trim($_POST['iq_answer']) : '';
$hash = isset($_REQUEST['hash']) ? trim($_REQUEST['hash']) : '';
$get_editor_img_mode = $config['cf_editor'] ? false : true;

$iq_secret = isset($_POST['iq_secret']) ? (int) $_POST['iq_secret'] : 0;
$iq_email = isset($_POST['iq_email']) ? clean_xss_tags($_POST['iq_email'], 1, 1) : '';
$iq_hp = isset($_POST['iq_hp']) ? clean_xss_tags($_POST['iq_hp'], 1, 1) : '';
$is_mobile_shop = isset($_REQUEST['is_mobile_shop']) ? (int) $_REQUEST['is_mobile_shop'] : 0;

if ($w == "" || $w == "u") {
    $iq_name     = addslashes(strip_tags($member['mb_name']));
    $iq_password = $member['mb_password'];

    if (!$iq_subject) alert("제목을 입력하여 주십시오.");
    if (!$iq_question) alert("질문을 입력하여 주십시오.");
}

if($is_mobile_shop)
    $url = './iteminfo.php?it_id='.$it_id.'&info=qa';
else
    $url = shop_item_url($it_id, "_=".get_token()."#sit_qa");

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
                   iq_ip = '".$_SERVER['REMOTE_ADDR']."' ";
    sql_query($sql);
    $iq_id = sql_insert_id();
    run_event('shop_item_qa_created', $iq_id, $it_id);

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
    run_event('shop_item_qa_updated', $iq_id, $it_id);

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

    // 에디터로 첨부된 썸네일 이미지만 삭제
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

            if ($destfile && preg_match('/\/data\/editor\/[A-Za-z0-9_]{1,20}\//', $destfile) && is_file($destfile)) {
                delete_item_thumbnail(dirname($destfile), basename($destfile));
                //@unlink($destfile);
            }
        }
    }

    $imgs = get_editor_image($row['iq_answer'], $get_editor_img_mode);

    $imgs_count = (isset($imgs[1]) && is_array($imgs[1])) ? count($imgs[1]) : 0;

    for($i=0;$i<$imgs_count;$i++) {
        $p = parse_url($imgs[1][$i]);
        if(strpos($p['path'], "/data/") != 0)
            $data_path = preg_replace("/^\/.*\/data/", "/data", $p['path']);
        else
            $data_path = $p['path'];

        if( preg_match('/(gif|jpe?g|bmp|png)$/i', strtolower(end(explode('.', $data_path))) ) ){

            $destfile = ( ! preg_match('/\w+\/\.\.\//', $data_path) ) ? G5_PATH.$data_path : '';

            if ($destfile && preg_match('/\/data\/editor\/[A-Za-z0-9_]{1,20}\//', $destfile) && is_file($destfile)) {
                delete_item_thumbnail(dirname($destfile), basename($destfile));
                // @unlink($destfile);
            }
        }
    }

    $sql = " delete from {$g5['g5_shop_item_qa_table']} where iq_id = '$iq_id' and md5(concat(iq_id,iq_time,iq_ip)) = '{$hash}' ";
    sql_query($sql);
    run_event('shop_item_qa_deleted', $iq_id, $it_id);

    $alert_msg = '상품문의가 삭제 되었습니다.';
}

if($w == 'd')
    alert($alert_msg, $url);
else
    alert_opener($alert_msg, $url);