<?
$sub_menu = '400720';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '새창관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$sql_common = " from {$g4['yc4_new_win_table']} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$sql = "select * $sql_common order by nw_id desc ";
$result = sql_query($sql);
?>

<style type="text/css">
    .newwin_center{text-align:center}
</style>

<section class="cbox">
    <h2>새창관리</h2>
    <p>건수 <? echo $total_count ?></p>
    <div id="btn_add">
        <a href="./newwinform.php">새창관리추가</a>
    </div>
    <table>
    <colgroup>
        <col class="grid_1">
        <col class="grid_2">
        <col class="grid_2">
        <col class="grid_1">
        <col class="grid_1">
        <col class="grid_1">
        <col class="grid_1">
        <col class="grid_1">
        <col class="grid_6">
        <col class="grid_2">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">번호</th>
        <th scope="col">시작일시</th>
        <th scope="col">종료일시</th>
        <th scope="col">시간</th>
        <th scope="col">Left</th>
        <th scope="col">Top</th>
        <th scope="col">Height</th>
        <th scope="col">Width</th>
        <th scope="col">제목</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?
    for ($i=0; $row=mysql_fetch_array($result); $i++)
    {
        $s_mod = icon("수정", "./newwinform.php?w=u&nw_id={$row['nw_id']}");
        $s_del = icon("삭제", "javascript:del('./newwinformupdate.php?w=d&nw_id={$row['nw_id']}');");

        $list = $i%2;
        ?>
        <tr>
            <td class="newwin_center"><?=$row['nw_id']?></td>
            <td><?=substr($row['nw_begin_time'],2,14)?></td>
            <td><?=substr($row['nw_end_time'],2,14)?></td>
            <td class="newwin_center"><?=$row['nw_disable_hours']?></td>
            <td class="newwin_center"><?=$row['nw_left']?></td>
            <td class="newwin_center"><?=$row['nw_top']?></td>
            <td class="newwin_center"><?=$row['nw_height']?></td>
            <td class="newwin_center"><?=$row['nw_width']?></td>
            <td><?=$row['nw_subject']?></td>
            <td class="newwin_center"><a href="./newwinform.php?w=u&nw_id=<?=$row['nw_id']?>">수정</a> <a href="./newwinformupdate.php?w=d&nw_id=<?=$row['nw_id']?>">삭제</a></td>
        </tr>
        <?
    }

    if ($i == 0) {
        echo "<tr><td colspan=\"10\" class=\"newwin_center\"><span>자료가 한건도 없습니다.</span></td></tr>\n";
    }
    ?>
    </tbody>
    </table>
</section>


<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
