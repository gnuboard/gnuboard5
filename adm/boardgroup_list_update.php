<?php
$sub_menu = "300200";
require_once './_common.php';

check_demo();

auth_check_menu($auth, $sub_menu, 'w');

check_admin_token();

$post_chk       = isset($_POST['chk']) ? (array) $_POST['chk'] : array();
$post_group_id  = isset($_POST['group_id']) ? (array) $_POST['group_id'] : array();
$act_button     = isset($_POST['act_button']) ? $_POST['act_button'] : '';

$chk_count = count($post_chk);

if (!$chk_count) {
    alert($act_button . '할 게시판그룹을 1개이상 선택해 주세요.');
}

if ($act_button === '선택수정') {
    // 최고관리자가 아니면 제출된 그룹을 모두 먼저 확인해, 하나라도 본인 그룹이 아니면 요청 전체를 거부한다.
    if ($is_admin != 'super') {
        for ($i = 0; $i < $chk_count; $i++) {
            $k = isset($post_chk[$i]) ? (int) $post_chk[$i] : 0;
            $chk_gr_id = isset($post_group_id[$k]) ? $post_group_id[$k] : '';
            if (!admin_group_owner_check($chk_gr_id)) {
                alert('게시판그룹을 관리할 권한이 없습니다.');
            }
        }
    }
} elseif ($act_button === '선택삭제') {
    // 그룹 생성과 같이 삭제도 최고관리자만 한다.
    if ($is_admin != 'super') {
        alert('게시판그룹 삭제는 최고관리자만 가능합니다.');
    }

    auth_check_menu($auth, $sub_menu, 'd');
} else {
    alert('올바른 방법으로 이용해 주십시오.');
}

for ($i = 0; $i < $chk_count; $i++) {
    $k              = isset($post_chk[$i]) ? (int) $post_chk[$i] : 0;
    $gr_id          = preg_replace('/[^a-z0-9_]/i', '', $post_group_id[$k]);
    $gr_subject     = isset($_POST['gr_subject'][$k]) ? addslashes(strip_tags(clean_xss_attributes(stripslashes($_POST['gr_subject'][$k])))) : '';
    $gr_admin       = isset($_POST['gr_admin'][$k]) ? strip_tags(clean_xss_attributes($_POST['gr_admin'][$k])) : '';
    $gr_device      = isset($_POST['gr_device'][$k]) ? clean_xss_tags($_POST['gr_device'][$k], 1, 1, 10) : '';
    $gr_use_access  = isset($_POST['gr_use_access'][$k]) ? (int) $_POST['gr_use_access'][$k] : 0;
    $gr_order       = isset($_POST['gr_order'][$k]) ? (int) $_POST['gr_order'][$k] : 0;

    if ($act_button == '선택수정') {
        // 그룹 관리자 지정은 최고관리자만 한다. 위임 관리자의 요청 값은 무시하고 기존 값(본인)을 유지한다.
        if ($is_admin != 'super') {
            $gr_admin = $member['mb_id'];
        }

        $sql = " update {$g5['group_table']}
                    set gr_subject    = '{$gr_subject}',
                        gr_device     = '" . sql_real_escape_string($gr_device) . "',
                        gr_admin      = '" . sql_real_escape_string($gr_admin) . "',
                        gr_use_access = '" . $gr_use_access . "',
                        gr_order      = '" . $gr_order . "'
                  where gr_id         = '{$gr_id}' ";
        if ($is_admin != 'super') {
            $sql .= " and gr_admin    = '" . sql_real_escape_string($member['mb_id']) . "' ";
        }
        sql_query($sql);
    } elseif ($act_button == '선택삭제') {
        $row = sql_fetch(" select count(*) as cnt from {$g5['board_table']} where gr_id = '$gr_id' ");
        if ($row['cnt']) {
            alert("이 그룹에 속한 게시판이 존재하여 게시판 그룹을 삭제할 수 없습니다.\\n\\n이 그룹에 속한 게시판을 먼저 삭제하여 주십시오.", './board_list.php?sfl=a.gr_id&amp;stx=' . $gr_id);
        }

        // 그룹 삭제
        sql_query(" delete from {$g5['group_table']} where gr_id = '$gr_id' ");

        // 그룹접근 회원 삭제
        sql_query(" delete from {$g5['group_member_table']} where gr_id = '$gr_id' ");
    }
}

run_event('admin_boardgroup_list_update', $act_button, $post_chk, $post_group_id, $qstr);

goto_url('./boardgroup_list.php?' . $qstr);
