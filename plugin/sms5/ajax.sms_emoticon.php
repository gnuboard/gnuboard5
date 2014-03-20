<?php
include_once("./_common.php");
include_once("./JSON.php");

if( !function_exists('json_encode') ) {
    function json_encode($data) {
        $json = new Services_JSON();
        return( $json->encode($data) );
    }
}

$page_size = 9;

if (!$page) $page = 1;

if (is_numeric($fg_no)) 
    $sql_group = " and fg_no='$fg_no' ";
else
    $sql_group = "";

if ($st == 'all') {
    $sql_search = "and (fo_name like '%{$sv}%' or fo_content like '%{$sv}%')";
} else if ($st == 'name') {
    $sql_search = "and fo_name like '%{$sv}%'";
} else if ($st == 'content') {
    $sql_search = "and fo_content like '%{$sv}%'";
} else {
    $sql_search = '';
}

$total_res = sql_fetch("select count(*) as cnt from {$g5['sms5_form_table']} where fg_member = 1 $sql_group $sql_search");
$total_count = $total_res['cnt'];

$total_page = (int)($total_count/$page_size) + ($total_count%$page_size==0 ? 0 : 1);
$page_start = $page_size * ( $page - 1 );

$vnum = $total_count - (($page-1) * $page_size);

$group = array();
$qry = sql_query("select * from {$g5['sms5_form_group_table']} where fg_member = 1 order by fg_name");
while ($res = sql_fetch_array($qry)) array_push($group, $res);

$res = sql_fetch("select count(*) as cnt from {$g5['sms5_form_table']} where fg_no=0");
$no_count = $res['cnt'];

$count = 1;
$qry = sql_query("select * from {$g5['sms5_form_table']} where fg_member = 1 $sql_group $sql_search order by fo_no desc limit $page_start, $page_size");
$list_text = array();

for($k=0;$res = sql_fetch_array($qry);$k++) 
{
    $tmp = sql_fetch("select fg_name from {$g5['sms5_form_group_table']} where fg_no='{$res['fg_no']}'");
    if (!$tmp)
        $group_name = '미분류';
    else
        $group_name = $tmp['fg_name'];

    $list_text[$k]['fo_no'] = $res['fo_no'];
    $list_text[$k]['fo_content'] = $res['fo_content'];
    $list_text[$k]['fo_content'] = $res['fo_content'];
    $list_text[$k]['fo_name'] = cut_str($res['fo_name'],20);
}

$arr_ajax_msg['error'] = "";
$arr_ajax_msg['list_text'] = $list_text;
$arr_ajax_msg['page'] = $page;
$arr_ajax_msg['total_count'] = $total_count;
$arr_ajax_msg['total_page'] = $total_page;
die( json_encode($arr_ajax_msg) );
?>