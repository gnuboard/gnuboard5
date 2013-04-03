<?
$sub_menu = '400740';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '배송회사관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$sql_common = " from {$g4['yc4_delivery_table']} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$sql = "select * $sql_common order by dl_order , dl_id desc ";
$result = sql_query($sql);
?>
<style type="text/css">
    .deliverycode_center{text-align:center}
</style>
<section class="cbox">
    <h2>배송회사관리</h2>
    <p>건수 <? echo $total_count ?></p>
     <div id="btn_add">
        <a href="./deliverycodeform.php">배송회사추가</a>
    </div>
    <table>
    <colgroup>
        <col class="grid_1">
        <col class="grid_11">
        <col class="gird_2">
        <col class="grid_1">
        <col class="grid_3">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">ID</th>
        <th scope="col">배송회사명</th>
        <th scope="col">고객센터</th>
        <th scope="col">순서</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?
    for ($i=0; $row=mysql_fetch_array($result); $i++)
    {
        $s_mod = icon("수정", "./deliverycodeform.php?w=u&dl_id={$row['dl_id']}");
        $s_del = icon("삭제", "javascript:del('./deliverycodeformupdate.php?w=d&dl_id={$row['dl_id']}');");
        $s_vie = icon("보기", $row['dl_url'], $target="_blank");

        /*if ($i)
            echo "<tr><td colspan=\"5\"></td></tr>"; 줄 없앰 김혜련 2013-04-03*/

        $list = $i%2;
        ?>
        <tr>
            <td class="deliverycode_center"><?=$row['dl_id']?></td>
            <td><?=stripslashes($row['dl_company'])?></td>
            <td class="deliverycode_center"><?=$row['dl_tel']?></td>
            <td class="deliverycode_center"><?=$row['dl_order']?></td>
            <td class="deliverycode_center"><a href="./deliverycodeform.php?w=u&dl_id=<?=$row['dl_id']?>">수정</a> <a href="./deliverycodeformupdate.php?w=d&dl_id=<?=$row['dl_id']?>">삭제</a> <a href="<?=$row['dl_url']?>" target="_blank">보기</a></td>
        </tr>
        <?
    }

    if ($i == 0)
        echo "<tr><td colspan=\"5\" class=\"deliverycode_center\"><span>자료가 한건도 없습니다.</span></td></tr>\n";
    ?>
    </tbody>
    </table>
</section>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
