<?
$sub_menu = "500125";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "배송일괄등록";
include_once ("$g4[admin_path]/admin.head.php");
?>

<table width=550><tr><td>

<?=subtitle($g4[title])?>

<form name="finvoicebundle" method="post" action="invoicebundleupdate.php" onsubmit="return finvoicebundle_submit(this);" enctype="multipart/form-data" style="margin:0px;">
<input type=hidden name=case value="1">

<table cellpadding=0 cellspacing=0 border=0 width=100%>
<tr><td colspan=20 height=2 bgcolor=#0E87F9></td></tr>
<colgroup width=100></colgroup>
<colgroup width='' bgcolor=#ffffff></colgroup>
<tr class=ht>
    <td>CSV 파일</td>
    <td class=lh style='padding:3px 0 3px 0;'>
        <input type=file name='csv_file' size=40 class='ed'>
        <br><font color=crimson>주문내역출력에서 다운로드 받은 CSV 파일에 운송장번호만 입력하신 후 저장하여, 
            반드시 이 CSV 파일로만 업로드 하시기 바랍니다.</font>
    </td>
</tr>
<tr class=ht>
    <td>배송회사</td>
    <td>
        <select name=dl_id>
        <option value=''>배송회사를 선택하세요.
        <?
        $sql = "select * from $g4[yc4_delivery_table] order by dl_order desc, dl_id desc ";
        $result = sql_query($sql);
        for ($i=0; $row=sql_fetch_array($result); $i++)
            echo "<option value='$row[dl_id]'>$row[dl_company]\n";
        mysql_free_result($result);
        ?>
        </select>
    </td>
</tr>
<tr class=ht>
    <td>배송일시</td>
    <td>
        <input type=text name='od_invoice_time' maxlength=19 class='ed'>
        <input type=checkbox name=od_invoice_chk
            value="<? echo date("Y-m-d H:i:s", $g4['server_time']); ?>"
            onclick="if (this.checked == true) this.form.od_invoice_time.value=this.form.od_invoice_chk.value; else this.form.od_invoice_time.value = this.form.od_invoice_time.defaultValue;">현재 시간
    </td>
</tr>
<tr class=ht>
    <td>주문상품 상태</td>
    <td>
        <select name=ct_status>
        <option value=''>변경안함
        <option value='배송'>배송중
        <option value='완료'>완료
        <select>
        주문상품의 상태를 일괄 변경합니다.
    </td>
</tr>
<tr class=ht>
    <td>업데이트</td>
    <td>
        <input type=checkbox name='re' value='1'> 이미 입력된 배송정보를 모두 새로 업데이트 합니다.
    </td>
</tr>
<tr><td colspan=20 height=2 bgcolor=#0E87F9></td></tr>
</table>

<p align=center>
    <input type=submit class=btn1 value='  확  인  '>

</form>

</td></tr></table>

<script>
function finvoicebundle_submit(f) 
{
    if (!f.csv_file.value) 
    {
        alert('배송일괄 처리할 CSV 파일을 선택하십시오.');
        f.csv_file.focus();
        return false;
    }

    if (!f.csv_file.value.match(/\.(csv)$/i)) 
    {    
        alert("쉼표로 분리(CSV : comma separated value) 된  파일이 아닙니다.\n\n예) filename.csv (확장자가 csv 인 파일만 업로드 가능합니다.)");
        return false;
    }

    if (!f.dl_id.value) 
    {
        alert('배송회사를 선택하세요.');
        f.dl_id.focus();
        return false;
    }

    return true;
}
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
