<?php
include_once('./_common.php');

// 정기구독은 회원만 구독이 가능하다.
if (!$is_member) {
    goto_url(G5_BBS_URL . '/login.php?url=' . urlencode(G5_SUBSCRIPTION_URL . basename(__FILE__)));
}

if (G5_IS_MOBILE) {
    include_once(G5_MSUBSCRIPTION_PATH . '/subscription_list.php');
    return;
}

define("_ORDERINQUIRY_", true);

$order_info = array();
$od_id = isset($_POST['od_id']) ? safe_replace_regex($_POST['od_id'], 'od_id') : '';
$uid = isset($_REQUEST['uid']) ? clean_xss_tags($_REQUEST['uid']) : '';
$status = isset($_REQUEST['status']) ? (string) preg_replace('/[^0-9]/i', '', $_REQUEST['status']) : '';

// 테이블의 전체 레코드수만 얻음
/*
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
*/

$add_sql = '';
if ($status || $status === '0') {
    $add_sql = " and od_enable_status = '". (int) $status ."' ";
}

$sql = "SELECT COUNT(*) AS cnt 
        FROM {$g5['g5_subscription_order_table']} 
        WHERE mb_id = '" . $member['mb_id'] . "' {$add_sql} ";
$row = sql_fetch($sql);

$cnt = $total_count = isset($row['cnt']) ? (int) $row['cnt'] : 0;

// 조건에 맞는 주문서가 없다면
if ($total_count == 0) {
    // alert('주문이 존재하지 않습니다.', G5_SUBSCRIPTION_URL);
}

$orders = array();

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) {
    $page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if ($total_count) {
    $sql = "SELECT * FROM `{$g5['g5_subscription_order_table']}` 
    WHERE mb_id = '" . $member['mb_id'] . "' {$add_sql}
    ORDER BY od_id DESC 
    LIMIT $rows OFFSET $from_record";
    $result = sql_query($sql);
    
    for ($i = 0; $row = sql_fetch_array($result); $i++) {
        $orders[] = $row;
    }
}
                
$g5['title'] = '정기구독 내역조회';
include_once('./_head.php');
?>

<!-- 주문 내역 시작 { -->
<div id="sod_v">
    <?php
    $sql_limit = $rows;
    $sql_offset = $from_record;

    include "./subscription_list.sub.php";
    ?>

    <?php echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>
</div>
<!-- } 주문 내역 끝 -->

<?php
include_once('./_tail.php');
