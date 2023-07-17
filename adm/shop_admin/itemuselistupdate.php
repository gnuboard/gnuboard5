<?php
$sub_menu = '400650';
include_once('./_common.php');

check_demo();

check_admin_token();

$count_post_chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;

if (! $count_post_chk) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

if ($_POST['act_button'] === "선택수정") {
    auth_check_menu($auth, $sub_menu, 'w');
} else if ($_POST['act_button'] === "선택삭제") {
    auth_check_menu($auth, $sub_menu, 'd');
} else {
    alert("선택수정이나 선택삭제 작업이 아닙니다.");
}

for ($i=0; $i<$count_post_chk; $i++)
{
    $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0; // 실제 번호를 넘김
    $iit_id = isset($_POST['it_id'][$k]) ? preg_replace('/[^a-z0-9_\-]/i', '', $_POST['it_id'][$k]) : '';
    $iis_id = isset($_POST['is_id'][$k]) ? (int) $_POST['is_id'][$k] : 0;
    $iis_score = isset($_POST['is_score'][$k]) ? (int) $_POST['is_score'][$k] : 0;
    $iis_confirm = isset($_POST['is_confirm'][$k]) ? (int) $_POST['is_confirm'][$k] : 0;

    if ($_POST['act_button'] == "선택수정")
    {
        $sql = "update {$g5['g5_shop_item_use_table']}
                   set is_score   = '{$iis_score}',
                       is_confirm = '{$iis_confirm}'
                 where is_id      = '{$iis_id}' ";
        sql_query($sql);
    }
    else if ($_POST['act_button'] == "선택삭제")
    {
        $sql = "delete from {$g5['g5_shop_item_use_table']} where is_id = '{$iis_id}' ";
        sql_query($sql);
        run_event('shop_admin_item_use_deleted', $iis_id);
    }
    
    if($iit_id){
        update_use_cnt($iit_id);
        update_use_avg($iit_id);
    }
}

goto_url("./itemuselist.php?sca=$sca&amp;sst=$sst&amp;sod=$sod&amp;sfl=$sfl&amp;stx=$stx&amp;page=$page");