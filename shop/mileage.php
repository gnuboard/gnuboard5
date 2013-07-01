<?php
include_once('./_common.php');

if ($is_guest)
    alert_close('회원만 조회하실 수 있습니다.');

$g4['title'] = $member['mb_nick'].' 님의 마일리지 내역';
include_once(G4_PATH.'/head.sub.php');

$list = array();

$sql_common = " from {$g4['shop_mileage_table']} where mb_id = '".mysql_escape_string($member['mb_id'])."' ";
$sql_order = " order by ml_id desc ";

$sql = " select count(*) as cnt {$sql_common} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if (!$page) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

// 포인트소계
$sql = " select ml_point
            {$sql_common}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);
$sum_point1 = $sum_point2 = 0;
for($i=0; $row=sql_fetch_array($result); $i++) {
    if($row['ml_point'] >= 0) {
        $sum_point1 += $row['ml_point'];
    } else {
        $sum_point2 += $row['ml_point'];
    }
}
?>

<!-- 마일리지 내역 시작 { -->
<div id="point" class="new_win">
    <h1 id="new_win_title"><?php echo $g4['title'] ?></h1>

    <table class="basic_tbl">
    <thead>
    <tr>
        <th scope="col">일시</th>
        <th scope="col">내용</th>
        <th scope="col">지급마일리지</th>
        <th scope="col">사용마일리지</th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <th scope="row" colspan="2">소계</th>
        <td><?php echo number_format($sum_point1) ?></td>
        <td><?php echo number_format($sum_point2) ?></td>
    </tr>
    <tr>
        <th scope="row" colspan="2">보유마일리지</th>
        <td colspan="2"><?php echo number_format($member['mb_mileage']) ?></td>
    </tr>
    </tfoot>
    <tbody>
    <?php
    $sum_point1 = $sum_point2 = 0;

    $sql = " select *
                {$sql_common}
                {$sql_order}
                limit {$from_record}, {$rows} ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $point1 = $point2 = 0;
        if ($row['ml_point'] > 0) {
            $point1 = '+' .number_format($row['ml_point']);
            $sum_point1 += $row['ml_point'];
        } else {
            $point2 = number_format($row['ml_point']);
            $sum_point2 += $row['ml_point'];
        }

    ?>
    <tr>
        <td class="td_datetime"><?php echo $row['ml_datetime'] ?></td>
        <td><?php echo $row['ml_content'] ?></td>
        <td class="td_bignum"><?php echo $point1 ?></td>
        <td class="td_bignum"><?php echo $point2 ?></td>
    </tr>
    <?php
    }

    if ($i == 0)
        echo '<tr><td colspan="5" class="empty_table">자료가 없습니다.</td></tr>';
    else {
        if ($sum_point1 > 0)
            $sum_point1 = "+" . number_format($sum_point1);
        $sum_point2 = number_format($sum_point2);
    }
    ?>
    </tbody>
    </table>


    <?php echo get_paging(G4_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['PHP_SELF'].'?'.$qstr.'&amp;page='); ?>

    <div class="btn_win"><a href="javascript:;" onclick="window.close();">창닫기</a></div>
</div>
<!-- } 마일리지 내역 끝 -->

<?php
include_once(G4_PATH.'/tail.sub.php');
?>