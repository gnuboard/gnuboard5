<?php
include_once('./_common.php');

$call = isset($_REQUEST['call']) ?  strtolower(preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['call'])) : '';

if( ! $call ){
    return;
}

if( ! $is_admin ){
	$sql = " select count(*) as cnt from {$g5['auth_table']} where mb_id = '{$member['mb_id']}' ";
	$row = sql_fetch($sql);
	if ( ! $row['cnt']) {
		return;
	}
}

run_event('admin_request_handler_'.$call, $arr_query, $token);

$sub_menu = admin_menu_find_by($call, 'sub_menu');
$g5['title'] = admin_menu_find_by($call, 'title');

include_once ('./admin.head.php');

run_event('admin_get_page_'.$call, $arr_query, $token);

include_once ('./admin.tail.php');
?>