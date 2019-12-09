<?php
$sub_menu = "300200";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$mb = get_member($mb_id);
if (!$mb['mb_id'])
    alert('존재하지 않는 회원입니다.');

$g5['title'] = '접근가능그룹';
include_once('./admin.head.php');

$colspan = 4;
?>

<form name="fboardgroupmember_form" id="fboardgroupmember_form" action="./boardgroupmember_update.php" onsubmit="return boardgroupmember_form_check(this)" method="post">
<input type="hidden" name="mb_id" value="<?php echo $mb['mb_id'] ?>" id="mb_id">
<input type="hidden" name="token" value="" id="token">
<div class="local_ov01 local_ov">
    <span class="btn_ov01"><span class="ov_txt"> 아이디</span><span class="ov_num"><?php echo $mb['mb_id'] ?></span></span>
    <span class="btn_ov01"><span class="ov_txt"> 이름</span><span class="ov_num"><?php echo get_text($mb['mb_name']); ?></span></span>
    <span class="btn_ov01"><span class="ov_txt"> 닉네임</span><span class="ov_num"><?php echo $mb['mb_nick'] ?></span></span>
</div>    
<div class="local_cmd01 local_cmd">

    <label for="gr_id">그룹지정</label>
    <select name="gr_id" id="gr_id">
        <option value="">접근가능 그룹을 선택하세요.</option>
        <?php
        $sql = " select *
                    from {$g5['group_table']}
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
    <input type="submit" value="선택" class="btn_submit btn" accesskey="s">
</div>
</form>

<form name="fboardgroupmember" id="fboardgroupmember" action="./boardgroupmember_update.php" onsubmit="return fboardgroupmember_submit(this);" method="post">
<input type="hidden" name="sst" value="<?php echo $sst ?>" id="sst">
<input type="hidden" name="sod" value="<?php echo $sod ?>" id="sod">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>" id="sfl">
<input type="hidden" name="stx" value="<?php echo $stx ?>" id="stx">
<input type="hidden" name="page" value="<?php echo $page ?>" id="page">
<input type="hidden" name="token" value="<?php echo $token ?>" id="token">
<input type="hidden" name="mb_id" value="<?php echo $mb['mb_id'] ?>" id="mb_id">
<input type="hidden" name="w" value="d" id="w">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">접근가능그룹 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">그룹아이디</th>
        <th scope="col">그룹</th>
        <th scope="col">처리일시</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sql = " select * from {$g5['group_member_table']} a, {$g5['group_table']} b
                where a.mb_id = '{$mb['mb_id']}'
                and a.gr_id = b.gr_id ";
    if ($is_admin != 'super')
        $sql .= " and b.gr_admin = '{$member['mb_id']}' ";
    $sql .= " order by a.gr_id desc ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++) {
    ?>
    <tr>
        <td class="td_chk">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo $row['gr_subject'] ?> 그룹</label>
            <input type="checkbox" name="chk[]" value="<?php echo $row['gm_id'] ?>" id="chk_<?php echo $i ?>">
        </td>
        <td class="td_grid"><a href="<?php echo G5_BBS_URL; ?>/group.php?gr_id=<?php echo $row['gr_id'] ?>"><?php echo $row['gr_id'] ?></a></td>
        <td class="td_category"><?php echo $row['gr_subject'] ?></td>
        <td class="td_datetime"><?php echo $row['gm_datetime'] ?></td>
    </tr>
    <?php
    }

    if ($i == 0) {
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">접근가능한 그룹이 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>
</div>

<div class="btn_list01 btn_list">
    <input type="submit" name="" value="선택삭제" class="btn btn_02">
</div>
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

<?php
include_once('./admin.tail.php');
?>
