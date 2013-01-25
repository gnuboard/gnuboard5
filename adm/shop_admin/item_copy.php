<?
$sub_menu = "400300";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "상품 복사";
include_once("$g4[path]/head.sub.php");
?>

<link rel='stylesheet' href='./admin.style.css' type='text/css'>

<table width=100% cellpadding=8><tr><td>

<?=subtitle($g4[title]);?>
<table cellpadding=4 cellspacing=1 width=100%>
<tr><td colspan=2 height=3 bgcolor=0E87F9></td></tr>
<tr align=center>
    <td>상품코드</td>
    <td><input type='text' id='new_it_id' value='<?=time()?>'></td>
</tr>
</table>

<p>
<div align='center'>
<input type='button' value='복사하기' onclick="_copy('item_copy_update.php?it_id=<?=$it_id?>&ca_id=<?=$ca_id?>');">
&nbsp;
<input type='button' value='창닫기' onclick='self.close();'>
</div>
</form>

<script type='text/javascript'>
function _copy(link)
{
    var new_it_id = document.getElementById('new_it_id').value;
    if (g4_charset.toUpperCase() == 'EUC-KR') 
        opener.parent.location.href = link+'&new_it_id='+new_it_id;
    else
        opener.parent.location.href = encodeURI(link+'&new_it_id='+new_it_id);
    self.close();
}
</script>

<?
include_once("$g4[path]/tail.sub.php");
?>