<?
$sub_menu = "400720";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "새창관리";
include_once ("$g4[admin_path]/admin.head.php");

$sql_common = " from $g4[yc4_new_win_table] ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$sql = "select * $sql_common order by nw_id desc ";
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
<colgroup width=40>
<colgroup width=100>
<colgroup width=100>
<colgroup width=40>
<colgroup width=50>
<colgroup width=50>
<colgroup width=50>
<colgroup width=50>
<colgroup width=''>
<colgroup width=80>
<tr><td colspan=10 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
    <td>번호</td>
    <td>시작일시</td>
    <td>종료일시</td>
    <td>시간</td>
    <td>Left</td>
    <td>Top</td>
    <td>Height</td>
    <td>Width</td>
    <td>제목</td>
    <td><a href='./newwinform.php'><img src='<?=$g4[admin_path]?>/img/icon_insert.gif' border=0></a></td>
</tr>
<tr><td colspan=10 height=1 bgcolor=#CCCCCC></td></tr>

<?
for ($i=0; $row=mysql_fetch_array($result); $i++) 
{
    $s_mod = icon("수정", "./newwinform.php?w=u&nw_id=$row[nw_id]");
    $s_del = icon("삭제", "javascript:del('./newwinformupdate.php?w=d&nw_id=$row[nw_id]');");

    $list = $i%2;
    echo "
    <tr class='list$list center ht'>
        <td>$row[nw_id]</td>
        <td>".substr($row[nw_begin_time],2,14)."</td>
        <td>".substr($row[nw_end_time],2,14)."</td>
        <td>$row[nw_disable_hours]</td>
        <td>$row[nw_left]</td>
        <td>$row[nw_top]</td>
        <td>$row[nw_height]</td>
        <td>$row[nw_width]</td>
        <td align=left>$row[nw_subject]</td>
        <td>$s_mod $s_del</td>
    </tr>";
}

if ($i == 0) {
    echo "<tr><td colspan=10 align=center height=100 bgcolor=#ffffff><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
}
?>
<tr><td colspan=10 height=1 bgcolor=CCCCCC></td></tr>
</table>


<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
