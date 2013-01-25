<?
$sub_menu = "400710";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "FAQ 상세관리 : $fm[fm_subject]";
include_once ("$g4[admin_path]/admin.head.php");

$sql = " select * from $g4[yc4_faq_master_table] where fm_id = '$fm_id' ";
$fm = sql_fetch($sql);

$sql_common = " from $g4[yc4_faq_table] where fm_id = '$fm_id' ";

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$sql = "select * $sql_common order by fa_order , fa_id ";
$result = sql_query($sql);
?>

<table width=100%>
<tr>
    <td width=20%> </td>
    <td width=60% align=center>&nbsp;</td>
    <td width=20% align=right>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>


<table cellpadding=0 cellspacing=0 width=100%>
<colgroup width=80>
<colgroup width=''>
<colgroup width=80>
<colgroup width=80>
<tr><td colspan=4 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
    <td>번호</td>
    <td>제목</td>
    <td>순서</td>
    <td>
        <a href='./faqform.php?fm_id=<?=$fm[fm_id]?>'><img src='<?=$g4[admin_path]?>/img/icon_insert.gif' border=0></a>
        <a href='<?="$g4[shop_path]/faq.php?fm_id=$fm[fm_id]"?>'><img src='<?=$g4[admin_path]?>/img/icon_view.gif' border=0></a>
    </td>
</tr>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
<?
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $row1 = sql_fetch(" select COUNT(*) as cnt from $g4[yc4_faq_table] where fm_id = '$row[fm_id]' ");
    $cnt = $row1[cnt];

    $s_mod = icon("수정", "./faqform.php?w=u&fm_id=$row[fm_id]&fa_id=$row[fa_id]");
    $s_del = icon("삭제", "javascript:del('./faqformupdate.php?w=d&fm_id=$row[fm_id]&fa_id=$row[fa_id]');");

    $num = $i + 1;

    $list = $i%2;
    echo "
    <tr class='list$list ht'>
        <td align=center>$num</td>
        <td>" . stripslashes($row[fa_subject]) . "</td>
        <td align=center>$row[fa_order]</td>
        <td align=center>$s_mod $s_del</td>
    </tr>";
}

if ($i == 0) {
    echo "<tr><td colspan=20 align=center height=100 bgcolor=#ffffff><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
}
?>
<tr><td colspan=4 height=1 bgcolor=CCCCCC></td></tr>
</table><br><br>

<table width=100% cellpadding=5 cellspacing=0 border=0 bgcolor=#F6F6F6>
    <tr>
        <td>
            <table width=100% cellpadding=10 cellspacing=0 bgcolor=#FFFFFF>
                <tr>
                    <td style="line-height:220%;">
                        <B><FONT COLOR="#18ABFF">&middot; FAQ 질문, 답변 등록하기</FONT></B><BR>
        
                        &nbsp;&nbsp;: FAQ는 무제한으로 등록할 수 있습니다.<BR>
                        1. <img src='<?=$g4[admin_path]?>/img/icon_insert.gif' align=absmiddle>를 눌러 세부적인 질문과 답변을 입력합니다.<BR>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p align=center>
    <input type=button class=btn1 accesskey='l' value='  FAQ 관리  ' onclick="location='./faqmasterlist.php'">


<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
