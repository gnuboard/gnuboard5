<?
include_once("./_common.php");

if ($sw == "move")
    $act = "이동";
else if ($sw == "copy")
    $act = "복사";
else 
    alert("sw 값이 제대로 넘어오지 않았습니다.");

// 게시판 관리자 이상 복사, 이동 가능
if ($is_admin != "board" && $is_admin != "group" && $is_admin != "super") 
    alert_close("게시판 관리자 이상 접근이 가능합니다.");

$g4[title] = "게시물 " . $act;
include_once("$g4[path]/head.sub.php");

$wr_id_list = "";
if ($wr_id)
    $wr_id_list = $wr_id;
else {
    $comma = "";
    for ($i=0; $i<count($_POST[chk_wr_id]); $i++) {
        $wr_id_list .= $comma . $_POST[chk_wr_id][$i];
        $comma = ",";
    }
}

$sql = " select * 
           from $g4[board_table] a, 
                $g4[group_table] b
          where a.gr_id = b.gr_id
            and bo_table <> '$bo_table' ";
if ($is_admin == 'group') 
    $sql .= " and b.gr_admin = '$member[mb_id]' ";
else if ($is_admin == 'board') 
    $sql .= " and a.bo_admin = '$member[mb_id]' ";
$sql .= " order by a.gr_id, a.bo_order_search, a.bo_table ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) 
{
    $list[$i] = $row;
}
?>

<table width="100%" border="0" cellpadding="2" cellspacing="0"><tr><td>

<table width="100%" height="50" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" bgcolor="#EBEBEB" style="padding:5px;">
        <table width="100%" height="40" border="0" cellspacing="0" cellpadding="0">
        <tr> 
            <td width="25" align="center" bgcolor="#FFFFFF" ><img src="<?=$g4[bbs_img_path]?>/icon_01.gif" width="5" height="5"></td>
            <td width="" align="left" bgcolor="#FFFFFF" ><font color="#666666"><b>게시물<?=$act?></b></font></td>
        </tr>
        </table></td>
</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr> 
    <td height="20" colspan="3"></td>
</tr>
<tr> 
    <td width="30" height="24"></td>
    <td width="" align="left" valign="middle">※ <?=$act?>할 게시판을 한개 이상 선택하여 주십시오.</td>
    <td width="30" height="24"></td>
</tr>
</table>

<form name="fboardmoveall" method="post" onsubmit="return fboardmoveall_submit(this);">
<input type=hidden name=sw          value='<?=$sw?>'>
<input type=hidden name=bo_table    value='<?=$bo_table?>'>
<input type=hidden name=wr_id_list  value="<?=$wr_id_list?>">
<input type=hidden name=sfl         value='<?=$sfl?>'>
<input type=hidden name=stx         value='<?=$stx?>'>
<input type=hidden name=spt         value='<?=$spt?>'>
<input type=hidden name=page        value='<?=$page?>'>
<input type=hidden name=act         value='<?=$act?>'>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr> 
    <td height="20" align="center" valign="top">&nbsp;</td>
</tr>
<tr>
    <td align="center" valign="top">
        <table width="98%" border="0" cellspacing="0" cellpadding="0">
        
        <? for ($i=0; $i<count($list); $i++) { ?>
        <tr> 
            <td width="39" height="25" align="center"><input type=checkbox id='chk<?=$i?>' name='chk_bo_table[]' value="<?=$list[$i][bo_table]?>"></td>
            <td width="10" valign="bottom"><img src="<?=$g4[bbs_img_path]?>/l.gif" width="1" height="8"></td>
            <td width="490">
                <span style="cursor:pointer;" onclick="document.getElementById('chk<?=$i?>').checked=document.getElementById('chk<?=$i?>').checked?'':'checked';">
                    <?
                    if ($save_gr_subject==$list[$i][gr_subject])
                        echo "<span style='color:#cccccc;'>";
                    else
                        echo "<span>";
                    echo $list[$i][gr_subject] . " > ";
                    echo "</span>";
                    $save_gr_subject = $list[$i][gr_subject];
                    ?>
                    <?=$list[$i][bo_subject]?> (<?=$list[$i][bo_table]?>)</span>
            </td>
        </tr>
        <tr> 
            <td height="1" colspan="3" bgcolor="#E9E9E9"></td>
        </tr>
        <? } ?>
        </table></td>
</tr>
<tr> 
    <td height="40">&nbsp;</td>
</tr>
<tr> 
    <td height="2" bgcolor="#D5D5D5"></td>
</tr>
<tr> 
    <td height="2" bgcolor="#E6E6E6"></td>
</tr>
<tr> 
    <td height="40" align="center" valign="bottom"><input id="btn_submit" type=image src='<?=$g4[bbs_img_path]?>/ok_btn.gif' border=0>&nbsp;&nbsp;<a href="javascript:window.close();"><img src="<?=$g4[bbs_img_path]?>/btn_close.gif" width="48" height="20" border="0"></a></td>
</tr>
</table>

</form>

<script type='text/javascript'>
function fboardmoveall_submit(f)
{
    var check = false;

    if (typeof(f.elements['chk_bo_table[]']) == 'undefined') 
        ;
    else {
        if (typeof(f.elements['chk_bo_table[]'].length) == 'undefined') {
            if (f.elements['chk_bo_table[]'].checked) 
                check = true;
        } else {
            for (i=0; i<f.elements['chk_bo_table[]'].length; i++) {
                if (f.elements['chk_bo_table[]'][i].checked) {
                    check = true;
                    break;
                }
            }
        }
    }

    if (!check) {
        alert('게시물을 '+f.act.value+'할 게시판을 한개 이상 선택해 주십시오.');
        return false;
    }

    document.getElementById("btn_submit").disabled = true;

    f.action = "./move_update.php";
    return true;
}
</script>

</td></tr></table>

<?
include_once("$g4[path]/tail.sub.php");
?>
