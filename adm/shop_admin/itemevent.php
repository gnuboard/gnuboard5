<?
$sub_menu = "400630";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "이벤트관리";
include_once ("$g4[admin_path]/admin.head.php");

$sql_common = " from $g4[yc4_event_table] ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$sql = "select * $sql_common order by ev_id desc ";
$result = sql_query($sql);
?>

<table width=100%>
<tr>
    <td width=20%>&nbsp;</td>
    <td width=60% align=center>&nbsp;</td>
    <td width=20% align=right>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>


<table cellpadding=0 cellspacing=0 width=100% border=0>
<colgroup width=100>
<colgroup width=''>
<colgroup width=80>
<colgroup width=40>
<colgroup width=80>
<tr><td colspan=5 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
    <td>이벤트번호</td>
    <td>제목</td>
    <td>연결상품</td>
    <td>사용</td>
    <td><a href='./itemeventform.php'><img src='<?=$g4[admin_path]?>/img/icon_insert.gif' border=0 title='등록'></a></td>
</tr>
<tr><td colspan=5 height=1 bgcolor=#CCCCCC></td></tr>
<?
for ($i=0; $row=mysql_fetch_array($result); $i++) 
{
    $s_mod = icon("수정", "./itemeventform.php?w=u&ev_id=$row[ev_id]");
    $s_del = icon("삭제", "javascript:del('./itemeventformupdate.php?w=d&ev_id=$row[ev_id]');");
    $s_vie = icon("보기", "$g4[shop_path]/event.php?ev_id=$row[ev_id]");

    $href = "";
    $sql = " select count(ev_id) as cnt from $g4[yc4_event_item_table] where ev_id = '$row[ev_id]' ";
    $ev = sql_fetch($sql);
    if ($ev[cnt]) {
        $href = "<a href='javascript:;' onclick='itemeventwin($row[ev_id]);'>";
    }

    $list = $i%2;
    echo "
    <tr class='list$list center ht'>
        <td>$row[ev_id]</td>
        <td align=left>$row[ev_subject]</td>
        <td>$href<U>$ev[cnt]</U></a></td>
        <td>".($row[ev_use] ? "예" : "아니오")."</td>
        <td>$s_mod $s_del $s_vie</td>
    </tr><tr><td colspan=5 height=1 bgcolor=F5F5F5></td></tr>";
}

if ($i == 0) {
    echo "<tr><td colspan=5 align=center height=100 bgcolor=#ffffff><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
}
?>

<tr><td colspan=5 height=1 bgcolor=CCCCCC></td></tr>
</table>

<SCRIPT LANGUAGE="JavaScript">
function itemeventwin(ev_id)
{
    window.open("./itemeventwin.php?ev_id="+ev_id, "itemeventwin", "left=10,top=10,width=500,height=600,scrollbars=1");
}
</SCRIPT>


<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
