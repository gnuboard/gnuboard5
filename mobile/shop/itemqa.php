<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

$it_id = isset($_REQUEST['it_id']) ? safe_replace_regex($_REQUEST['it_id'], 'it_id') : '';

$itemqa_list = G5_SHOP_URL."/itemqalist.php";
$itemqa_form = G5_SHOP_URL."/itemqaform.php?it_id=".$it_id;
$itemqa_formupdate = G5_SHOP_URL."/itemqaformupdate.php?it_id=".$it_id;

$sql_common = " from `{$g5['g5_shop_item_qa_table']}` where it_id = '{$it_id}' ";

// 테이블의 전체 레코드수만 얻음
$sql = " select COUNT(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = 5;
$total_page  = ceil($total_count / $rows); // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 레코드 구함

$sql = "select * $sql_common order by iq_id desc limit $from_record, $rows ";
$result = sql_query($sql);

$itemqa_skin = G5_MSHOP_SKIN_PATH.'/itemqa.skin.php';

if(!file_exists($itemqa_skin)) {
    echo str_replace(G5_PATH.'/', '', $itemqa_skin).' 스킨 파일이 존재하지 않습니다.';
} else {
    include_once($itemqa_skin);
}