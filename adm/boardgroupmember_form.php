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

<div class="cbox">
    <p>아이디 <?=$mb['mb_id']?>, 이름 <?=$mb['mb_name']?>, 별명 <?=$mb['mb_nick']?>님이 접근가능한 그룹 목록</p>
    <form id="fboardgroupmember" name="fboardgroupmember" method="post" action="./boardgroupmember_update.php" onsubmit="return fboardgroupmember_submit(this);">
    <input type="hidden" id="sst" name="sst" value="<?=$sst?>">
    <input type="hidden" id="sod" name="sod" value="<?=$sod?>">
    <input type="hidden" id="sfl" name="sfl" value="<?=$sfl?>">
    <input type="hidden" id="stx" name="stx" value="<?=$stx?>">
    <input type="hidden" id="page" name="page" value="<?=$page?>">
    <input type="hidden" id="token" name="token" value="<?=$token?>">
    <input type="hidden" id="mb_id" name="mb_id" value="<?=$mb['mb_id']?>">
    <input type="hidden" id="w" name="w" value="d">
    <table>
    <thead>
    <tr>
        <th scope="col"><input type="checkbox" id="chkall" name="chkall" value="1" title="현재 페이지 접근가능그룹 전체선택" onclick="check_all(this.form)"></th>
        <th scope="col">그룹아이디</th>
        <th scope="col">그룹</th>
        <th scope="col">처리일시</th>
        <th scope="col">삭제</th>
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
        <td class="td_chk"><input type="checkbox" id="chk_<?=$i?>" name="chk[]" value="<?=$row['gm_id']?>" title="<?=$row['gr_subject']?> 그룹 선택"></td>
        <td class="td_grid"><a href="<?=$g4['bbs_path']?>/group.php?gr_id=<?=$row['gr_id']?>"><?=$row['gr_id']?></a></td>
        <td class="td_category"><?=$row['gr_subject']?></td>
        <td class="td_time"><?=$row['gm_datetime']?></td>
        <td class="td_mng"><?=$s_del?></td>
    </tr>
    <?
    }

    if ($i == 0) {
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">접근가능한 그룹이 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>

    <div class="btn_list">
        <input type="submit" name="" value="선택삭제">
    </div>
    </form>
</div>

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
        //if ($is_admin == 'group') {
        if ($is_admin != 'super') 
            $sql .= " and gr_admin = '{$member['mb_id']}' ";
        $sql .= " order by gr_id ";
        $result = sql_query($sql);
        for ($i=0; $row=sql_fetch_array($result); $i++) {
            echo "<option value=\"".$row['gr_id']."\">".$row['gr_subject']."</option>";
        }
        ?>
    </select>
    <input type="submit" class="btn_submit" value="선택" accesskey="s">
    <p>게시판 그룹이 존재하지 않는다면 <a href="./boardgroup_form.php">게시판그룹생성하기</a></p>
</fieldset>
</form>

<script>
function fboardgroupmember_submit(f)
{
    if (!is_checked("chk[]")) {
        alert("선택삭제 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    return true;
}

function boardgroupmember_form_check(f)
{
    if (f.gr_id.value == '') {
        alert('접근가능 그룹을 선택하세요.');
        return false;
    }

    return true;
}
</script>

<?
include_once('./admin.tail.php');
?>
