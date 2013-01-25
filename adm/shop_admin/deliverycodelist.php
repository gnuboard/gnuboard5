<?
$sub_menu = "400740";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "배송회사관리";
include_once ("$g4[admin_path]/admin.head.php");

$sql_common = " from $g4[yc4_delivery_table] ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$sql = "select * $sql_common order by dl_order , dl_id desc ";
$result = sql_query($sql);
?>

<table width=100%>
    <tr>
        <td width=20%>&nbsp;</td>
        <td width=60% align=center>&nbsp;</td>
        <td width=20% align=right>건수 : <? echo $total_count ?>&nbsp;</td>
    </tr>
</table>


<table cellpadding=0 cellspacing=0 width=100%>
<colgroup width=100>
<colgroup width=''>
<colgroup width=200>
<colgroup width=100>
<colgroup width=80>
<tr><td colspan=5 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
    <td>ID</td>
    <td>배송회사명</td>
    <td>고객센터</td>
    <td>순서</td>
    <td><?=icon("입력", "./deliverycodeform.php");?></td>
</tr>
<tr><td colspan=5 height=1 bgcolor=#CCCCCC></td></tr>

<?
for ($i=0; $row=mysql_fetch_array($result); $i++)
{
    $s_mod = icon("수정", "./deliverycodeform.php?w=u&dl_id=$row[dl_id]");
    $s_del = icon("삭제", "javascript:del('./deliverycodeformupdate.php?w=d&dl_id=$row[dl_id]');");
    $s_vie = icon("보기", "$row[dl_url]", $target="_blank");

    if ($i) 
        echo "<tr><td colspan=5 height=1 bgcolor=F1F1F1></td></tr>";

    $list = $i%2;
    echo "
    <tr class='list$list center ht'>
        <td align=center>$row[dl_id]</td>
        <td>". stripslashes($row[dl_company]) . "</td>
        <td align=center>$row[dl_tel]</td>
        <td align=center>$row[dl_order]</td>
        <td align=center>$s_mod $s_del $s_vie</td>
    </tr>";
}

if ($i == 0)
    echo "<tr><td colspan=5 align=center height=100 bgcolor=#ffffff><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
?>
<tr><td colspan=5 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
