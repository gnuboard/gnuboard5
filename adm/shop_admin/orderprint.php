<?
$sub_menu = "500120";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "주문내역출력";
include_once ("$g4[admin_path]/admin.head.php");
?>

<table width=550><tr><td>

<?=subtitle($g4[title])?>

<table cellpadding=0 cellspacing=0 border=0 width=100%>
<form name=forderprint action="./orderprintresult.php" onsubmit="return forderprintcheck(this);" autocomplete="off" style="margin:0px;">
<input type=hidden name=case value="1">
<tr><td colspan=20 height=2 bgcolor=#0E87F9></td></tr>
<colgroup width=100></colgroup>
<colgroup width='' bgcolor=#ffffff></colgroup>
<tr>
    <td>출력기간</td>
    <td>
        <table width=100% cellpadding=4>
        <tr>
            <td align=left>
                &nbsp; <input type='radio' name='csv' value='xls' id='xls1'><label for='xls1'>MS엑셀 XLS 데이터</label>
                &nbsp; <input type='radio' name='csv' value='csv' id='csv1'><label for='csv1'>MS엑셀 CSV 데이터</label>
            </td>
        </tr>
        <tr>
            <td align=right>
                <input type=text name=fr_date id='fr_date' size=10 maxlength=8 value="<?=date("Ymd");?>" class=ed><a href="javascript:win_calendar('fr_date', document.getElementById('fr_date').value, '');"><img src='<?=$g4[path]?>/img/calendar.gif' border=0 align=absmiddle title='달력 - 날짜를 선택하세요'></a>
                ∼
                <input type=text name=to_date id='to_date' size=10 maxlength=8 value="<?=date("Ymd");?>" class=ed><a href="javascript:win_calendar('to_date', document.getElementById('to_date').value, '');"><img src='<?=$g4[path]?>/img/calendar.gif' border=0 align=absmiddle title='달력 - 날짜를 선택하세요'></a>
                &nbsp;
                <select name=ct_status>
                    <option value='주문'>주문
                    <option value='준비'>상품준비중
                    <option value='배송'>배송
                    <option value='완료'>완료
                    <option value='취소'>취소
                    <option value='반품'>반품
                    <option value='품절'>품절
                    <option value=''>전체
                </select>
                &nbsp;
                <input type=submit class=btn1 value='  확  인  '>
            </td>
        </tr>
        </table>
    </td>
</tr>
</form>

<form name=forderprint action="./orderprintresult.php" onsubmit="return forderprintcheck(this);" autocomplete="off" style="margin:0px;">
<input type=hidden name=case value="2">
<tr><td colspan=20 height=2 bgcolor=#DDDDDD></td></tr>
<tr>
    <td>주문번호구간</td>
    <td>
        <table width=100% cellpadding=4>
        <tr>
            <td align=left>
                &nbsp; <input type='radio' name='csv' value='xls' id='xls2'><label for='xls2'>MS엑셀 XLS 데이터</label>
                &nbsp; <input type='radio' name='csv' value='csv' id='csv2'><label for='csv2'>MS엑셀 CSV 데이터</label>
            </td>
        </tr>
        <tr>
            <td align=right>
                <input type=text name=fr_od_id size=10 maxlength=10 class=ed> 부터
                <input type=text name=to_od_id size=10 maxlength=10 class=ed> 까지
                &nbsp;
                <select name=ct_status>
                    <option value='주문'>주문
                    <option value='준비'>상품준비중
                    <option value='배송'>배송
                    <option value='완료'>완료
                    <option value='취소'>취소
                    <option value='반품'>반품
                    <option value='품절'>품절
                    <option value=''>전체
                </select>
                &nbsp;
                <input type=submit class=btn1 value='  확  인  '>
            </td>
        </tr>
        </table>
    </td>
</tr>
<tr><td colspan=20 height=2 bgcolor=#0E87F9></td></tr>
</form>
</table>

</td></tr></table>

<script language="JavaScript">
function forderprintcheck(f)
{
    if (f.csv[0].checked || f.csv[1].checked) 
    {
        f.target = "_top";
    }
    else
    {
        var win = window.open("", "winprint", "left=10,top=10,width=670,height=800,menubar=yes,toolbar=yes,scrollbars=yes");
        f.target = "winprint";
    }

    f.submit();
}
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
