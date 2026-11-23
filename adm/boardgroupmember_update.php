<?php
$sub_menu = "300200";
require_once './_common.php';


if ($w == '') {
    auth_check_menu($auth, $sub_menu, 'w');

    $mb = get_member($mb_id);
    if (empty($mb['mb_id'])) {
        alert('존재하지 않는 회원입니다.');
    }

    $gr = get_group($gr_id);
    if (empty($gr['gr_id'])) {
        alert('존재하지 않는 그룹입니다.');
    }

    if (!admin_group_owner_check($gr['gr_id'])) {
        alert('게시판그룹을 관리할 권한이 없습니다.');
    }

    $sql = " select count(*) as cnt
                from {$g5['group_member_table']}
                where gr_id = '{$gr_id}'
                and mb_id = '{$mb_id}' ";
    $row = sql_fetch($sql);
    if ($row['cnt']) {
        alert('이미 등록되어 있는 자료입니다.');
    } else {
        check_admin_token();

        $sql = " insert into {$g5['group_member_table']}
                    set gr_id = '" . sql_real_escape_string($gr['gr_id']) . "',
                         mb_id = '" . sql_real_escape_string($mb['mb_id']) . "',
                         gm_datetime = '" . G5_TIME_YMDHIS . "' ";
        sql_query($sql);
    }
} elseif ($w == 'd' || $w == 'ld') {
    auth_check_menu($auth, $sub_menu, 'd');

    $post_chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? $_POST['chk'] : array();
    $count = count($post_chk);
    if (!$count) {
        alert('삭제할 목록을 하나이상 선택해 주세요.');
    }

    check_admin_token();

    // 삭제 전에 모든 행의 그룹 소유자를 확인해, 하나라도 본인 그룹이 아니면 요청 전체를 거부한다.
    $delete_gm_ids = array();
    for ($i = 0; $i < $count; $i++) {
        $gm_id = isset($post_chk[$i]) ? (int) $post_chk[$i] : 0;
        $sql = " select * from {$g5['group_member_table']} where gm_id = '$gm_id' ";
        $gm = sql_fetch($sql);
        if (!(isset($gm['gm_id']) && $gm['gm_id'])) {
            if ($count == 1) {
                alert('존재하지 않는 자료입니다.');
            } else {
                continue;
            }
        }

        if (!admin_group_owner_check((string) $gm['gr_id'])) {
            alert('게시판그룹을 관리할 권한이 없습니다.');
        }

        $delete_gm_ids[] = $gm_id;
    }

    foreach ($delete_gm_ids as $gm_id) {
        $sql = " delete from {$g5['group_member_table']} where gm_id = '$gm_id' ";
        sql_query($sql);
    }
}

if ($w == 'ld') {
    goto_url('./boardgroupmember_list.php?gr_id=' . $gr_id);
} else {
    goto_url('./boardgroupmember_form.php?mb_id=' . $mb_id);
}
