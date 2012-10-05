<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

global $is_admin;

// 투표번호가 넘어오지 않았다면 가장 큰(최근에 등록한) 투표번호를 얻는다
if (!$po_id) 
{
    $po_id = $config[cf_max_po_id];

    if (!$po_id) return;
}

$po = sql_fetch(" select * from $g4[poll_table] where po_id = '$po_id' ");
?>

<table width="220" border="0" cellspacing="0" cellpadding="0">
<form name="fpoll" method="post" action="<?=$g4[bbs_path]?>/poll_update.php" onsubmit="return fpoll_submit(this);" target="winPoll">
<input type="hidden" name="po_id" value="<?=$po_id?>">
<input type="hidden" name="skin_dir" value="<?=$skin_dir?>">
<tr>
    <td width=7 height=7><img src="<?=$poll_skin_path?>/img/bg_tl.gif" width=7></td>
    <td background="<?=$poll_skin_path?>/img/bg_t.gif"></td>
    <td width=6><img src="<?=$poll_skin_path?>/img/bg_tr.gif" width=6></td>
</tr>
<tr>
    <td background="<?=$poll_skin_path?>/img/bg_ml.gif"></td>
    <td>
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr><td height=5 colspan=5></td></tr>
        <tr>
            <td width="5"></td>
            <td align="center" colspan=3>
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td width=5><img src="<?=$poll_skin_path?>/img/bg_mcl.gif"></td>
                    <td align=center background="<?=$poll_skin_path?>/img/bg_mc.gif"><img src="<?=$poll_skin_path?>/img/title.gif"></td>
                    <td width=4><img src="<?=$poll_skin_path?>/img/bg_mcr.gif"></td>
                </tr>
                </table></td>
            <td width="5"></td>
        </tr>
        <tr><td height=10 colspan=4></td></tr>
        <tr>
            <td></td>
            <td width="25" align="center"><img src="<?=$poll_skin_path?>/img/q.gif" width="12" height="13"></td>
            <td height="20" style="text-align:justify;"><font color="#848484"><?=$po[po_subject]?></font>
                <? if ($is_admin == "super") { ?><a href="<?=$g4[admin_path]?>/poll_form.php?w=u&po_id=<?=$po_id?>"><img src="<?=$poll_skin_path?>/img/admin.gif" width="33" height="15" border=0 align=absmiddle></a></center><? } ?>
            </td>
            <td></td>
        </tr>
        <tr><td height=5 colspan=4></td></tr>

        <tr>
            <td></td>
            <td colspan=2>
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <? for ($i=1; $i<=9 && $po["po_poll{$i}"]; $i++) { ?>
                <tr>
                    <td width="25" align="center"><? if ($i == 1) { echo "<img src='$poll_skin_path/img/a.gif' width='12' height='13'>"; } else { echo "&nbsp;"; } ?></td>
                    <td width="30" height="25" align="center"><input type="radio" name="gb_poll" value="<?=$i?>" id='gb_poll_<?=$i?>'></td>
                    <td width=""><font color="#848484"><label for='gb_poll_<?=$i?>'><?=$po['po_poll'.$i]?></label></font></td>
                </tr>
                <? } ?>
                </table></td>
        </tr>
        <tr><td height=5 colspan=4></td></tr>
        <tr>
            <td></td>
            <td colspan="2" align=center>
                <input type="image" src="<?=$poll_skin_path?>/img/poll_button.gif" width="70" height="25" border="0">
                <a href="javascript:;" onclick="poll_result('<?=$po_id?>');"><img src="<?=$poll_skin_path?>/img/poll_view.gif" width="70" height="25" border="0"></td>
            <td></td>
        </tr>
        <tr><td height=5 colspan=5></td></tr>
        </table></td>
    <td background="<?=$poll_skin_path?>/img/bg_mr.gif"></td>
</tr>
<tr>
    <td height=7><img src="<?=$poll_skin_path?>/img/bg_bl.gif" width=7></td>
    <td background="<?=$poll_skin_path?>/img/bg_b.gif"></td>
    <td><img src="<?=$poll_skin_path?>/img/bg_br.gif" width=6></td>
</tr>
</form>
</table>

<script type='text/javascript'>
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
