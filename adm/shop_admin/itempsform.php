<?
$sub_menu = "400650";
define('G4_EDITOR', 1);
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

$sql = " select *
           from $g4[shop_item_ps_table] a
           left join $g4[member_table] b on (a.mb_id = b.mb_id)
           left join $g4[shop_item_table] c on (a.it_id = c.it_id)
          where is_id = '$is_id' ";
$is = sql_fetch($sql);
if (!$is[is_id])
    alert("등록된 자료가 없습니다.");

$name = get_sideview($is[mb_id], get_text($is[is_name]), $is[mb_email], $is[mb_homepage]);

$g4[title] = "사용후기 수정";
include_once(G4_ADMIN_PATH."/admin.head.php");

$qstr = "page=$page&sort1=$sort1&sort2=$sort2";
?>

<?=subtitle($g4[title])?>

<form id="fitemps" name="fitemps" method=post onsubmit="return fitemps_submit(this);">
<input type="hidden" id="w" name="w"     value='<? echo $w ?>'>
<input type="hidden" id="is_id" name="is_id" value='<? echo $is_id ?>'>
<input type="hidden" id="page" name="page"  value='<? echo $page ?>'>
<input type="hidden" id="sort1" name="sort1" value='<? echo $sort1 ?>'>
<input type="hidden" id="sort2" name="sort2" value='<? echo $sort2 ?>'>
<table cellpadding=0 cellspacing=1>
<colgroup class=tdsl></colgroup>
<colgroup bgcolor=#ffffff></colgroup>
<tr><td colspan=4 height=3 bgcolor=0E87F9></td></tr>
<tr height=25>
    <td>상품명</td>
    <td><a href='<?="$g4[shop_path]/item.php?it_id=$is[it_id]"?>'><?=$is[it_name]?></a></td>
</tr>
<tr height=25>
    <td>이 름</td>
    <td><?=$name?></td>
</tr>
<tr height=25>
    <td>점 수</td>
    <td><? echo stripslashes($is[is_score]) ?> 점</td>
</tr>
<tr height=25>
    <td>제 목</td>
    <td><input type="text" id="is_subject" name="is_subject" required itenmae='제목'
        value='<?=conv_subject($is[is_subject], 120)?>'></td>
</tr>
<tr>
    <td>내 용</td>
    <td>
        <!-- <?=textarea_size("is_content")?>
        <textarea id="is_content" id="is_content" name="is_content" rows="10" required itemname='내용'><? echo get_text($is[is_content]) ?></textarea> -->
        <?=editor_html('is_content', $is['is_content']);?>
    </td>
</tr>
<tr height=25>
    <td>확 인</td>
    <td><input type="checkbox" id="is_confirm" name="is_confirm" value='1' <?=($is[is_confirm]?"checked":"")?> id="is_confirm"> <label for='is_confirm'>확인하였습니다.</a></td>
</tr>

</table>

<p>
    <input type="submit" value='  확  인  ' accesskey='s'>&nbsp;
    <input type="button" value='  목  록  ' onclick="document.location.href='./itempslist.php?<?=$qstr?>';">
</form>

<script>
function fitemps_submit(f)
{
    <? echo get_editor_js('is_content'); ?>

    f.action="./itempsformupdate.php";
    return true;
}
</script>

<?
include_once(G4_ADMIN_PATH."/admin.tail.php");
?>
