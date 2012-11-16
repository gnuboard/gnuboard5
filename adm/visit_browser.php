<?
$sub_menu = "200800";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g4['title'] = '브라우저별 접속자집계';
include_once('./admin.head.php');
include_once('./visit.sub.php');

$colspan = 5;

$max = 0;
$sum_count = 0;
$sql = " select * from {$g4['visit_table']}
            where vi_date between '{$fr_date}' and '{$to_date}' ";
$result = sql_query($sql);
while ($row=sql_fetch_array($result)) {
    $s = get_brow($row['vi_agent']);

    $arr[$s]++;

    if ($arr[$s] > $max) $max = $arr[$s];

    $sum_count++;
}
?>
<table>
<caption>브라우저별 접속자 수</caption>
<thead>
<tr>
    <th scope="col">순위</th>
    <th scope="col">브라우저</th>
    <th scope="col">접속자수</th>
    <th scope="col">비율(%)</th>
    <th scope="col">그래프</th>
</tr>
</thead>
<tfoot>
<tr>
    <td colspan="2">합계</td>
    <td><?=$sum_count?></td>
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
    arsort($arr);
    foreach ($arr as $key=>$value) {
        $count = $arr[$key];
        if ($save_count != $count) {
            $i++;
            $no = $i;
            $save_count = $count;
        } else {
            $no = "";
        }

        $rate = ($count / $sum_count * 100);
        $s_rate = number_format($rate, 1);

        $bar = (int)($count / $max * 100);
?>

<tr>
    <td><?=$no?></td>
    <td><?=$key?></td>
    <td><?=$count?></td>
    <td><?=$s_rate?></td>
    <td>
        <div class="visit_bar">
            <span style="width:<?=$bar?>"></span>
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
