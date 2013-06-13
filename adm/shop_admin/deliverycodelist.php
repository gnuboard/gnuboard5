<?php
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
    <p>등록된 배송업체 <?php echo $total_count; ?>곳</p>

     <div class="btn_add">
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
    <?php for ($i=0; $row=mysql_fetch_array($result); $i++) { ?>
    <tr>
        <td class="td_num"><?php echo $row['dl_id']; ?></td>
        <td><?php echo stripslashes($row['dl_company']); ?></td>
        <td class="td_bignum"><?php echo $row['dl_tel']; ?></td>
        <td class="td_num"><?php echo $row['dl_order']; ?></td>
        <td class="td_smallmng">
            <a href="<?php echo $row['dl_url']; ?>" target="_blank"><span class="sound_only"><?php echo stripslashes($row['dl_company']); ?> </span>홈페이지</a>
            <a href="./deliverycodeform.php?w=u&amp;dl_id=<?php echo $row['dl_id']; ?>"><span class="sound_only"><?php echo stripslashes($row['dl_company']); ?> </span>수정</a>
            <a href="./deliverycodeformupdate.php?w=d&amp;dl_id=<?php echo $row['dl_id']; ?>" onclick="return delete_confirm();"><span class="sound_only"><?php echo stripslashes($row['dl_company']); ?> </span>삭제</a>
        </td>
    </tr>
    <?php
    }
    if ($i == 0)
        echo '<tr><td colspan="5" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>

</section>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
