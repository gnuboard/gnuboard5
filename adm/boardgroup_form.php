<?php
$sub_menu = "300200";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

if ($is_admin != 'super' && $w == '') alert('최고관리자만 접근 가능합니다.');

$html_title = '게시판그룹';
$gr_id_attr = '';
$sound_only = '';
if ($w == '') {
    $gr_id_attr = 'required';
    $sound_only = '<strong class="sound_only"> 필수</strong>';
    $gr['gr_use_access'] = 0;
    $html_title .= ' 생성';
} else if ($w == 'u') {
    $gr_id_attr = 'readonly';
    $gr = sql_fetch(" select * from {$g5['group_table']} where gr_id = '$gr_id' ");
    $html_title .= ' 수정';
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

if (!isset($group['gr_device'])) {
    sql_query(" ALTER TABLE `{$g5['group_table']}` ADD `gr_device` ENUM('both','pc','mobile') NOT NULL DEFAULT 'both' AFTER `gr_subject` ", false);
}


$g5['title'] = $html_title;
include_once('./admin.head.php');
?>

<form name="fboardgroup" id="fboardgroup" action="./boardgroup_form_update.php" onsubmit="return fboardgroup_check(this);" method="post" autocomplete="off">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="gr_id">그룹 ID<?php echo $sound_only ?></label></th>
        <td><input type="text" name="gr_id" value="<?php echo $group['gr_id'] ?>" id="gr_id" <?php echo $gr_id_attr; ?> class="<?php echo $gr_id_attr; ?> alnum_ frm_input" maxlength="10">
            <?php
            if ($w=='')
                echo '영문자, 숫자, _ 만 가능 (공백없이)';
            else
                echo '<a href="'.G5_BBS_URL.'/group.php?gr_id='.$group['gr_id'].'" class="btn_frmline">게시판그룹 바로가기</a>';
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="gr_subject">그룹 제목<strong class="sound_only"> 필수</strong></label></th>
        <td>
            <input type="text" name="gr_subject" value="<?php echo get_text($group['gr_subject']) ?>" id="gr_subject" required class="required frm_input" size="80">
            <?php
            if ($w == 'u')
                echo '<a href="./board_form.php?gr_id='.$gr_id.'" class="btn_frmline">게시판생성</a>';
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="gr_device">접속기기</label></th>
        <td>
            <?php echo help("PC 와 모바일 사용을 구분합니다.") ?>
            <select id="gr_device" name="gr_device">
                <option value="both"<?php echo get_selected($group['gr_device'], 'both', true); ?>>PC와 모바일에서 모두 사용</option>
                <option value="pc"<?php echo get_selected($group['gr_device'], 'pc'); ?>>PC 전용</option>
                <option value="mobile"<?php echo get_selected($group['gr_device'], 'mobile'); ?>>모바일 전용</option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php if ($is_admin == 'super') { ?><label for="gr_admin"><?php } ?>그룹 관리자<?php if ($is_admin == 'super') { ?></label><?php } ?></th>
        <td>
            <?php
            if ($is_admin == 'super')
                echo '<input type="text" id="gr_admin" name="gr_admin" class="frm_input" value="'.$gr['gr_admin'].'" maxlength="20">';
            else
                echo '<input type="hidden" id="gr_admin" name="gr_admin" value="'.$gr['gr_admin'].'">'.$gr['gr_admin'];
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="gr_use_access">접근회원사용</label></th>
        <td>
            <?php echo help("사용에 체크하시면 이 그룹에 속한 게시판은 접근가능한 회원만 접근이 가능합니다.") ?>
            <input type="checkbox" name="gr_use_access" value="1" id="gr_use_access" <?php echo $gr['gr_use_access']?'checked':''; ?>>
            사용
        </td>
    </tr>
    <tr>
        <th scope="row">접근회원수</th>
        <td>
            <?php
            // 접근회원수
            $sql1 = " select count(*) as cnt from {$g5['group_member_table']} where gr_id = '{$gr_id}' ";
            $row1 = sql_fetch($sql1);
            echo '<a href="./boardgroupmember_list.php?gr_id='.$gr_id.'">'.$row1['cnt'].'</a>';
            ?>
        </td>
    </tr>
    <?php for ($i=1;$i<=10;$i++) { ?>
    <tr>
        <th scope="row">여분필드<?php echo $i ?></th>
        <td class="td_extra">
            <label for="gr_<?php echo $i ?>_subj">여분필드 <?php echo $i ?> 제목</label>
            <input type="text" name="gr_<?php echo $i ?>_subj" value="<?php echo get_text($group['gr_'.$i.'_subj']) ?>" id="gr_<?php echo $i ?>_subj" class="frm_input">
            <label for="gr_<?php echo $i ?>">여분필드 <?php echo $i ?> 내용</label>
            <input type="text" name="gr_<?php echo $i ?>" value="<?php echo $gr['gr_'.$i] ?>" id="gr_<?php echo $i ?>" class="frm_input">
        </td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <a href="./boardgroup_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
    <input type="submit" class="btn_submit btn" accesskey="s" value="확인">
</div>

</form>

<div class="local_desc01 local_desc">
    <p>
        게시판을 생성하시려면 1개 이상의 게시판그룹이 필요합니다.<br>
        게시판그룹을 이용하시면 더 효과적으로 게시판을 관리할 수 있습니다.
    </p>
</div>

<script>
function fboardgroup_check(f)
{
    f.action = './boardgroup_form_update.php';
    return true;
}
</script>

<?php
include_once ('./admin.tail.php');
?>
