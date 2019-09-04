<?php
$sub_menu = "300100";
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

check_admin_token();

if ($_POST['act_button'] == "선택수정") {

    auth_check($auth[$sub_menu], 'w');

    for ($i=0; $i<count($_POST['chk']); $i++) {

        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        if ($is_admin != 'super') {
            $sql = " select count(*) as cnt from {$g5['board_table']} a, {$g5['group_table']} b
                      where a.gr_id = '".sql_real_escape_string($_POST['gr_id'][$k])."'
                        and a.gr_id = b.gr_id
                        and b.gr_admin = '{$member['mb_id']}' ";
            $row = sql_fetch($sql);
            if (!$row['cnt'])
                alert('최고관리자가 아닌 경우 다른 관리자의 게시판('.$board_table[$k].')은 수정이 불가합니다.');
        }

        $p_bo_subject = is_array($_POST['bo_subject']) ? strip_tags($_POST['bo_subject'][$k]) : '';

        $sql = " update {$g5['board_table']}
                    set gr_id               = '".sql_real_escape_string(strip_tags($_POST['gr_id'][$k]))."',
                        bo_subject          = '".$p_bo_subject."',
                        bo_device           = '".sql_real_escape_string(strip_tags($_POST['bo_device'][$k]))."',
                        bo_skin             = '".sql_real_escape_string(strip_tags($_POST['bo_skin'][$k]))."',
                        bo_mobile_skin      = '".sql_real_escape_string(strip_tags($_POST['bo_mobile_skin'][$k]))."',
                        bo_read_point       = '".sql_real_escape_string(strip_tags($_POST['bo_read_point'][$k]))."',
                        bo_write_point      = '".sql_real_escape_string(strip_tags($_POST['bo_write_point'][$k]))."',
                        bo_comment_point    = '".sql_real_escape_string(strip_tags($_POST['bo_comment_point'][$k]))."',
                        bo_download_point   = '".sql_real_escape_string(strip_tags($_POST['bo_download_point'][$k]))."',
                        bo_use_search       = '".sql_real_escape_string(strip_tags($_POST['bo_use_search'][$k]))."',
                        bo_use_sns          = '".sql_real_escape_string(strip_tags($_POST['bo_use_sns'][$k]))."',
                        bo_order            = '".sql_real_escape_string(strip_tags($_POST['bo_order'][$k]))."'
                  where bo_table            = '".sql_real_escape_string($_POST['board_table'][$k])."' ";

        sql_query($sql);
    }

} else if ($_POST['act_button'] == "선택삭제") {

    if ($is_admin != 'super')
        alert('게시판 삭제는 최고관리자만 가능합니다.');

    auth_check($auth[$sub_menu], 'd');

    // _BOARD_DELETE_ 상수를 선언해야 board_delete.inc.php 가 정상 작동함
    define('_BOARD_DELETE_', true);

    for ($i=0; $i<count($_POST['chk']); $i++) {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        // include 전에 $bo_table 값을 반드시 넘겨야 함
        $tmp_bo_table = trim($_POST['board_table'][$k]);

        if( preg_match("/^[A-Za-z0-9_]+$/", $tmp_bo_table) ){
            include ('./board_delete.inc.php');
        }
    }


}

goto_url('./board_list.php?'.$qstr);
?>
