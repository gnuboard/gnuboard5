<?php
$sub_menu = "200800";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$fr_date = isset($_REQUEST['fr_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['fr_date']) : G5_TIME_YMD;
$to_date = isset($_REQUEST['to_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['to_date']) : G5_TIME_YMD;

$g5['title'] = '일별 접속자집계';
include_once('./visit.sub.php');

$colspan = 4;

$max = 0;
$sum_count = 0;
$arr = array();

$sql = " select vs_date, vs_count as cnt
            from {$g5['visit_sum_table']}
            where vs_date between '{$fr_date}' and '{$to_date}'
            order by vs_date desc ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $arr[$row['vs_date']] = $row['cnt'];

    if ($row['cnt'] > $max) $max = $row['cnt'];

    $sum_count += $row['cnt'];
}
?>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">년-월-일</th>
        <th scope="col">그래프</th>
        <th scope="col">접속자수</th>
        <th scope="col">비율(%)</th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <td colspan="2">합계</td>
        <td><strong><?php echo number_format($sum_count) ?></strong></td>
        <td>100%</td>
    </tr>
    </tfoot>
    <tbody>
    <?php
    $i = 0;
    $k = 0;
    $save_count = -1;
    $tot_count = 0;
    if (count($arr)) {
        foreach ($arr as $key=>$value) {
            $count = $value;

            $rate = ($count / $sum_count * 100);
            $s_rate = number_format($rate, 1);

            $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>">
        <td class="td_category"><a href="./visit_list.php?fr_date=<?php echo $key ?>&amp;to_date=<?php echo $key ?>"><?php echo $key ?></a></td>
        <td>
            <div class="visit_bar">
                <span style="width:<?php echo $s_rate ?>%"></span>
            </div>
        </td>
        <td class="td_num_c3"><?php echo number_format($value) ?></td>
        <td class="td_num"><?php echo $s_rate ?></td>
    </tr>

    <?php
        $i++;
        }
    } else {
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<?php
include_once('./admin.tail.php');