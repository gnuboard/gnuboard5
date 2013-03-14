<?
$sub_menu = "200800";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g4['title'] = '연도별 접속자집계';
include_once('./admin.head.php');
include_once('./visit.sub.php');

$colspan = 4;

$max = 0;
$sum_count = 0;
$sql = " select SUBSTRING(vs_date,1,4) as vs_year, SUM(vs_count) as cnt
            from {$g4['visit_sum_table']}
            where vs_date between '{$fr_date}' and '{$to_date}'
            group by vs_year
            order by vs_year desc ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $arr[$row['vs_year']] = $row['cnt'];

    if ($row['cnt'] > $max) $max = $row['cnt'];

    $sum_count += $row['cnt'];
}
?>

<section class="cbox">
    <h2>연간 접속자 수</h2>

    <table>
    <thead>
    <tr>
        <th scope="col">년</th>
        <th scope="col">그래프</th>
        <th scope="col">접속자수</th>
        <th scope="col">비율(%)</th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <td colspan="2">합계</td>
        <td><strong><?=number_format($sum_count)?></strong></td>
        <td>100%</td>
    </tr>
    </tfoot>
    <tbody>
    <?
    $i = 0;
    $k = 0;
    $save_count = -1;
    $tot_count = 0;
    if (count($arr)) {
        foreach ($arr as $key=>$value) {
            $count = $value;

            $rate = ($count / $sum_count * 100);
            $s_rate = number_format($rate, 1);
    ?>

    <tr>
        <td class="td_category"><a href="./visit_month.php?fr_date=<?=$key?>-01-01&amp;to_date=<?=$key?>-12-31"><?=$key?></a></td>
        <td>
            <div class="visit_bar">
                <span style="width:<?=$s_rate?>%"></span>
            </div>
        </td>
        <td class="td_bignum"><?=number_format($value)?></td>
        <td class="td_num"><?=$s_rate?></td>
    </tr>

    <?
        }
    } else {
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</section>

<?
include_once('./admin.tail.php');
?>
