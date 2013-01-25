<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

// 총몇개 = 한줄에 몇개 * 몇줄
$items = $list_mod * $list_row;
// <TD> 태그 폭
$td_width = (int)(100 / $list_mod);

$sql = "select COUNT(*) as cnt $sql_common ";
$row = sql_fetch($sql);
$total_count = $row[cnt];

// 전체 페이지 계산
$total_page  = ceil($total_count / $items);
// 페이지가 없으면 첫 페이지 (1 페이지)
if ($page == "") $page = 1;
// 시작 레코드 구함
$from_record = ($page - 1) * $items;
?>
