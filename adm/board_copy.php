<?
$sub_menu = "300100";
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

$token = get_token();

$g4[title] = '게시판 복사';
$administrator = 1;
include_once($g4['path'].'/head.sub.php');
?>

<form id="fboardcopy" name="fboardcopy" method="post" onsubmit="return fboardcopy_check(this);" autocomplete="off">
<input type="hidden" id="bo_table" name="bo_table" value="<?=$bo_table?>">
<input type="hidden" id="token" name="token" value="<?=$token?>">
<table>
<tr>
	<th scope="col" id="th1">원본 테이블</th>
	<td headers="th1"><?=$bo_table?></td>
</tr>
<tr>
	<th scope="col" id="th2">복사할 TABLE</th>
	<td headers="th2"><input type="text" id="target_table" name="target_table" maxlength="20" required alphanumericunderline> 영문자, 숫자, _ 만 가능 (공백없이)</td>
</tr>
<tr>
	<th scope="col" id="th3">게시판 제목</th>
	<td headers="th3"><input type="text" id="target_subject" name="target_subject" maxlength=120 required value="[복사본] <?=$board["bo_subject"]?>"></td>
</tr>
<tr>
	<th scope="col" id="th4">복사 유형</th>
	<td headers="th4">
        <input type="radio" id="copy_case" name="copy_case" value="schema_only" checked>구조만
        <input type="radio" id="copy_case" name="copy_case" value="schema_data_both">구조와 데이터
    </td>
</tr>
</table>

<input type="submit" value="복사">
<input type="button" value="창닫기" onclick="window.close();">

</form>

<script type="text/javascript">
function fboardcopy_check(f)
{
    f.action = "./board_copy_update.php";
    return true;
}
</script>

<?
include_once($g4['path'].'/tail.sub.php');
?>
