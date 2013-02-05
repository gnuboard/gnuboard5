<?
include_once('./_common.php');

if (!$member[mb_id])
    alert_close('회원만 조회하실 수 있습니다.');

$g4['title'] = $member[mb_nick].' 님의 포인트 내역';
include_once(G4_PATH.'/head.sub.php');

$list = array();

$sql_common = " from {$g4[point_table]} where mb_id = '".mysql_escape_string($member[mb_id])."' ";
$sql_order = " order by po_id desc ";

$sql = " select count(*) as cnt {$sql_common} ";
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if (!$page) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

// 포인트소계
$sql = " select po_point
            {$sql_common}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);
$sum_point1 = $sum_point2 = 0;
for($i=0; $row=sql_fetch_array($result); $i++) {
    if($row['po_point'] >= 0) {
        $sum_point1 += $row['po_point'];
    } else {
        $sum_point2 += $row['po_point'];
    }
}
?>

<div id="point" class="new_win">
    <h1><?=$g4['title']?></h1>

    <table class="basic_tbl">
    <caption>포인트 사용내역 목록</caption>
    <thead>
    <tr>
        <th scope="col">일시</th>
        <th scope="col">내용</th>
        <th scope="col">지급포인트</th>
        <th scope="col">사용포인트</th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <th scope="row" colspan="2">소계</td>
        <td><?=number_format($sum_point1)?></td>
        <td><?=number_format($sum_point2)?></td>
    </tr>
    <tr>
        <th scope="row" colspan="2">보유포인트</th>
        <td colspan="2"><?=number_format($member[mb_point])?></td>
    </tr>
    </tfoot>
    <tbody>
    <?
    $sum_point1 = $sum_point2 = 0;

    $sql = " select *
                {$sql_common}
                {$sql_order}
                limit {$from_record}, {$rows} ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $point1 = $point2 = 0;
        if ($row[po_point] > 0) {
            $point1 = '+' .number_format($row[po_point]);
            $sum_point1 += $row[po_point];
        } else {
            $point2 = number_format($row[po_point]);
            $sum_point2 += $row[po_point];
        }

    ?>
    <tr>
        <td class="td_datetime"><?=$row[po_datetime]?></td>
        <td><?=$row[po_content]?></td>
        <td class="td_bignum"><?=$point1?></td>
        <td class="td_bignum"><?=$point2?></td>
    </tr>
    <?
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


    <?=get_paging($config[cf_write_pages], $page, $total_page, $_SERVER[PHP_SELF].'?'.$qstr.'&amp;page=');?>

</div>

<script>
$(function() {
    $("#point").append("<div class=\"btn_win\"><a>창닫기</a></div>");

    $(".btn_win a").click(function() {
        window.close();
    });
});
</script>

<?
include_once(G4_PATH.'/tail.sub.php');
?>