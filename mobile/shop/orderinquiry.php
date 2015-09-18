<?php
include_once('./_common.php');

define("_ORDERINQUIRY_", true);

$od_pwd = get_encrypt_string($od_pwd);

// 회원인 경우
if ($is_member)
{
    $sql_common = " from {$g5['g5_shop_order_table']} where mb_id = '{$member['mb_id']}' ";
}
else if ($od_id && $od_pwd) // 비회원인 경우 주문서번호와 비밀번호가 넘어왔다면
{
    $sql_common = " from {$g5['g5_shop_order_table']} where od_id = '$od_id' and od_pwd = '$od_pwd' ";
}
else // 그렇지 않다면 로그인으로 가기
{
    goto_url(G5_BBS_URL.'/login.php?url='.urlencode(G5_SHOP_URL.'/orderinquiry.php'));
}

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

// 비회원 주문확인시 비회원의 모든 주문이 다 출력되는 오류 수정
// 조건에 맞는 주문서가 없다면
if ($total_count == 0)
{
    if ($is_member) // 회원일 경우는 메인으로 이동
        alert('주문이 존재하지 않습니다.', G5_SHOP_URL);
    else // 비회원일 경우는 이전 페이지로 이동
        alert('주문이 존재하지 않습니다.');
}

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함


// 비회원 주문확인의 경우 바로 주문서 상세조회로 이동
if (!$is_member)
{
    $sql = " select od_id, od_time, od_ip from {$g5['g5_shop_order_table']} where od_id = '$od_id' and od_pwd = '$od_pwd' ";
    $row = sql_fetch($sql);
    if ($row['od_id']) {
        $uid = md5($row['od_id'].$row['od_time'].$row['od_ip']);
        set_session('ss_orderview_uid', $uid);
        goto_url(G5_SHOP_URL.'/orderinquiryview.php?od_id='.$row['od_id'].'&amp;uid='.$uid);
    }
}

$g5['title'] = '주문내역조회';
include_once(G5_MSHOP_PATH.'/_head.php');
?>

<div id="sod_v">
    <p id="sod_v_info">주문서번호 링크를 누르시면 주문상세내역을 조회하실 수 있습니다.</p>

    <?php
    $limit = " limit $from_record, $rows ";
    include G5_MSHOP_PATH.'/orderinquiry.sub.php';
    ?>

    <?php echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>
</div>

<?php
include_once(G5_MSHOP_PATH.'/_tail.php');
?>
