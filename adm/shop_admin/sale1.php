<?
$sub_menu = "500110";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "매출현황";
include_once ("$g4[admin_path]/admin.head.php");
?>

<?=subtitle($g4[title])?>

<table cellpadding=0 cellspacing=0 border=0>
<colgroup width=150></colgroup>
<colgroup width='' bgcolor=#ffffff></colgroup>
<tr><td colspan=2 height=2 bgcolor=#0E87F9></td></tr>
<tr height=40>
    <form name=frm_sale_today action='./sale1today.php'>
    <td>당일 매출현황</td>
    <td align=right>
        <input type=text name=date size=8 maxlength=8 value='<? echo date("Ymd", $g4['server_time']) ?>' class=ed>
        일 하루
        <input type=submit class=btn1 value='  확  인  '>
    </td>
    </form>
</tr>
<tr><td colspan=2 height=1 bgcolor=#CCCCCC></td></tr>
<tr height=40>
    <form name=frm_sale_date action='./sale1date.php'>
    <td>일별 매출현황</td>
    <td align=right>
        <input type=text name=fr_date size=8 maxlength=8 value='<? echo date("Ym01", $g4['server_time']) ?>' class=ed>
        일 부터
        <input type=text name=to_date size=8 maxlength=8 value='<? echo date("Ymd", $g4['server_time']) ?>' class=ed>
        일 까지
        <input type=submit class=btn1 value='  확  인  '>
    </td>
    </form>
</tr>
<tr><td colspan=2 height=1 bgcolor=#CCCCCC></td></tr>
<tr height=40>
    <form name=frm_sale_month action='./sale1month.php'>
    <td>월별 매출현황</td>
    <td align=right>
        <input type=text name=fr_date size=6 maxlength=6 value='<? echo date("Y01", $g4['server_time']) ?>' class=ed>
        월 부터
        <input type=text name=to_date size=6 maxlength=6 value='<? echo date("Ym", $g4['server_time']) ?>' class=ed>
        월 까지
        <input type=submit class=btn1 value='  확  인  '>
    </td>
    </form>
</tr>
<tr><td colspan=2 height=1 bgcolor=#CCCCCC></td></tr>
<tr height=40>
    <form name=frm_sale_year action='./sale1year.php'>
    <td>연별 매출현황</td>
    <td align=right>
        <input type=text name=fr_date size=4 maxlength=4 value='<? echo date("Y", $g4['server_time'])-1 ?>' class=ed>
        년 부터
        <input type=text name=to_date size=4 maxlength=4 value='<? echo date("Y", $g4['server_time']) ?>' class=ed>
        년 까지
        <input type=submit class=btn1 value='  확  인  '>
    </td>
    </form>
</tr>
<tr><td colspan=2 height=2 bgcolor=#0E87F9></td></tr>
</table>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
