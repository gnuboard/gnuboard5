<?php
$sub_menu = '400720';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '팝업레이어 관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$sql_common = " from {$g4['shop_new_win_table']} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$sql = "select * $sql_common order by nw_id desc ";
$result = sql_query($sql);
?>

<section class="cbox">
    <h2>팝업레이어 관리</h2>
    <p>추가된 팝업레이어 <?php echo $total_count; ?>건</p>
    <div class="btn_add">
        <a href="./newwinform.php">새창관리추가</a>
    </div>
    <table id="spop_list">
    <thead>
    <tr>
        <th scope="col">번호</th>
        <th scope="col">제목</th>
        <th scope="col">시작일시</th>
        <th scope="col">종료일시</th>
        <th scope="col">시간</th>
        <th scope="col">Left</th>
        <th scope="col">Top</th>
        <th scope="col">Width</th>
        <th scope="col">Height</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php for ($i=0; $row=mysql_fetch_array($result); $i++) { ?>
    <tr>
        <td class="td_num"><?php echo $row['nw_id']; ?></td>
        <td><?php echo $row['nw_subject']; ?></td>
        <td class="td_time"><?php echo substr($row['nw_begin_time'],2,14); ?></td>
        <td class="td_time"><?php echo substr($row['nw_end_time'],2,14); ?></td>
        <td><?php echo $row['nw_disable_hours']; ?>시간</td>
        <td><?php echo $row['nw_left']; ?>px</td>
        <td><?php echo $row['nw_top']; ?>px</td>
        <td><?php echo $row['nw_width']; ?>px</td>
        <td><?php echo $row['nw_height']; ?>px</td>
        <td class="td_smallmng">
            <a href="./newwinform.php?w=u&amp;nw_id=<?php echo $row['nw_id']; ?>" class="sel_a"><span class="sound_only"><?php echo $row['nw_subject']; ?> </span>수정</a>
            <a href="./newwinformupdate.php?w=d&amp;nw_id=<?php echo $row['nw_id']; ?>" class="sel_a" onclick="return delete_confirm();"><span class="sound_only"><?php echo $row['nw_subject']; ?> </span>삭제</a>
        </td>
    </tr>
    <?php
    }

    if ($i == 0) {
        echo '<tr><td colspan="10" class="empty_table">자료가 한건도 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</section>


<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
