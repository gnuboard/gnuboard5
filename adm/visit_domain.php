<?
$sub_menu = "200800";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g4['title'] = '도메인별 접속자집계';
include_once('./admin.head.php');
include_once('./visit.sub.php');

$colspan = 5;

$max = 0;
$sum_count = 0;
$sql = " select * from {$g4['visit_table']}
            where vi_date between '{$fr_date}' and '{$to_date}' ";
$result = sql_query($sql);
while ($row=sql_fetch_array($result)) {
    $str = $row['vi_referer'];
    preg_match("/^http[s]*:\/\/([\.\-\_0-9a-zA-Z]*)\//", $str, $match);
    $s = $match[1];
    $s = preg_replace("/^(www\.|search\.|dirsearch\.|dir\.search\.|dir\.|kr\.search\.|myhome\.)(.*)/", "\\2", $s);
    $arr[$s]++;

    if ($arr[$s] > $max) $max = $arr[$s];

    $sum_count++;
}
?>

<section class="cbox">
    <h2>도메인별 접속자 수</h2>

    <table>
    <thead>
    <tr>
        <th scope="col">순위</th>
        <th scope="col">접속 도메인</th>
        <th scope="col">그래프</th>
        <th scope="col">접속자수</th>
        <th scope="col">비율(%)</th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <td colspan="3">합계</td>
        <td><strong><?=$sum_count?></strong></td>
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
        arsort($arr);
        foreach ($arr as $key=>$value) {
            $count = $arr[$key];
            if ($save_count != $count) {
                $i++;
                $no = $i;
                $save_count = $count;
            } else {
                $no = '';
            }

            if (!$key) {
                $link = '';
                $link2 = '';
                $key = '직접';
            } else {
                $link = '<a href="./visit_list.php?$qstr&amp;domain='.$key.'">';
                $link2 = '</a>';
            }

            $rate = ($count / $sum_count * 100);
            $s_rate = number_format($rate, 1);
    ?>
    <tr>
        <td class="td_num"><?=$no?></td>
        <td class="td_category"><?=$link?><?=$key?><?=$link2?></td>
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
