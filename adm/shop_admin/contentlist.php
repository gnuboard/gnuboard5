<?
$sub_menu = '400700';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '내용관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$sql_common = " from {$g4['yc4_content_table']} ";

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
<style type="text/css">
    #content_fir{position:relative}
    #content_fir span{position:absolute;top:-12;right:5px}
    .content_center{text-align:center}
</style>

<section class="cbox">
    <h2>내용관리</h2>
    <p id="content_fir">
        <a href="<?=$_SERVER['PHP_SELF']?>">처음</a>
        <span>건수 <? echo $total_count ?>&nbsp;</span>
    </p>
    <div id="btn_add">
        <a href="./contentform.php">내용관리추가</a>
    </div>
    <table>
    <colgroup>
        <col class="grid_2">
        <col class="gird_13">
        <col class="grid_3">
    </colgroup>
    <thead>
    <tr>
        <th scope="col">ID</th>
        <th scope="col">제목</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?
    for ($i=0; $row=mysql_fetch_array($result); $i++) {
        $s_mod = icon("수정", "./contentform.php?w=u&co_id={$row['co_id']}");
        $s_del = icon("삭제", "javascript:del('./contentformupdate.php?w=d&co_id={$row['co_id']}')");
        $s_vie = icon("보기", G4_SHOP_URL."/content.php?co_id={$row['co_id']}");

        $list = $i%2;
    ?>
    <tr class="list<?=$list?>">
        <td class="content_center"><?=$row['co_id']?></td>
        <td><?=htmlspecialchars2($row['co_subject'])?></td>
        <td class="content_center"><a href="./contentform.php?w=u&co_id=<?=$row['co_id']?>">수정</a> <a href="./contentformupdate.php?w=d&co_id=<?=$row['co_id']?>">삭제</a> <a href="<?=G4_SHOP_URL?>/content.php?co_id=<?=$row['co_id']?>">보기</a></td>
    </tr>
    <?
    }
    if ($i == 0) {
        echo "<tr><td colspan=\"3\" class=\"content_center\"><span class=\"point\">자료가 한건도 없습니다.</span></td></tr>\n";
    }
    ?>
    </tbody>
    </table>
    <div><?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&page=");?></div>
</section>


<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
