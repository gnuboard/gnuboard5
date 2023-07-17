<?php
$sub_menu = '400650';
include_once('./_common.php');

check_demo();

if ($w == 'd')
    auth_check_menu($auth, $sub_menu, "d");
else
    auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$posts = array();
$check_keys = array('is_subject', 'is_content', 'is_confirm', 'is_reply_subject', 'is_reply_content', 'is_id');

foreach($check_keys as $key){

    if( in_array($key, array('is_content', 'is_reply_content')) ){
        $posts[$key] = isset($_POST[$key]) ? $_POST[$key] : '';
    } else {
        $posts[$key] = isset($_POST[$key]) ? clean_xss_tags($_POST[$key], 1, 1) : '';
    }
}

if ($w == "u")
{
    $sql = "update {$g5['g5_shop_item_use_table']}
               set is_subject = '".$posts['is_subject']."',
                   is_content = '".$posts['is_content']."',
                   is_confirm = '".$posts['is_confirm']."',
                   is_reply_subject = '".$posts['is_reply_subject']."',
                   is_reply_content = '".$posts['is_reply_content']."',
                   is_reply_name = '".$member['mb_nick']."'
             where is_id = '".$posts['is_id']."'";
    sql_query($sql);
    run_event('shop_admin_item_use_updated', $posts['is_id']);

    if( isset($_POST['it_id']) ) {
        update_use_cnt($_POST['it_id']);
        update_use_avg($_POST['it_id']);
    }

    goto_url("./itemuseform.php?w=$w&amp;is_id=$is_id&amp;sca=$sca&amp;$qstr");
}
else
{
    alert();
}