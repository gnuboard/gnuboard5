<?
$sub_menu = "200300";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$sql_common = " from $g4[mail_table] ";

// 테이블의 전체 레코드수만 얻음
$sql = " select COUNT(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$page = 1;

$sql = "select * $sql_common order by ma_id desc ";
$result = sql_query($sql);

$g4[title] = "회원메일발송";
include_once("./admin.head.php");

$colspan = 6;
?>

<table width=100%>
<tr>
    <td width=20%>&nbsp;</td>
    <td width=60% align=center>&nbsp;</td>
    <td width=20% align=right>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>


<table cellpadding=0 cellspacing=0 width=100%>
<tr><td colspan='<?=$colspan?>' class='line1'></td></tr>
<tr class='bgcol1 bold col1 ht center'>
    <td width=40>ID</td>
    <td width=''>제목</td>
    <td width=120>작성일시</td>
    <td width=50>테스트</td>
    <td width=50>보내기</td>
    <td width=80><a href='./mail_form.php'><img src='<?=$g4[admin_path]?>/img/icon_insert.gif' border=0></a></td>
</tr>
<tr><td colspan='<?=$colspan?>' class='line2'></td></tr>

<?
for ($i=0; $row=mysql_fetch_array($result); $i++) {
    $s_mod = icon("수정", "./mail_form.php?w=u&ma_id=$row[ma_id]");
    //$s_del = icon("삭제", "javascript:del('./mail_update.php?w=d&ma_id=$row[ma_id]');");
    $s_del = "<a href=\"javascript:post_delete('mail_update.php', '$row[ma_id]');\"><img src='img/icon_delete.gif' border=0 title='삭제' align='absmiddle'></a>";
    $s_vie = icon("보기", "./mail_preview.php?ma_id=$row[ma_id]", "_blank");

    $num = number_format($total_count - ($page - 1) * $config[cf_page_rows] - $i);

    $list = $i%2;
    echo "
    <tr class='list$list col1 ht center'>
        <td>$num</td>
        <td align=left>$row[ma_subject]</td>
        <td>$row[ma_time]</td>
        <td><a href='./mail_test.php?ma_id=$row[ma_id]'>테스트</a></td>
        <td><a href='./mail_select_form.php?ma_id=$row[ma_id]'>보내기</a></td>
        <td>$s_mod $s_del $s_vie</td>
    </tr>";
}

if (!$i)
    echo "<tr><td colspan='$colspan' height=100 align=center bgcolor='#FFFFFF'>자료가 없습니다.</td></tr>";
?>
<tr><td colspan='<?=$colspan?>' class='line2'></td></tr>
</table>

<script>
// POST 방식으로 삭제
function post_delete(action_url, val)
{
	var f = document.fpost;

	if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) {
        f.ma_id.value = val;
		f.action      = action_url;
		f.submit();
	}
}
</script>

<form name='fpost' method='post'>
<input type='hidden' name='sst'  value='<?=$sst?>'>
<input type='hidden' name='sod'  value='<?=$sod?>'>
<input type='hidden' name='sfl'  value='<?=$sfl?>'>
<input type='hidden' name='stx'  value='<?=$stx?>'>
<input type='hidden' name='page' value='<?=$page?>'>
<input type='hidden' name='w'    value='d'>
<input type='hidden' name='ma_id'>
</form>

<?
include_once ("./admin.tail.php");
?>