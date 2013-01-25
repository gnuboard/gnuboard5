<?
$sub_menu = "400740";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

$html_title = "배송회사";
if ($w == "u") {
    $html_title .= " 수정";
    $readonly = " readonly";

    $sql = " select * from $g4[yc4_delivery_table] where dl_id = '$dl_id' ";
    $dl = sql_fetch($sql);
    if (!$dl[dl_id]) alert("등록된 자료가 없습니다.");
}
else
{
    $html_title .= " 입력";
    $dl[dl_url] = "http://";
}

$g4[title] = $html_title;
include_once ("$g4[admin_path]/admin.head.php");
?>

<?=subtitle($html_title);?>

<table cellpadding=0 cellspacing=0 width=100%>
<form name=fdeliverycodeform method=post action='./deliverycodeformupdate.php'>
<input type=hidden name=w     value='<? echo $w ?>'>
<input type=hidden name=dl_id value='<? echo $dl_id ?>'>
<colgroup width=15%></colgroup>
<colgroup width=85% bgcolor=#ffffff></colgroup>
<tr><td colspan=2 height=2 bgcolor=#0E87F9></td></tr>
<tr class=ht>
    <td>배송회사명</td>
    <td><input type=text class=ed name=dl_company value='<? echo stripslashes($dl[dl_company]) ?>' required itemname="배송회사명"></td>
</tr>
<tr class=ht>
    <td>화물추적 URL</td>
    <td><input type=text class=ed name=dl_url value='<? echo stripslashes($dl[dl_url]) ?>' style='width:98%;'></td>
</tr>
<tr class=ht>
    <td>고객센터 전화</td>
    <td><input type=text class=ed name=dl_tel value='<? echo stripslashes($dl[dl_tel]) ?>'></td>
</tr>
<tr class=ht>
    <td>출력 순서</td>
    <td>
        <?=order_select("dl_order", $dl[dl_order])?>
        <?=help("셀렉트박스에서 출력할 때 순서를 정합니다.\n\n숫자가 작을수록 상단에 출력합니다.");?>
    </td>
</tr>
<tr><td colspan=2 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<p align=center>
    <input type=submit class=btn1 accesskey='s' value='  확  인  '>&nbsp;
    <input type=button class=btn1 accesskey='l' value='  목  록  ' onclick="document.location.href='./deliverycodelist.php';">
</form>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
