<?
$sub_menu = "200800";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g4['title'] = '요일별 접속자집계';
include_once('./admin.head.php');
include_once('./visit.sub.php');

$colspan = 4;
$weekday = array ('월', '화', '수', '목', '금', '토', '일');

$sum_count = 0;
$sql = " select WEEKDAY(vs_date) as weekday_date, SUM(vs_count) as cnt
            from {$g4['visit_sum_table']}
            where vs_date between '{$fr_date}' and '{$to_date}'
            group by weekday_date
            order by weekday_date ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $arr[$row['weekday_date']] = $row['cnt'];

    $sum_count += $row['cnt'];
}
?>

<section class="cbox">
    <h2>요일별 접속자 수</h2>

    <table>
    <thead>
    <tr>
        <th scope="col">요일</th>
        <th scope="col">그래프</th>
        <th scope="col">접속자수</th>
        <th scope="col">비율(%)</th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <td colspan="2">합계</td>
        <td><strong><?=$sum_count?></strong></td>
        <td>100%</td>
    </tr>
    </tfoot>
    <tbody>
    <?
    $k = 0;
    if ($i) {
        for ($i=0; $i<7; $i++) {
            $count = (int)$arr[$i];

            $rate = ($count / $sum_count * 100);
            $s_rate = number_format($rate, 1);
    ?>

    <tr>
        <td class="td_category"><?=$weekday[$i]?></td>
        <td>
            <div class="visit_bar">
                <span style="width:<?=$s_rate?>%"></span>
            </div>
        </td>
        <td class="td_bignum"><?=$count?></td>
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
