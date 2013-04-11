<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

$sql = " select * from {$g4['shop_new_win_table']}
          where '".G4_TIME_YMDHIS."' between nw_begin_time and nw_end_time
          order by nw_id asc ";
$result = sql_query($sql);
for ($i=0; $row_nw=sql_fetch_array($result); $i++)
{
    // 이미 체크 되었다면 Continue
    if ($_COOKIE["ck_popup_{$row_nw['nw_id']}"])
        continue;

    $sql = " select * from {$g4['shop_new_win_table']} where nw_id = '{$row_nw['nw_id']}' ";
    $nw = sql_fetch($sql);
?>
    <div id="div_popup_<? echo $nw['nw_id'] ?>" style="position:absolute;left:<?=$nw['nw_left']?>px;top:<?=$nw['nw_top']?>px;background-color:#eee;z-index:100">
        <table width="<? echo $nw['nw_width'] ?>" height="<? echo $nw['nw_height'] ?>" cellpadding="0" cellspacing="0">
        <tr>
            <td valign="top"><?=conv_content($nw['nw_content'], 1);?></td>
        </tr>
        <tr>
            <td height="30" align="center"><input type="checkbox" id="check_popup_<?=$nw['nw_id']?>" name="check_popup_<?=$nw['nw_id']?>" value="<?=$nw['nw_disable_hours']?>" class="popup_close">&nbsp;<label for='check_popup_<?=$nw['nw_id']?>'><?=$nw['nw_disable_hours']?>시간동안 이창을 열지 않습니다.</label></td>
        </tr>
        </table>
    </div>
<? } ?>

<script>
$(function() {
    $(".popup_close").click(function() {
        if($(this).is(":checked")) {
            var id = $(this).attr("id");
            var layer_id = id.replace("check", "div");
            var ck_name = id.replace("check", "ck");
            var exp_time = parseInt($(this).val());
            $("#"+layer_id).css("display", "none");
            set_cookie(ck_name, 1, exp_time, g4_cookie_domain);
        }
    });
});
</script>