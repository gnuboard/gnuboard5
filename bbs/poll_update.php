<?php
include_once('./_common.php');

$po_id = isset($_POST['po_id']) ? preg_replace('/[^0-9]/', '', $_POST['po_id']) : 0;

$po = sql_fetch(" select * from {$g5['poll_table']} where po_id = '$po_id' ");
if (! (isset($po['po_id']) && $po['po_id']))
    alert('po_id 값이 제대로 넘어오지 않았습니다.');

if ($member['mb_level'] < $po['po_level'])
    alert_close('권한 '.$po['po_level'].' 이상 회원만 투표에 참여하실 수 있습니다.');

$gb_poll = isset($_POST['gb_poll']) ? preg_replace('/[^0-9]/', '', $_POST['gb_poll']) : 0;
if(!$gb_poll)
    alert_close('항목을 선택하세요.');

$post_skin_dir = isset($_POST['skin_dir']) ? clean_xss_tags($_POST['skin_dir'], 1, 1) : '';
$result_url = G5_BBS_URL."/poll_result.php?po_id=$po_id&skin_dir={$post_skin_dir}";

// 레이스 컨디션 방지: MyISAM은 트랜잭션을 지원하지 않으므로,
// WHERE 조건에 중복 검증(FIND_IN_SET)을 포함한 원자적 UPDATE로 처리한다.
// 기존 투표자 목록에 없을 때만 갱신되며, 동시 요청이 와도 DB 서버 레벨에서 직렬화된다.
if ($is_member) {
    $sql = " update {$g5['poll_table']}
                set po_cnt{$gb_poll} = po_cnt{$gb_poll} + 1,
                    mb_ids = concat(ifnull(mb_ids, ''), '".$member['mb_id'].",')
              where po_id = '$po_id'
                and find_in_set('".$member['mb_id']."', ifnull(mb_ids, '')) = 0 ";
} else {
    $sql = " update {$g5['poll_table']}
                set po_cnt{$gb_poll} = po_cnt{$gb_poll} + 1,
                    po_ips = concat(ifnull(po_ips, ''), '".$_SERVER['REMOTE_ADDR'].",')
              where po_id = '$po_id'
                and find_in_set('".$_SERVER['REMOTE_ADDR']."', ifnull(po_ips, '')) = 0 ";
}
sql_query($sql);

if (get_sql_affected_rows() <= 0) {
    alert(addcslashes($po['po_subject'], '"\\/').'에 이미 참여하셨습니다.', $result_url);
}

insert_point($member['mb_id'], $po['po_point'], $po['po_id'] . '. ' . cut_str($po['po_subject'],20) . ' 투표 참여 ', '@poll', $po['po_id'], '투표');

//goto_url($g5['bbs_url'].'/poll_result.php?po_id='.$po_id.'&amp;skin_dir='.$skin_dir);
goto_url($result_url);