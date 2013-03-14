<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<style>
.n_title1 { font-family:돋움; font-size:9pt; color:#FFFFFF; }
.n_title2 { font-family:돋움; font-size:9pt; color:#5E5E5E; }
</style>

</style>

<!-- 분류 시작 -->
<form name=fnew method=get style="margin:0px;">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td height=30>
        <?=$group_select?>
        <select id=view name=view onchange="select_change()">
            <option value=''>전체게시물
            <option value='w'>원글만
            <option value='c'>코멘트만
        </select>
        &nbsp;<b>회원아이디 : </b>
        <input type=text class=ed id='mb_id' name='mb_id' value='<?=$mb_id?>'>
        <input type=submit value='검색'>
        <script type="text/javascript">
        function select_change()
        {
            document.fnew.submit();
        }
        document.getElementById("gr_id").value = "<?=$gr_id?>";
        document.getElementById("view").value = "<?=$view?>";
        </script>
    </td>
</tr>
</table>
</form>
<!-- 분류 끝 -->

<!-- 제목 시작 -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr> 
    <td width="4" height="33" bgcolor="#7BB2D6"><img src="<?=$new_skin_path?>/img/list_top_01.gif" width="4" height="33"></td>
    <td width="100" align="center" bgcolor="#7BB2D6" height="30"><font class=n_title1><strong>그룹</strong></font></td>
    <td width="5" align="center" valign="middle" bgcolor="#7BB2D6"><img src="<?=$new_skin_path?>/img/list_top_02.gif" width="5" height="33"></td>
    <td width="5" align="center" valign="middle" bgcolor="#EEEEEE"><img src="<?=$new_skin_path?>/img/list_top_03.gif" width="5" height="33"></td>
    <td width="100" align="center" bgcolor="#EEEEEE"><font class=n_title2><strong>게시판</strong></font></td>
    <td width="" align="center" bgcolor="#EEEEEE"><font class=n_title2><strong>제목</strong></font></td>
    <td width="120" align="center" bgcolor="#EEEEEE"><font class=n_title2><strong>이름</strong></font></td>
    <td width="60" align="center" bgcolor="#EEEEEE"><font class=n_title2><strong>일시</strong></font></td>
    <td width="4" bgcolor="#EEEEEE"><img src="<?=$new_skin_path?>/img/list_top_04.gif" width="4" height="33"></td>
</tr>
<?
for ($i=0; $i<count($list); $i++) 
{
    $gr_subject = cut_str($list[$i][gr_subject], 10);
    $bo_subject = cut_str($list[$i][bo_subject], 10);
    $wr_subject = get_text(cut_str($list[$i][wr_subject], 40));

    echo <<<HEREDOC
<tr> 
    <td align="center" height="30" colspan=3><a href='./new.php?gr_id={$list[$i][gr_id]}'>{$gr_subject}</a></td>
    <td align="center" colspan=2><a href='./board.php?bo_table={$list[$i][bo_table]}'>{$bo_subject}</a></td>
    <td width="">&nbsp;<a href='{$list[$i][href]}'>{$list[$i][comment]}{$wr_subject}</a></td>
    <td align="center">{$list[$i][name]}</td>
    <td align="center" colspan=2>{$list[$i][datetime2]}</td>
    <!-- <a href="javascript:;" onclick="document.getElementById('mb_id').value='{$list[$i][mb_id]}';">&middot;</a> -->
</tr>
<tr>
    <td colspan="9" height="1" background="{$new_skin_path}/img/dot_bg.gif"></td>
</tr>
HEREDOC;
}
?>

<? if ($i == 0) { ?>
<tr><td colspan="9" height=50 align=center>게시물이 없습니다.</td></tr>
<? } ?>
<tr> 
    <td colspan="9" height="30" align="center"><?=$write_pages?></td>
</tr>
</table>
