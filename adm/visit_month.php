<?
$sub_menu = "200800";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g4['title'] = '월별 접속자집계';
include_once('./admin.head.php');
include_once('./visit.sub.php');

$colspan = 4;

$max = 0;
$sum_count = 0;
$sql = " select SUBSTRING(vs_date,1,7) as vs_month, SUM(vs_count) as cnt
            from {$g4['visit_sum_table']}
            where vs_date between '{$fr_date}' and '{$to_date}'
            group by vs_month
            order by vs_month desc ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $arr[$row['vs_month']] = $row[cnt];

    if ($row[cnt] > $max) $max = $row[cnt];

    $sum_count += $row[cnt];
}
?>

<table>
<caption>월별 접속자 수</caption>
<thead>
<tr>
    <th scope="col">년-월</th>
    <th scope="col">접속자수</th>
    <th scope="col">비율(%)</th>
    <th scope="col">그래프</th>
</tr>
</thead>
<tfoot>
<tr>
    <td>합계</td>
    <td><?=number_format($sum_count)?></td>
    <td colspan="2"></td>
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

        $bar = (int)($count / $max * 100);
?>

<tr>
    <td><a href="./visit_date.php?fr_date=<?=$key?>-01&amp;to_date=<?=$key?>-31"><?=$key?></a></td>
    <td><?=number_format($value)?></td>
    <td><?=$s_rate?></td>
    <td>
        <div class="visit_graph">
            <span style="width:<?=$bar?>%"></span>
        </div>
    </td>
</tr>

<?
    }


} else {
    echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
}
?>

</tbody>
</table>

<?
include_once('./admin.tail.php');
?>
