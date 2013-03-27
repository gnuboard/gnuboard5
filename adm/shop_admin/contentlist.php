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
    #content_head{width:900px;height:35px;line-height:35px ;text-align:center}
    #content_head th{text-align:center}
    #content_fir{position:relative}
    #content_fir span{position:absolute;top:-12;right:5px}
</style>

<section class="cbox">
    <h2>내용관리</h2>
    <?=$pg_anchor?>
    <p id="content_fir">
        <a href='<?=$_SERVER['PHP_SELF']?>'>처음</a>
        <span>건수 : <? echo $total_count ?>&nbsp;</span>
    </p>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="gird_14">
        <col class="grid_1">
    </colgroup>
    <thead id="content_head">
    <tr>
        <th scope="row">ID</th>
        <th scope="row">제목</th>
        <th><a href="./contentform.php"><img src="<?=G4_ADMIN_URL?>/img/icon_insert.gif" alt="내용입력버튼"></a></th>
    </tr>
    </thead>
    <tbody>
        <?
        for ($i=0; $row=mysql_fetch_array($result); $i++) {
            $s_mod = icon("수정", "./contentform.php?w=u&co_id={$row['co_id']}");
            $s_del = icon("삭제", "javascript:del('./contentformupdate.php?w=d&co_id={$row['co_id']}')");
            $s_vie = icon("보기", G4_SHOP_URL."/content.php?co_id={$row['co_id']}");

            $list = $i%2;
            echo "
            <tr class='list$list ht'>
                <td align=center>{$row['co_id']}</td>
                <td>".htmlspecialchars2($row['co_subject'])."</td>
                <td>$s_mod $s_del $s_vie</td>
            </tr>";
        }

        if ($i == 0) {
            echo "<tr><td colspan=\"3\" align=\"center\" height="100" bgcolor=\"#ffffff\"><span class=\"point\">자료가 한건도 없습니다.</span></td></tr>\n";
        }
        ?>
    </tbody>
    </table>
    <div><?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&page=");?></div>
</section>


<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
