<?
$sub_menu = "300300";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "다이얼로그관리";
include_once ("$g4[admin_path]/admin.head.php");

$sql_common = " from $g4[dialog_table] ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql, false);
if (!$row) {
    sql_query("
    CREATE TABLE IF NOT EXISTS `$g4[dialog_table]` (
      `di_id` int(11) NOT NULL auto_increment,
      `di_ui_theme` varchar(255) NOT NULL,
      `di_begin_time` datetime NOT NULL,
      `di_end_time` datetime NOT NULL,
      `di_subject` varchar(255) NOT NULL,
      `di_content` text NOT NULL,
      `di_speeds` int(11) NOT NULL,
      `di_position` varchar(255) NOT NULL,
      `di_draggable` tinyint(4) NOT NULL,
      `di_width` smallint(6) NOT NULL,
      `di_height` smallint(6) NOT NULL,
      `di_modal` tinyint(4) NOT NULL,
      `di_resizable` tinyint(4) NOT NULL,
      `di_disable_hours` tinyint(4) NOT NULL,
      `di_show` varchar(255) NOT NULL,
      `di_hide` varchar(255) NOT NULL,
      `di_escape` tinyint(4) NOT NULL,
      `di_zindex` int(11) NOT NULL,
      PRIMARY KEY  (`di_id`)
    )");
}
$total_count = $row[cnt];

$sql = "select * $sql_common order by di_id desc ";
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
<colgroup width=100>
<tr><td colspan=10 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
    <td>번호</td>
    <td>시작일시</td>
    <td>종료일시</td>
    <td>modal</td>
    <td>ESC</td>
    <td>위치</td>
    <td>Height</td>
    <td>Width</td>
    <td>제목</td>
    <td><a href='./dialog_form.php'><img src='<?=$g4[admin_path]?>/img/icon_insert.gif' border=0></a></td>
</tr>
<tr><td colspan=10 height=1 bgcolor=#CCCCCC></td></tr>

<?
for ($i=0; $row=mysql_fetch_array($result); $i++) 
{
    $s_mod = icon("수정", "./dialog_form.php?w=u&di_id=$row[di_id]");
    $s_del = icon("삭제", "javascript:del('./dialog_form_update.php?w=d&di_id=$row[di_id]');");
    $s_vie = icon("보기", "./dialog_view.php?di_id=$row[di_id]");

    $list = $i%2;
    echo "
    <tr class='list$list center ht'>
        <td>$row[di_id]</td>
        <td>".substr($row['di_begin_time'],2,14)."</td>
        <td>".substr($row['di_end_time'],2,14)."</td>
        <td>$row[di_modal]</td>
        <td>$row[di_escape]</td>
        <td align=left>$row[di_position]</td>
        <td>$row[di_height]</td>
        <td>$row[di_width]</td>
        <td align=left>$row[di_subject]</td>
        <td>$s_mod $s_del $s_vie</td>
    </tr>";
}

if ($i == 0) {
    echo "<tr><td colspan=10 align=center height=100 bgcolor=#ffffff><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
}
?>
<tr><td colspan=10 height=1 bgcolor=CCCCCC></td></tr>
</table>

<p>* 같은 페이지에 다이얼로그 창이 2개 이상 뜨는 경우 나중에 설정한 테마가 적용됩니다.</p>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
