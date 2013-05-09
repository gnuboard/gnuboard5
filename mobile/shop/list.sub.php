<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 상품수
$items = $list_row;

$sql = "select COUNT(*) as cnt $sql_common ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

// 전체 페이지 계산
$total_page  = ceil($total_count / $items);
// 페이지가 없으면 첫 페이지 (1 페이지)
if ($page == "") $page = 1;
// 시작 레코드 구함
$from_record = ($page - 1) * $items;
?>
