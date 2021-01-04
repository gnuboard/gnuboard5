<?php
$sub_menu = "300200";
include_once('./_common.php');

//print_r2($_POST); exit;

check_demo();

auth_check_menu($auth, $sub_menu, 'w');

check_admin_token();

$post_chk = isset($_POST['chk']) ? (array) $_POST['chk'] : array();
$post_group_id = isset($_POST['group_id']) ? (array) $_POST['group_id'] : array();
$act_button = isset($_POST['act_button']) ? $_POST['act_button'] : '';

$count = count($post_chk);

if(!$count)
    alert($act_button.'할 게시판그룹을 1개이상 선택해 주세요.');

for ($i=0; $i<$count; $i++)
{
    $k     = isset($post_chk[$i]) ? (int) $post_chk[$i] : 0;
    $gr_id = preg_replace('/[^a-z0-9_]/i', '', $post_group_id[$k]);
    $gr_subject = isset($_POST['gr_subject'][$k]) ? strip_tags(clean_xss_attributes($_POST['gr_subject'][$k])) : '';
    $gr_admin = isset($_POST['gr_admin'][$k]) ? strip_tags(clean_xss_attributes($_POST['gr_admin'][$k])) : '';
    $gr_device = isset($_POST['gr_device'][$k]) ? clean_xss_tags($_POST['gr_device'][$k], 1, 1, 10) : '';
    $gr_use_access = isset($_POST['gr_use_access'][$k]) ? (int) $_POST['gr_use_access'][$k] : 0;
    $gr_order = isset($_POST['gr_order'][$k]) ? (int) $_POST['gr_order'][$k] : 0;

    if($act_button == '선택수정') {
        $sql = " update {$g5['group_table']}
                    set gr_subject    = '{$gr_subject}',
                        gr_device     = '".sql_real_escape_string($gr_device)."',
                        gr_admin      = '".sql_real_escape_string($gr_admin)."',
                        gr_use_access = '".$gr_use_access."',
                        gr_order      = '".$gr_order."'
                  where gr_id         = '{$gr_id}' ";
        if ($is_admin != 'super')
            $sql .= " and gr_admin    = '{$gr_admin}' ";
        sql_query($sql);
    } else if($act_button == '선택삭제') {
        $row = sql_fetch(" select count(*) as cnt from {$g5['board_table']} where gr_id = '$gr_id' ");
        if ($row['cnt'])
            alert("이 그룹에 속한 게시판이 존재하여 게시판 그룹을 삭제할 수 없습니다.\\n\\n이 그룹에 속한 게시판을 먼저 삭제하여 주십시오.", './board_list.php?sfl=gr_id&amp;stx='.$gr_id);

        // 그룹 삭제
        sql_query(" delete from {$g5['group_table']} where gr_id = '$gr_id' ");

        // 그룹접근 회원 삭제
        sql_query(" delete from {$g5['group_member_table']} where gr_id = '$gr_id' ");
    }
}

run_event('admin_boardgroup_list_update', $act_button, $chk, $post_group_id, $qstr);

goto_url('./boardgroup_list.php?'.$qstr);