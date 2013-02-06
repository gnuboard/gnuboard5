<?
$sub_menu = "400700";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "내용관리";
include_once(G4_ADMIN_PATH."/admin.head.php");

$sql_common = " from $g4[yc4_content_table] ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = "select * $sql_common order by co_id limit $from_record, $config[cf_page_rows] ";
$result = sql_query($sql);
?>

<table>
<tr>
    <td><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
    <td>&nbsp;</td>
    <td>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>


<table>
<colgroup>
<colgroup>
<colgroup>

<tr>
    <td>ID</td>
    <td>제목</td>
    <td><a href='./contentform.php'><img src='<?=$g4[admin_path]?>/img/icon_insert.gif' border=0></a></td>
</tr>

<?
for ($i=0; $row=mysql_fetch_array($result); $i++) {
    $s_mod = icon("수정", "./contentform.php?w=u&co_id=$row[co_id]");
    $s_del = icon("삭제", "javascript:del('./contentformupdate.php?w=d&co_id=$row[co_id]')");
    $s_vie = icon("보기", "$g4[shop_path]/content.php?co_id=$row[co_id]");

    $list = $i%2;
    echo "
    <tr class='list$list ht'>
        <td>$row[co_id]</td>
        <td>".htmlspecialchars2($row[co_subject])."</td>
        <td>$s_mod $s_del $s_vie</td>
    </tr>";
}

if ($i == 0) {
    echo "<tr><td colspan=3 height=100 bgcolor=#ffffff><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
}
?>

</table>

<table>
<tr bgcolor=#ffffff>
    <td></td>
    <td><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</table>


<?
include_once(G4_ADMIN_PATH."/admin.tail.php");
?>
