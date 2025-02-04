<?php
include_once('./_common.php');

$pay_id = isset($_REQUEST['pay_id']) ? safe_replace_regex($_REQUEST['pay_id'], 'pay_id') : '';

if (!$pay_id) {
    die('');
}

$sql_wheres = array('id' => $pay_id);

if ($is_member && !$is_admin) {
    $sql_wheres['mb_id'] = $member['mb_id'];
}

$pays = sql_bind_select_fetch($g5['g5_subscription_pay_table'], '*', $sql_wheres);

if (!(isset($pays['id']) && $pays['id'])) {

    die(json_encode(array('error' => 1, 'msg'=>'조회 권한이 없습니다.')));
    
}

die(json_encode($pays));