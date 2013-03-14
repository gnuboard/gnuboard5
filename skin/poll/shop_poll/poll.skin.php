<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

global $is_admin;

// 투표번호가 넘어오지 않았다면 가장 큰(최근에 등록한) 투표번호를 얻는다
if (!$po_id) {
    $po_id = $config[cf_max_po_id];

    if (!$po_id) return;
}

$po = sql_fetch(" select * from $g4[poll_table] where po_id = '$po_id' ");
?>

<table background='<?=$poll_skin_path?>/img/bg_poll.gif' width=100%>
<form name="fpoll" method="post" action="<?=$g4[bbs_path]?>/poll_update.php" onsubmit="return fpoll_submit(this);" target="winPoll">
<input type="hidden" name="po_id" value="<?=$po_id?>">
<input type="hidden" name="skin_dir" value="<?=$skin_dir?>">
<tr><td style='padding:4px' align=center><img src='<?=$poll_skin_path?>/img/bar_poll.gif'></td></tr>
<tr><td align=center style='padding-bottom:4px'>
    <table bgcolor=#FFFFFF width=165 cellpadding=0 cellspacing=0>
    <tr><td height=5></td></tr>
    <tr><td height=25 style='padding:2px;'>&nbsp;<img src='<?=$poll_skin_path?>/img/icon_poll_q.gif' align=bottom> <?=$po[po_subject]?> <? if ($is_admin == "super") { ?><a href="<?=$g4[admin_path]?>/poll_form.php?w=u&po_id=<?=$po_id?>"><img src="<?=$poll_skin_path?>/img/admin.gif" width="33" height="15" border=0 align=absmiddle></a></center><? } ?></td></tr>
    <tr><td height=5></td></tr>

    <? for ($i=1; $i<=9 && $po["po_poll{$i}"]; $i++) { ?>
    <tr><td><input type="radio" name="gb_poll" value="<?=$i?>"> <?=$po['po_poll'.$i]?></td></tr>
    <? } ?>

    <tr><td align=center height=40>
        <input type="image" src="<?=$poll_skin_path?>/img/poll_button.gif" border="0">
        <a href="javascript:;" onclick="poll_result('<?=$po_id?>');"><img src="<?=$poll_skin_path?>/img/poll_view.gif" border="0"></td></tr>
    </table>
    </td>
</tr>
</form>
</table>

<script language='JavaScript'>
function fpoll_submit(f)
{
    var chk = false;
    for (i=0; i<f.gb_poll.length;i ++) {
        if (f.gb_poll[i].checked == true) {
            chk = f.gb_poll[i].value;
            break;
        }
    }

    <?
    if ($member[mb_level] < $po[po_level])
        echo " alert('권한 $po[po_level] 이상의 회원만 투표에 참여하실 수 있습니다.'); return false; ";
    ?>

    if (!chk) {
        alert("항목을 선택하세요");
        return false;
    }

    win_poll();
    return true;
}

function poll_result(po_id)
{
    <?
    if ($member[mb_level] < $po[po_level])
        echo " alert('권한 $po[po_level] 이상의 회원만 결과를 보실 수 있습니다.'); return false; ";
    ?>

    win_poll("<?=$g4[bbs_path]?>/poll_result.php?po_id="+po_id+"&skin_dir="+document.fpoll.skin_dir.value);
}
</script>
