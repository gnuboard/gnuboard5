<?
$sub_menu = "300100";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

$token = get_token();

$g4[title] = "게시판 복사";
include_once("$g4[path]/head.sub.php");
?>

<link rel="stylesheet" href="./admin.style.css" type="text/css">

<form name="fboardcopy" method='post' onsubmit="return fboardcopy_check(this);" autocomplete="off">
<input type="hidden" name="bo_table" value="<?=$bo_table?>">
<input type="hidden" name="token"    value="<?=$token?>">
<table width=100% cellpadding=0 cellspacing=0>
<colgroup width=30% class='col1 pad1 bold right'>
<colgroup width=70% class='col2 pad2'>
<tr><td colspan=2 height=5></td></tr>
<tr>
    <td colspan=2 class=title align=left><img src='<?=$g4[admin_path]?>/img/icon_title.gif'> <?=$g4[title]?></td>
</tr>
<tr><td colspan=2 class='line1'></td></tr>
<tr class='ht'>
	<td>원본 테이블</td>
	<td><?=$bo_table?></td>
</tr>
<tr class='ht'>
	<td>복사할 TABLE</td>
	<td><input type=text class=ed name="target_table" size="20" maxlength="20" required alphanumericunderline itemname="TABLE"> 영문자, 숫자, _ 만 가능 (공백없이)</td>
</tr>
<tr class='ht'>
	<td>게시판 제목</td>
	<td><input type=text class=ed name='target_subject' size=60 maxlength=120 required itemname='게시판 제목' value='[복사본] <?=$board[bo_subject]?>'></td>
</tr>
<tr class='ht'>
	<td>복사 유형</td>
	<td>
        <input type="radio" name="copy_case" value="schema_only" checked>구조만
        <input type="radio" name="copy_case" value="schema_data_both">구조와 데이터
    </td>
</tr>
<tr height=40>
    <td></td>
	<td>
        <input type="submit" value="  복  사  " class=btn1>&nbsp;
        <input type="button" value="창닫기" onclick="window.close();" class=btn1>
    </td>
</tr>
</table>

</form>

<script type='text/javascript'>
function fboardcopy_check(f)
{
    f.action = "./board_copy_update.php";
    return true;
}
</script>

<?
include_once("$g4[path]/tail.sub.php");
?>
