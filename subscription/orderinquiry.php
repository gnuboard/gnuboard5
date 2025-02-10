<?php
include_once('./_common.php');

// 정기결제는 회원만 구독이 가능하다.
if (!$is_member) {
    goto_url(G5_BBS_URL.'/login.php?url='.urlencode(G5_SUBSCRIPTION_URL.'/orderinquiry.php'));
}

if (G5_IS_MOBILE) {
    include_once(G5_MSUBSCRIPTION_PATH.'/orderinquiry.php');
    return;
}

define("_ORDERINQUIRY_", true);

$order_info = array();
$od_id = isset($_POST['od_id']) ? safe_replace_regex($_POST['od_id'], 'od_id') : '';

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);

$row = sql_bind_select_fetch($g5['g5_subscription_order_table'], 'count(*) as cnt', array('mb_id' => $member['mb_id']));
$cnt = $total_count = $row['cnt'];

// 조건에 맞는 주문서가 없다면
if ($total_count == 0) {
    alert('주문이 존재하지 않습니다.', G5_SUBSCRIPTION_URL);
}

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$g5['title'] = '정기구독 내역조회';
include_once('./_head.php');
?>

<!-- 주문 내역 시작 { -->
<div id="sod_v">
    <?php
    $sql_limit = $rows;
    $sql_offset = $from_record;
    
    include "./orderinquiry.sub.php";
    ?>

    <?php echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>
</div>
<!-- } 주문 내역 끝 -->

<?php
include_once('./_tail.php');