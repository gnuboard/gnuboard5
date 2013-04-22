<?
$sub_menu = '400740';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '배송업체관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$sql_common = " from {$g4['shop_delivery_table']} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$sql = "select * $sql_common order by dl_order , dl_id desc ";
$result = sql_query($sql);
?>

<section class="cbox">

    <h2>배송업체 목록</h2>
    <p>등록된 배송업체 <?=$total_count ?>곳</p>

     <div id="btn_add">
        <a href="./deliverycodeform.php">배송회사추가</a>
    </div>

    <table>
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
    <? for ($i=0; $row=mysql_fetch_array($result); $i++) { ?>
    <tr>
        <td class="td_num"><?=$row['dl_id']?></td>
        <td><?=stripslashes($row['dl_company'])?></td>
        <td class="td_bignum"><?=$row['dl_tel']?></td>
        <td class="td_num"><?=$row['dl_order']?></td>
        <td class="td_mng">
            <a href="<?=$row['dl_url']?>" target="_blank">홈페이지</a>
            <a href="./deliverycodeform.php?w=u&amp;dl_id=<?=$row['dl_id']?>"><img src="./img/icon_mod.jpg" alt="<?=stripslashes($row['dl_company'])?> 수정"></a>
            <a href="javascript:del('./deliverycodeformupdate.php?w=d&amp;dl_id=<?=$row['dl_id']?>');"><img src="./img/icon_del.jpg" alt="<?=stripslashes($row['dl_company'])?> 삭제"></a>
        </td>
    </tr>
    <?
    }
    if ($i == 0)
        echo '<tr><td colspan="5" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>

</section>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
