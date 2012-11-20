<?
$sub_menu = "200900";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$token = get_token();

$html_title = '투표';
if ($w == '')
    $html_title .= ' 생성';
else if ($w == 'u')  {
    $html_title .= ' 수정';
    $sql = " select * from {$g4['poll_table']} where po_id = '{$po_id}' ";
    $po = sql_fetch($sql);
} else
    alert('w 값이 제대로 넘어오지 않았습니다.');

$g4['title'] = $html_title;
include_once('./admin.head.php');
?>

<form id="fpoll" name="fpoll" method="post" onsubmit="return fpoll_check(this);" enctype="multipart/form-data">
<input type="hidden" name="po_id" value="<?=$po_id?>">
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
    <th scope="row"><label for="po_subject">투표 제목</label></th>
    <td><input type="text" id="po_subject" name="po_subject" required value="<?=$po['po_subject']?>" maxlength="125"></td>
</tr>

<?
for ($i=1; $i<=9; $i++) {
    $required = '';
    if ($i==1 || $i==2) {
        $required = 'required';
    }

    $po_poll = get_text($po['po_poll'.$i]);
?>

<tr>
    <th scope="row"><label for="po_poll<?=$i?>">항목<?=$i?></label></th>
    <td>
        <input type="text" id="po_poll<?=$i?>" name="po_poll<?=$i?>" <?=$required?> value="<?=$po_poll?>" maxlength="125">
        <label for="po_cnt<?=$i?>">투표수</label>
        <input type="text" id="po_cnt<?=$i?>" name="po_cnt<?=$i?>" value="<?=$po[po_cnt.$i]?>">
   </td>
</tr>

<? } ?>

<tr>
    <th scope="row"><label for="po_etc">기타의견</label></th>
    <td><input type="text" id="po_etc" name="po_etc" value="<?=get_text($po['po_etc'])?>" maxlength="125"></td>
</tr>
<tr>
    <th scope="row"><label for="po_level">투표권한</label></th>
    <td><?=get_member_level_select('po_level', 1, 10, $po[po_level])?>이상 투표할 수 있음</td>
</tr>
<tr>
    <th scope="row"><label for="po_point">포인트</label></th>
    <td>
        <?=help('투표한 회원에게 부여함')?>
        <input type="text" id="po_point" name="po_point" value="<?=$po[po_point]?>"> 점
    </td>
</tr>

<? if ($w == 'u') { ?>
<tr>
    <th scope="row"><label for="po_date">투표시작일</label></th>
    <td><input type="text" id="po_date" name="po_date" maxlength="10" value="<?=$po['po_date']?>"></td>
</tr>
<tr>
    <th scope="row"><label for="po_ips">투표참가 IP</label></th>
    <td><textarea id="po_ips" name="po_ips" rows="10" readonly><?=preg_replace("/\n/", " / ", $po['po_ips'])?></textarea></td>
</tr>
<tr>
    <th scope="row"><label for="mb_ids">투표참가 회원</label></th>
    <td><textarea id="mb_ids" name="mb_ids" rows="10" readonly><?=preg_replace("/\n/", " / ", $po['mb_ids'])?></textarea></td>
</tr>
<? } ?>
</tbody>
</table>

<div class="btn_confirm">
    <input type="submit" accesskey="s" value="확인">
    <input type="button" value="목록" onclick="document.location.href='./poll_list.php?<?=$qstr?>';">
</div>
</form>

<script>
function fpoll_check(f)
{
    f.action = './poll_form_update.php';
    return true;
}
</script>

<?
include_once('./admin.tail.php');
?>
