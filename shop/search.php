<?php
include_once('./_common.php');

define('IS_SHOP_SEARCH', true);

if (G5_IS_MOBILE) {
    include_once(G5_MSHOP_PATH.'/search.php');
    return;
}

$g5['title'] = "상품 검색 결과";
include_once('./_head.php');

// QUERY 문에 공통적으로 들어가는 내용
// 상품명에 검색어가 포한된것과 상품판매가능인것만
$sql_common = " from {$g5['g5_shop_item_table']} a, {$g5['g5_shop_category_table']} b ";

$where = array();
$where[] = " (a.ca_id = b.ca_id and a.it_use = 1 and b.ca_use = 1) ";

$search_all = true;
// 상세검색 이라면
if (isset($_GET['qname']) || isset($_GET['qexplan']) || isset($_GET['qid']) || isset($_GET['qbasic']))
    $search_all = false;

$q       = utf8_strcut(get_search_string(trim($_GET['q'])), 30, "");
$qname   = isset($_GET['qname']) ? trim(clean_xss_tags($_GET['qname'])) : '';
$qexplan = isset($_GET['qexplan']) ? trim(clean_xss_tags($_GET['qexplan'])) : '';
$qid     = isset($_GET['qid']) ? trim(clean_xss_tags($_GET['qid'])) : '';
$qbasic  = isset($_GET['qbasic']) ? trim(clean_xss_tags($_GET['qbasic'])) : '';
$qcaid   = isset($_GET['qcaid']) ? preg_replace('#[^a-z0-9]#i', '', trim($_GET['qcaid'])) : '';
$qfrom   = isset($_GET['qfrom']) ? preg_replace('/[^0-9]/', '', trim($_GET['qfrom'])) : '';
$qto     = isset($_GET['qto']) ? preg_replace('/[^0-9]/', '', trim($_GET['qto'])) : '';

if (isset($_GET['qsort']))  {
    $qsort = trim($_GET['qsort']);
    $qsort = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\s]/", "", $qsort);
} else {
    $qsort = '';
}
if (isset($_GET['qorder']))  {
    $qorder = preg_match("/^(asc|desc)$/i", $qorder) ? $qorder : '';
} else {
    $qorder = '';
}

if(!($qname || $qexplan || $qid || $qbasic))
    $search_all = true;

// 검색범위 checkbox 처리
$qname_check = false;
$qexplan_check = false;
$qid_check = false;
$qbasic_check = false;

if($search_all) {
    $qname_check = true;
    $qexplan_check = true;
    $qid_check = true;
    $qbasic_check = true;
} else {
    if($qname)
        $qname_check = true;
    if($qexplan)
        $qexplan_check = true;
    if($qid)
        $qid_check = true;
    if($qbasic)
        $qbasic_check = true;
}

if ($q) {
    $arr = explode(" ", $q);
    $detail_where = array();
    for ($i=0; $i<count($arr); $i++) {
        $word = trim($arr[$i]);
        if (!$word) continue;

        $concat = array();
        if ($search_all || $qname)
            $concat[] = "a.it_name";
        if ($search_all || $qexplan)
            $concat[] = "a.it_explan2";
        if ($search_all || $qid)
            $concat[] = "a.it_id";
        if ($search_all || $qbasic)
            $concat[] = "a.it_basic";
        $concat_fields = "concat(".implode(",' ',",$concat).")";

        $detail_where[] = $concat_fields." like '%$word%' ";

        // 인기검색어
        insert_popular($concat, $word);
    }

    $where[] = "(".implode(" and ", $detail_where).")";
}

if ($qcaid)
    $where[] = " a.ca_id like '$qcaid%' ";

if ($qfrom && $qto)
    $where[] = " a.it_price between '$qfrom' and '$qto' ";

$sql_where = " where " . implode(" and ", $where);

// 상품 출력순서가 있다면
$qsort  = strtolower($qsort);
$qorder = strtolower($qorder);
$order_by = "";
// 아래의 $qsort 필드만 정렬이 가능하게 하여 다른 필드로 하여금 유추해 볼수 없게함
if (($qsort == "it_sum_qty" || $qsort == "it_price" || $qsort == "it_use_avg" || $qsort == "it_use_cnt" || $qsort == "it_update_time") &&
    ($qorder == "asc" || $qorder == "desc")) {
    $order_by = ' order by ' . $qsort . ' ' . $qorder . ' , it_order, it_id desc';
}

// 총몇개 = 한줄에 몇개 * 몇줄
$items = $default['de_search_list_mod'] * $default['de_search_list_row'];
// 페이지가 없으면 첫 페이지 (1 페이지)
if ($page < 1) $page = 1;
// 시작 레코드 구함
$from_record = ($page - 1) * $items;

// 검색된 내용이 몇행인지를 얻는다
$sql = " select COUNT(*) as cnt $sql_common $sql_where ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];
$total_page  = ceil($total_count / $items); // 전체 페이지 계산

$sql = " select b.ca_id, b.ca_name, count(*) as cnt $sql_common $sql_where group by b.ca_id order by b.ca_id ";
$result = sql_query($sql);

$categorys = array();
// 검색된 분류를 배열에 저장
while($row = sql_fetch_array($result)){
    $categorys[] = $row;
}

$q = get_text($q);
$search_skin = G5_SHOP_SKIN_PATH.'/search.skin.php';

$list_file = G5_SHOP_SKIN_PATH.'/'.$default['de_search_list_skin'];
if (file_exists($list_file) && is_include_path_check($list_file)) {
    define('G5_SHOP_CSS_URL', G5_SHOP_SKIN_URL);
    $list = new item_list($list_file, $default['de_search_list_mod'], $default['de_search_list_row'], $default['de_search_img_width'], $default['de_search_img_height']);
    $list->set_query(" select * $sql_common $sql_where {$order_by} limit $from_record, $items ");
}

if(!file_exists($search_skin)) {
    echo str_replace(G5_PATH.'/', '', $search_skin).' 스킨 파일이 존재하지 않습니다.';
} else {
    include_once($search_skin);
}

include_once('./_tail.php');