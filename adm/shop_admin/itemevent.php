<?php
$sub_menu = '500300';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '이벤트관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$sql_common = " from {$g4['shop_event_table']} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$sql = "select * $sql_common order by ev_id desc ";
$result = sql_query($sql);
?>

<p>전체 이벤트 <?php echo $total_count ?>건</p>

<section class="cbox">
    <h2>이벤트 목록</h2>

    <div class="btn_add">
        <a href="./itemeventform.php">이벤트 추가</a>
    </div>

    <table>
    <thead>
    <tr>
        <th scope="col">이벤트번호</th>
        <th scope="col">제목</th>
        <th scope="col">연결상품</th>
        <th scope="col">사용</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=mysql_fetch_array($result); $i++) {

        $href = "";
        $sql = " select count(ev_id) as cnt from {$g4['shop_event_item_table']} where ev_id = '{$row['ev_id']}' ";
        $ev = sql_fetch($sql);
        if ($ev['cnt']) {
            $href = '<a href="javascript:;" onclick="itemeventwin('.$row['ev_id'].');">';
            $href_close = '</a>';
        }
        if ($row['ev_subject_strong']) $subject = '<strong>'.$row['ev_subject'].'</strong>';
        else $subject = $row['ev_subject'];
    ?>

    <tr>
        <td class="td_bignum"><?php echo $row['ev_id']; ?></td>
        <td><?php echo $subject; ?></td>
        <td class="td_num"><?php echo $href; ?><?php echo $ev['cnt']; ?><?php echo $href_close; ?></td>
        <td class="td_smallmng"><?php echo $row['ev_use'] ? '<span class="txt_true">예</span>' : '<span class="txt_false">아니오</span>'; ?></td>
        <td class="td_mng">
            <a href="<?php echo G4_SHOP_URL; ?>/event.php?ev_id=<?php echo $row['ev_id']; ?>">보기</a>
            <a href="./itemeventform.php?w=u&amp;ev_id=<?php echo $row['ev_id']; ?>">수정</a>
            <a href="./itemeventformupdate.php?w=d&amp;ev_id=<?php echo $row['ev_id']; ?>" onclick="return delete_confirm();">삭제</a>
        </td>
    </tr>

    <?php
    }

    if ($i == 0) {
        echo '<tr><td colspan="5" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</section>


<SCRIPT>
function itemeventwin(ev_id)
{
    window.open("./itemeventwin.php?ev_id="+ev_id, "itemeventwin", "left=10,top=10,width=500,height=600,scrollbars=1");
}
</SCRIPT>


<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
