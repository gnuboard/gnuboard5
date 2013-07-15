<?php
$sub_menu = '400430';
include_once('./_common.php');

auth_check($auth[$sub_menu], "d");

if($w == 'd') {
    $sql = " select rq_id from {$g4['shop_request_table']} where rq_id = '$rq_id' ";
    $row = sql_fetch($sql);
    if(!$row['rq_id'])
        alert('자료가 존재하지 않습니다.');

    $sql = " delete from {$g4['shop_request_table']} where rq_id = '$rq_id' or rq_parent = '$rq_id' ";
    sql_query($sql);
} else {
    $count = count($_POST['chk']);
    if(!$count)
        alert('삭제하시려는 항목을 하나이상 선택해 주십시오.');

    for($i=0; $i<$count; $i++) {
        $k = $_POST['chk'][$i];

        $sql = " delete from {$g4['shop_request_table']} where rq_id = '{$_POST['rq_id'][$k]}' or rq_parent = '{$_POST['rq_id'][$k]}' ";
        sql_query($sql);
    }
}

$qstr .= '&amp;rq_type='.$rq_type;

goto_url('./orderrequestlist.php?'.$qstr);
?>