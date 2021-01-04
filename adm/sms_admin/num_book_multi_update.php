<?php
$sub_menu = "900800";
include_once("./_common.php");

auth_check_menu($auth, $sub_menu, "w");

$g5['title'] = "전화번호부";

$post_bk_no = (isset($_POST['bk_no']) && is_array($_POST['bk_no'])) ? $_POST['bk_no'] : array();

for ($i=0; $i<count($post_bk_no); $i++) 
{
    $bk_no = $post_bk_no[$i];
    if (!trim($bk_no)) continue;

    $res = sql_fetch("select * from {$g5['sms5_book_table']} where bk_no='$bk_no'");
    if (!$res) continue;

    if ($atype == 'reject') // 수신거부
    {
        sql_query("update {$g5['sms5_book_table']} set bk_receipt=0 where bk_no='$bk_no'");

        if ($res['mb_id'])
           sql_query("update {$g5['member_table']} set mb_sms=0 where mb_id='{$res['mb_id']}'");

        if ($res['bk_receipt'] == 1)
            sql_query("update {$g5['sms5_book_group_table']} set bg_receipt= case bg_receipt when 0 then 0 else bg_receipt - 1 end, bg_reject=bg_reject+1 where bg_no='{$res['bg_no']}'");
    }
    else if ($atype == 'receipt') // 수신허용
    {
        sql_query("update {$g5['sms5_book_table']} set bk_receipt=1 where bk_no='$bk_no'");

        if ($res['mb_id'])
           sql_query("update {$g5['member_table']} set mb_sms=1 where mb_id='{$res['mb_id']}'");

        if ($res['bk_receipt'] == 0)
            sql_query("update {$g5['sms5_book_group_table']} set bg_receipt=bg_receipt+1, bg_reject= case bg_reject when 0 then 0 else bg_reject - 1 end where bg_no='{$res['bg_no']}'");
    }
    else if ($atype == 'del') // 삭제
    {
        sql_query("delete from {$g5['sms5_book_table']} where bk_no='$bk_no'");

        if ($res['bk_receipt'] == 1) $bg_sms = 'bg_receipt'; else $bg_sms = 'bg_reject';
        if ($res['mb_id']) $bg_mb = 'bg_member'; else $bg_mb = 'bg_nomember';

        sql_query("update {$g5['sms5_book_group_table']} set $bg_sms = case $bg_sms when 0 then 0 else $bg_sms - 1 end , $bg_mb = case $bg_mb when 0 then 0 else $bg_mb - 1 end, bg_count = case bg_count when 0 then 0 else bg_count - 1 end where bg_no='{$res['bg_no']}'");

        /*
        if (!$res['mb_id']) {
            sql_query("delete from {$g5['sms5_book_table']} where bk_no='$bk_no'");
            sql_query("update {$g5['sms5_book_group_table']} set bg_count = bg_count - 1 where bg_no='{$res['bg_no']}'");

            if ($res['bk_receipt'] == 1)
                sql_query("update {$g5['sms5_book_group_table']} set bg_receipt = bg_receipt - 1, bg_count = bg_count - 1 where bg_no='{$res['bg_no']'}");
            else
                sql_query("update {$g5['sms5_book_group_table']} set bg_reject = bg_reject - 1, bg_count = bg_count - 1 where bg_no='{$res['bg_no']}'");
        }
        */
    }
}
if( $str_query ){
    $str_query = '?'.$str_query;
}
goto_url('./num_book.php'.$str_query);