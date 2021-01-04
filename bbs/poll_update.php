<?php
include_once('./_common.php');

$po_id = isset($_POST['po_id']) ? preg_replace('/[^0-9]/', '', $_POST['po_id']) : 0;

$po = sql_fetch(" select * from {$g5['poll_table']} where po_id = '{$_POST['po_id']}' ");
if (! (isset($po['po_id']) && $po['po_id']))
    alert('po_id 값이 제대로 넘어오지 않았습니다.');

if ($member['mb_level'] < $po['po_level'])
    alert_close('권한 '.$po['po_level'].' 이상 회원만 투표에 참여하실 수 있습니다.');

$gb_poll = isset($_POST['gb_poll']) ? preg_replace('/[^0-9]/', '', $_POST['gb_poll']) : 0;
if(!$gb_poll)
    alert_close('항목을 선택하세요.');

$search_mb_id = false;
$search_ip = false;

if($is_member) {
    // 투표했던 회원아이디들 중에서 찾아본다
    $ids = explode(',', trim($po['mb_ids']));
    for ($i=0; $i<count($ids); $i++) {
        if ($member['mb_id'] == trim($ids[$i])) {
            $search_mb_id = true;
            break;
        }
    }
} else {
    // 투표했던 ip들 중에서 찾아본다
    $ips = explode(',', trim($po['po_ips']));
    for ($i=0; $i<count($ips); $i++) {
        if ($_SERVER['REMOTE_ADDR'] == trim($ips[$i])) {
            $search_ip = true;
            break;
        }
    }
}

$post_skin_dir = isset($_POST['skin_dir']) ? clean_xss_tags($_POST['skin_dir'], 1, 1) : '';
$result_url = G5_BBS_URL."/poll_result.php?po_id=$po_id&skin_dir={$post_skin_dir}";

// 없다면 선택한 투표항목을 1증가 시키고 ip, id를 저장
if (!($search_ip || $search_mb_id)) {
    $po_ips = $po['po_ips'] . $_SERVER['REMOTE_ADDR'].",";
    $mb_ids = $po['mb_ids'];
    if ($is_member) { // 회원일 때는 id만 추가
        $mb_ids .= $member['mb_id'].',';
        $sql = " update {$g5['poll_table']} set po_cnt{$gb_poll} = po_cnt{$gb_poll} + 1, mb_ids = '$mb_ids' where po_id = '$po_id' ";
    } else {
        $sql = " update {$g5['poll_table']} set po_cnt{$gb_poll} = po_cnt{$gb_poll} + 1, po_ips = '$po_ips' where po_id = '$po_id' ";
    }

    sql_query($sql);
} else {
    alert(addcslashes($po['po_subject'], '"\\/').'에 이미 참여하셨습니다.', $result_url);
}

if (!$search_mb_id)
    insert_point($member['mb_id'], $po['po_point'], $po['po_id'] . '. ' . cut_str($po['po_subject'],20) . ' 투표 참여 ', '@poll', $po['po_id'], '투표');

//goto_url($g5['bbs_url'].'/poll_result.php?po_id='.$po_id.'&amp;skin_dir='.$skin_dir);
goto_url($result_url);