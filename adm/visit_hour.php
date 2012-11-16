<?
$sub_menu = "200800";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g4['title'] = '시간별 접속자집계';
include_once('./admin.head.php');
include_once('./visit.sub.php');

$colspan = 4;

$max = 0;
$sum_count = 0;
$sql = " select SUBSTRING(vi_time,1,2) as vi_hour, count(vi_id) as cnt
            from {$g4['visit_table']}
            where vi_date between '{$fr_date}' and '{$to_date}'
            group by vi_hour
            order by vi_hour ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $arr[$row['vi_hour']] = $row[cnt];

    if ($row[cnt] > $max) $max = $row[cnt];

    $sum_count += $row[cnt];
}
?>

<table>
<caption>시간대별 접속자 수</caption>
<thead>
<tr>
    <th scope="col">시간</th>
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
$k = 0;
if ($i) {
    for ($i=0; $i<24; $i++) {
        $hour = sprintf("%02d", $i);
        $count = (int)$arr[$hour];

        $rate = ($count / $sum_count * 100);
        $s_rate = number_format($rate, 1);

        $bar = (int)($count / $max * 100);
?>
<tr>
    <td><?=$hour?></td>
    <td><?=number_format($count)?></td>
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
