<?
$sub_menu = "200900";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

$token = get_token();

$html_title = "투표";
if ($w == "")
    $html_title .= " 생성";
else if ($w == "u")  {
    $html_title .= " 수정";
    $sql = " select * from $g4[poll_table] where po_id = '$po_id' ";
    $po = sql_fetch($sql);
} else 
    alert("w 값이 제대로 넘어오지 않았습니다."); 

$g4[title] = $html_title;
include_once("./admin.head.php");
?>

<form name=fpoll method=post onsubmit="return fpoll_check(this);" enctype="multipart/form-data">
<input type=hidden name=po_id value='<?=$po_id?>'>
<input type=hidden name=w     value='<?=$w?>'>
<input type=hidden name=sfl   value='<?=$sfl?>'>
<input type=hidden name=stx   value='<?=$stx?>'>
<input type=hidden name=sst   value='<?=$sst?>'>
<input type=hidden name=sod   value='<?=$sod?>'>
<input type=hidden name=page  value='<?=$page?>'>
<input type=hidden name=token value='<?=$token?>'>
<table width=100% cellpadding=0 cellspacing=0>
<colgroup width=20% class='col1 pad1 bold right'>
<colgroup width=30% class='col2 pad2'>
<colgroup width=20% class='col1 pad1 bold right'>
<colgroup width=30% class='col2 pad2'>
<tr>
    <td colspan=4 class=title align=left><img src='<?=$g4[admin_path]?>/img/icon_title.gif'> <?=$html_title?></td>
</tr>
<tr><td colspan=4 class='line1'></td></tr>
<tr class='ht'>
    <td>투표 제목</td>
    <td colspan=3><input type='text' class=ed name='po_subject' style='width:99%;' required itemname='투표 제목' value='<?=$po[po_subject]?>' maxlength="125"></td>
</tr>

<? 
for ($i=1; $i<=9; $i++) {
    $required = "";
    $itemname = "";
    if ($i==1 || $i==2) {
        $required = "required";
        $itemname = "itemname='항목$i'";
    }

    $po_poll = get_text($po["po_poll".$i]);

    echo <<<HEREDOC
    <tr class='ht'>
        <td>항목{$i}</td>
        <td><input type="text" class=ed name="po_poll{$i}" {$required} {$itemname} value="{$po_poll}" style="width:99%;" maxlength="125"></td>
        <td>투표수</td>
        <td><input type="text" class=ed name="po_cnt{$i}" size=5 value="{$po["po_cnt".$i]}"></td>
        
    </tr>
HEREDOC;
} 
?>

<tr class='ht'>
    <td>기타의견</td>
    <td colspan=3><input type='text' class=ed name='po_etc' style='width:99%;' value='<?=get_text($po[po_etc])?>' maxlength="125"></td>
</tr>

<tr class='ht'>
    <td>투표권한</td>
    <td colspan=3><?=get_member_level_select("po_level", 1, 10, $po[po_level])?>이상 투표할 수 있음</td>
</tr>

<tr class='ht'>
    <td>포인트</td>
    <td colspan=3><input type='text' class=ed name='po_point' size='10' value='<?=$po[po_point]?>'> 점 (투표한 회원에게 부여함)</td>
</tr>


<? if ($w == "u") { ?>
<tr class='ht'>
    <td>투표시작일</td>
    <td colspan=3><input type="text" class=ed name="po_date" size=10 maxlength=10 value="<?=$po[po_date]?>"></td>
</tr>

<tr class='ht'>
    <td>투표참가 IP</td>
    <td colspan=3><textarea class=ed name="po_ips" rows=10 style='width:99%;' readonly><?=preg_replace("/\n/", " / ", $po[po_ips])?></textarea></td>
</tr>

<tr class='ht'>
    <td>투표참가 회원</td>
    <td colspan=3><textarea class=ed name="mb_ids" rows=10 style='width:99%;' readonly><?=preg_replace("/\n/", " / ", $po[mb_ids])?></textarea></td>
</tr>

<? } ?>

<tr><td colspan=4 class='line2'></td></tr>
</table>

<p align=center>
    <input type=submit class=btn1 accesskey='s' value='  확  인  '>&nbsp;
    <input type=button class=btn1 value='  목  록  ' onclick="document.location.href='./poll_list.php?<?=$qstr?>';">
</form>

<script type='text/javascript'>
function fpoll_check(f)
{
    f.action = './poll_form_update.php';
    return true;
}
</script>

<?
include_once("./admin.tail.php");
?>
