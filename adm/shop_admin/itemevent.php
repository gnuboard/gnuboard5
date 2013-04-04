<?
$sub_menu = '400630';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '이벤트관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$sql_common = " from {$g4['yc4_event_table']} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$sql = "select * $sql_common order by ev_id desc ";
$result = sql_query($sql);
?>
<style type="text/css">
.itemevent_center{text-align:center}
</style>

<section class="cbox">
    <h2>이벤트관리</h2>
    <p>건수 <? echo $total_count ?></p>
    <div id="btn_add">
        <a href="./itemeventform.php">이벤트관리추가</a>
    </div>
    <table>
    <colgroup>
        <col class="grid_2">
        <col class="gird_10">
        <col class="grid_2">
        <col class="grid_1">
        <col class="grid_3">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">이벤트 번호</th>
        <th scope="col">제목</th>
        <th scope="col">연결 상품</th>
        <th scope="col">사용</th>
        <th scope="col">구분</th>
    </tr>
    </thead>
    <tbody>
    <?
    for ($i=0; $row=mysql_fetch_array($result); $i++)
    {
        $s_mod = icon("수정", "./itemeventform.php?w=u&ev_id={$row['ev_id']}");
        $s_del = icon("삭제", "javascript:del('./itemeventformupdate.php?w=d&ev_id={$row['ev_id']}');");
        $s_vie = icon("보기", G4_SHOP_URL."/event.php?ev_id={$row['ev_id']}");

        $href = "";
        $sql = " select count(ev_id) as cnt from {$g4['yc4_event_item_table']} where ev_id = '{$row['ev_id']}' ";
        $ev = sql_fetch($sql);
        if ($ev[cnt]) {
            $href = "<a href='javascript:;' onclick='itemeventwin({$row['ev_id']});'>";
        }

        $list = $i%2;
        ?>
        <tr>
            <td class="itemevent_center"><?=$row['ev_id']?></td>
            <td><?=$row['ev_subject']?></td>
            <td class="itemevent_center"><?=$href?><?=$ev['cnt']?></td>
            <td class="itemevent_center"><?=$row['ev_use'] ? "예" : "아니오"?></td>
            <td class="itemevent_center"><a href="./itemeventform.php?w=u&ev_id=<?=$row['ev_id']?>">수정</a> <a href="./itemeventformupdate.php?w=d&ev_id=<?=$row['ev_id']?>">삭제</a> <a href="<?=G4_SHOP_URL?>/event.php?ev_id=<?=$row['ev_id']?>">보기</a></td>
        </tr>
        <?
    }

    if ($i == 0) {
        echo '<tr><td colspan="5" class="itemevent_center"><span>자료가 한건도 없습니다.</span></td></tr>PHP_EOL';
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


<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
