<?
$sub_menu = "300200";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$token = get_token();

$mb = get_member($mb_id);
if (!$mb['mb_id'])
    alert('존재하지 않는 회원입니다.');

$g4['title'] = '회원별 접근가능그룹';
include_once('./admin.head.php');

$colspan = 4;
?>

<table>
<caption><a href="./member_form.php?w=u&amp;mb_id=<?=$mb['mb_id']?>"><?=$mb['mb_id']?></a> 이름:<?=$mb['mb_name']?>, 별명:<?=$mb['mb_nick']?> 님이 접근가능한 그룹 목록</caption>
<thead>
<tr>
    <th scope="col" id="th_gr_id">그룹아이디</th>
    <th scope="col" id="th_gr">그룹</th>
    <th scope="col" id="th_date">처리일시</th>
    <th scope="col" id="th_del">삭제</th>
</tr>
</thead>
<tbody>
<?
$sql = " select * from {$g4['group_member_table']} a, {$g4['group_table']} b
            where a.mb_id = '{$mb['mb_id']}'
            and a.gr_id = b.gr_id ";
if ($is_admin != 'super')
    $sql .= " and b.gr_admin = '{$member['mb_id']}' ";
$sql .= " order by a.gr_id desc ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $s_del = '<a href="javascript:post_delete(\'boardgroupmember_update.php\', \''.$row['gm_id'].'\');">삭제</a>';
?>
<tr>
    <td headers="th_gr_id"><a href="<?=$g4['bbs_path']?>/group.php?gr_id=<?=$row['gr_id']?>"><?=$row['gr_id']?></a></td>
    <td headers="th_gr"><?=$row['gr_subject']?></td>
    <td headers="th_date"><?=$row['gm_datetime']?></td>
    <td headers="th_del"><?=$s_del?></td>
</tr>
<?
}

if ($i == 0) {
    echo '<tr><td colspan="'.$colspan.'" class="empty_table">접근가능한 그룹이 없습니다.</td></tr>';
}
?>
</tbody>
</table>

<form id="fboardgroupmember_form" name="fboardgroupmember_form" method="post" action="./boardgroupmember_update.php" onsubmit="return boardgroupmember_form_check(this)">
<input type="hidden" id="mb_id" name="mb_id" value="<?=$mb['mb_id']?>">
<input type="hidden" id="token" name="token" value="<?=$token?>">
<fieldset>
    <legend><?=$mb['mb_id']?>님 접근가능그룹 추가</legend>
    <label for="gr_id">그룹지정</label>
    <select id="gr_id" name="gr_id">
    <option value="">접근가능 그룹을 선택하세요.</option>
    <?
    $sql = " select *
                from {$g4['group_table']}
                where gr_use_access = 1 ";
    if ($is_admin != 'super')
        $sql .= " and gr_admin = '{$member['mb_id']}' ";
    $sql .= " order by gr_id ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        echo '<option value="'.$row['gr_id'].'">'.$row['gr_subject'].'</option>';
    }
    ?>
    </select>
    <input type="submit" value="완료" accesskey="s">
</fieldset>
</form>

<script>
function boardgroupmember_form_check(f)
{
    if (f.gr_id.value == '') {
        alert('접근가능 그룹을 선택하세요.');
        return false;
    }

    return true;
}
</script>

<script>
// POST 방식으로 삭제
function post_delete(action_url, val)
{
    var f = document.fpost;

    if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) {
        f.gm_id.value = val;
        f.action      = action_url;
        f.submit();
    }
}
</script>

<form id="fpost" name="fpost" method="post">
<input type="hidden" id="sst" name="sst" value="<?=$sst?>">
<input type="hidden" id="sod" name="sod" value="<?=$sod?>">
<input type="hidden" id="sfl" name="sfl" value="<?=$sfl?>">
<input type="hidden" id="stx" name="stx" value="<?=$stx?>">
<input type="hidden" id="page" name="page" value="<?=$page?>">
<input type="hidden" id="token" name="token" value="<?=$token?>">
<input type="hidden" id="w" name="w" value="d">
<input type="hidden" id="gm_id" name="gm_id">
</form>

<?
include_once('./admin.tail.php');
?>
