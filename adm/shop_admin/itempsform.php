<?
$sub_menu = "400650";
include_once("./_common.php");
include_once ("$g4[path]/lib/cheditor4.lib.php");

auth_check($auth[$sub_menu], "w");

$sql = " select * 
           from $g4[yc4_item_ps_table] a
           left join $g4[member_table] b on (a.mb_id = b.mb_id) 
           left join $g4[yc4_item_table] c on (a.it_id = c.it_id)
          where is_id = '$is_id' ";
$is = sql_fetch($sql);
if (!$is[is_id]) 
    alert("등록된 자료가 없습니다.");

$name = get_sideview($is[mb_id], get_text($is[is_name]), $is[mb_email], $is[mb_homepage]);

$g4[title] = "사용후기 수정";
include_once ("$g4[admin_path]/admin.head.php");

$qstr = "page=$page&sort1=$sort1&sort2=$sort2";
?>
<script src="<?=$g4[cheditor4_path]?>/cheditor.js"></script>
<?=cheditor1('is_content', '100%', '350');?>

<?=subtitle($g4[title])?>

<form name=fitemps method=post onsubmit="return fitemps_submit(this);" style="margin:0px;">
<input type=hidden name=w     value='<? echo $w ?>'>
<input type=hidden name=is_id value='<? echo $is_id ?>'>
<input type=hidden name=page  value='<? echo $page ?>'>
<input type=hidden name=sort1 value='<? echo $sort1 ?>'>
<input type=hidden name=sort2 value='<? echo $sort2 ?>'>
<table cellpadding=0 cellspacing=1 width=100%>
<colgroup width=120 class=tdsl></colgroup>
<colgroup width='' bgcolor=#ffffff></colgroup>
<tr><td colspan=4 height=3 bgcolor=0E87F9></td></tr>
<tr height=25>
    <td>&nbsp;상품명</td>
    <td><a href='<?="$g4[shop_path]/item.php?it_id=$is[it_id]"?>'><?=$is[it_name]?></a></td>
</tr>
<tr height=25>
    <td>&nbsp;이 름</td>
    <td><?=$name?></td>
</tr>
<tr height=25>
    <td>&nbsp;점 수</td>
    <td><? echo stripslashes($is[is_score]) ?> 점</td>
</tr>
<tr height=25>
    <td>&nbsp;제 목</td>
    <td><input type=text class=ed name=is_subject required itenmae='제목' style='width:99%;'
        value='<?=conv_subject($is[is_subject], 120)?>'></td>
</tr>
<tr>
    <td>&nbsp;내 용</td>
    <td>
        <!-- <?=textarea_size("is_content")?>
        <textarea id='is_content' name="is_content" rows="10" style='width:99%;' class=ed required itemname='내용'><? echo get_text($is[is_content]) ?></textarea> -->
        <?=cheditor2('is_content', $is['is_content']);?>
    </td>
</tr>
<tr height=25>
    <td>&nbsp;확 인</td>
    <td><input type=checkbox name=is_confirm value='1' <?=($is[is_confirm]?"checked":"")?> id=is_confirm> <label for='is_confirm'>확인하였습니다.</a></td>
</tr>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<p align=center>
    <input type=submit class=btn1 value='  확  인  ' accesskey='s'>&nbsp;
    <input type=button class=btn1 value='  목  록  ' onclick="document.location.href='./itempslist.php?<?=$qstr?>';">
</form>

<script>
function fitemps_submit(f)
{
    <? echo cheditor3('is_content'); ?>

    f.action="./itempsformupdate.php";
    return true;
}
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
