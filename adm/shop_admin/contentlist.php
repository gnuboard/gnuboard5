<?
$sub_menu = '400700';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '내용관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$sql_common = " from {$g4['shop_content_table']} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "select * $sql_common order by co_id limit $from_record, {$config['cf_page_rows']} ";
$result = sql_query($sql);
?>

<section class="cbox">
    <h2>내용관리</h2>
    <p>
        <? if ($page > 1) {?><a href="<?=$_SERVER['PHP_SELF']?>">처음으로</a><? } ?>
        <span>전체 내용 <?=$total_count?>건</span>
    </p>
    <div id="btn_add">
        <a href="./contentform.php">내용 추가</a>
    </div>
    <table>
    <thead>
    <tr>
        <th scope="col">ID</th>
        <th scope="col">제목</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <? for ($i=0; $row=mysql_fetch_array($result); $i++) { ?>
    <tr>
        <td class="td_odrnum"><?=$row['co_id']?></td>
        <td><?=htmlspecialchars2($row['co_subject'])?></td>
        <td class="td_mng">
            <a href="<?=G4_SHOP_URL?>/content.php?co_id=<?=$row['co_id']?>"><img src="./img/icon_view.jpg" alt="<?=htmlspecialchars2($row['co_subject'])?> 보기"></a>
            <a href="./contentform.php?w=u&amp;co_id=<?=$row['co_id']?>"><img src="./img/icon_mod.jpg" alt="<?=htmlspecialchars2($row['co_subject'])?> 수정"></a>
            <a href="./contentformupdate.php?w=d&amp;co_id=<?=$row['co_id']?>" onclick="return delete_confirm();"><img src="./img/icon_del.jpg" alt="<?=htmlspecialchars2($row['co_subject'])?> 삭제"></a>
        </td>
    </tr>
    <?
    }
    if ($i == 0) {
        echo '<tr><td colspan="3" class="empty_table">자료가 한건도 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</section>

<?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page=");?>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
