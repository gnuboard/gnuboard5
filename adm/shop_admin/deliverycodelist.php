<?
$sub_menu = "400740";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "배송회사관리";
include_once(G4_ADMIN_PATH."/admin.head.php");

$sql_common = " from $g4[yc4_delivery_table] ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$sql = "select * $sql_common order by dl_order , dl_id desc ";
$result = sql_query($sql);
?>

<table>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>건수 : <? echo $total_count ?>&nbsp;</td>
    </tr>
</table>


<table>
<colgroup width=100>
<colgroup>
<colgroup width=200>
<colgroup width=100>
<colgroup>
<tr><td colspan=5 height=2 bgcolor=#0E87F9></td></tr>
<tr>
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
        <td>$row[dl_id]</td>
        <td>". stripslashes($row[dl_company]) . "</td>
        <td>$row[dl_tel]</td>
        <td>$row[dl_order]</td>
        <td>$s_mod $s_del $s_vie</td>
    </tr>";
}

if ($i == 0)
    echo "<tr><td colspan=5 height=100 bgcolor=#ffffff><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
?>
<tr><td colspan=5 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<?
include_once(G4_ADMIN_PATH."/admin.tail.php");
?>
