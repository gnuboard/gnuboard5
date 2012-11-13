<?
$sub_menu = "300200";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$token = get_token();

if ($is_admin != 'super' && $w == '') alert('최고관리자만 접근 가능합니다.');

$html_title = '게시판그룹';
if ($w == '')
{
    $gr_id_attr = 'required';
    $gr['gr_use_access'] = 0;
    $html_title .= ' 생성';
}
else if ($w == 'u')
{
    $gr_id_attr = 'readonly style="background-color:#dddddd"';
    $gr = sql_fetch(" select * from {$g4['group_table']} where gr_id = '$gr_id' ");
    $html_title .= ' 수정';
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

$g4['title'] = $html_title;
include_once('./admin.head.php');
?>

<form id="fboardgroup" name="fboardgroup" method="post" onsubmit="return fboardgroup_check(this);" autocomplete="off">
<input type="hidden" name="w" value="<?=$w?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="token" value="<?=$token?>">
<table>
<tbody>
<tr>
    <th scope="row"><label for="gr_id">그룹 ID</label></th>
    <td><input type="text" id="gr_id" name="gr_id" maxlength="10" <?=$gr_id_attr?> alphanumericunderline value="<?=$group['gr_id']?>"> 영문자, 숫자, _ 만 가능 (공백없이)</td>
</tr>
<tr>
    <th scope="row"><label for="gr_subject">그룹 제목</label></th>
    <td>
        <input type="text" id="gr_subject" name="gr_subject" required value="<?=get_text($group['gr_subject'])?>">
        <?
        if ($w == 'u')
            echo '<input type="button" value="게시판생성" onclick="location.href=\'./board_form.php?gr_id='.$gr_id.'\';">';
        ?>
    </td>
</tr>
<tr>
    <th scope="row"><label for="gr_admin">그룹 관리자</label></th>
    <td>
        <?
        if ($is_admin == 'super')
            echo '<input type="text" id="gr_admin" name="gr_admin" value="'.$gr['gr_admin'].'" maxlength="20">';
        else
            echo '<input type="hidden" id="gr_admin" name="gr_admin" value="'.$gr['gr_admin'].'">'.$gr['gr_admin'];
        ?>
    </td>
</tr>
<tr>
    <th scope="row"><label for="gr_use_access">접근회원사용</label></th>
    <td>
        <?=help("사용에 체크하시면 이 그룹에 속한 게시판은 접근가능한 회원만 접근이 가능합니다.")?>
        <input type="checkbox" id="gr_use_access" name="gr_use_access" value="1" <?=$gr['gr_use_access']?'checked':'';?>>사용
    </td>
</tr>
<tr>
    <th scope="row">접근회원수</th>
    <td headers="th_access_cnt">
        <?
        // 접근회원수
        $sql1 = " select count(*) as cnt from {$g4['group_member_table']} where gr_id = '{$gr_id}' ";
        $row1 = sql_fetch($sql1);
        echo '<a href="./boardgroupmember_list.php?gr_id='.$gr_id.'">'.$row1['cnt'].'</a>';
        ?>
    </td>
</tr>
<? for ($i=1;$i<=10;$i++) { ?>
<tr>
    <th scope="row" id="th_extra_<?=$i?>">회원여분필드<?=$i?></th>
    <td headers="th_extra_<?=$i?>">
        <label for="gr_<?=$i?>_subj">여분필드 <?=$i?> 제목</label>
        <input type="text" id="gr_<?=$i?>_subj" name="gr_<?=$i?>_subj" value="<?=get_text($group['gr_'.$i.'_subj'])?>">
        <label for="gr_<?=$i?>">여분필드 <?=$i?> 내용</label>
        <input type="text" id="gr_<?=$i?>" name="gr_<?=$i?>" value="<?=$gr['gr_'.$i]?>">
    </td>
</tr>
<? } ?>
</tbody>
</table>

<div class="btn_confirm">
    <input type="submit" accesskey="s" value="확인">
    <input type="button" value='목록' onclick="document.location.href='./boardgroup_list.php?<?=$qstr?>';">
</div>

</form>

<script>
if (document.fboardgroup.w.value == '')
    document.fboardgroup.gr_id.focus();
else
    document.fboardgroup.gr_subject.focus();

function fboardgroup_check(f)
{
    f.action = './boardgroup_form_update.php';
    return true;
}
</script>

<?
include_once ('./admin.tail.php');
?>
