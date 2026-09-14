<?php
if (!defined('_GNUBOARD_')) exit;

function shop_order_cleanup($apply = false, $grace = 86400)
{
    global $g5;
    $cutoff = G5_SERVER_TIME - max(3600, (int)$grace);
    $table = shop_order_state_table();
    $rows = sql_query("select od_id from $table where (status in ('completed','failed','cancelled') and updated_at < $cutoff) or (status='pending' and pg in ('toss','kcp') and expires < $cutoff) order by od_id limit 500");
    $result = array();
    while ($candidate = sql_fetch_array($rows)) {
        $id = $candidate['od_id']; shop_order_state_lock('order', $id);
        $row = shop_order_state_row($id);
        if (!$row) { shop_order_state_unlock('order', $id); continue; }
        $safe = (in_array($row['status'], array('completed','failed','cancelled'), true) && (int)$row['updated_at'] < $cutoff) ||
            ($row['status'] === 'pending' && in_array($row['pg'], array('toss','kcp'), true) && (int)$row['expires'] < $cutoff);
        if (!$safe) { shop_order_state_unlock('order', $id); continue; }
        $result[] = array('order'=>$id, 'status'=>$row['status'], 'action'=>$apply ? 'purged' : 'would_purge');
        if ($apply) {
            $pg = sql_escape_string($row['pg']);
            if (!sql_query("delete from {$g5['g5_shop_order_data_table']} where od_id='$id' and dt_pg='$pg'", false)) shop_order_access_fail();
            shop_order_state_write($id, array('status'=>'purged','state_json'=>'','response_json'=>'','token_hash'=>'','payment_key'=>''));
        }
        shop_order_state_unlock('order', $id);
    }
    return $result;
}

function shop_order_quarantine_legacy($id)
{
    global $g5;
    shop_order_state_lock('order', $id);
    if (shop_order_state_row($id)) throw new RuntimeException('이미 상태 기록이 있는 주문입니다.');
    $res = sql_query("select * from {$g5['g5_shop_order_data_table']} where od_id='$id'");
    if (sql_num_rows($res) !== 1) throw new RuntimeException('임시 주문을 하나로 확정할 수 없습니다.');
    $row = sql_fetch_array($res);
    $data = shop_order_decode_data($row['dt_data']);
    if (!is_array($data)) throw new RuntimeException('임시 데이터 형식을 확인해 주십시오.');
    $password_hash = isset($data['od_pwd']) && is_string($data['od_pwd']) ? get_encrypt_string($data['od_pwd']) : '';
    unset($data['od_pwd']);
    $data = shop_order_filter_data($data, $row['dt_pg']);
    $encoded = base64_encode(serialize($data));
    // 먼저 접근 불가능한 상태를 기록한다. 세션이 남아 있어도 자동 전환하지 않는다.
    $state = sql_escape_string(json_encode(array('password_hash'=>$password_hash, 'legacy'=>true)));
    $pg = sql_escape_string($row['dt_pg']);
    if (!sql_query("insert into ".shop_order_state_table()." set od_id='$id',pg='$pg',status='legacy',state_json='$state',response_json='',updated_at=".G5_SERVER_TIME, false)) shop_order_access_fail();
    if (!sql_query("update {$g5['g5_shop_order_data_table']} set dt_data='$encoded' where od_id='$id'", false)) shop_order_access_fail();
    return array('order'=>$id, 'status'=>'legacy', 'plaintext_removed'=>true, 'token_issued'=>false);
}

function shop_order_reconcile_toss($id, $toss)
{
    global $g5;
    shop_order_state_lock('order', $id);
    $row = shop_order_state_row($id);
    $state = $row ? json_decode($row['state_json'], true) : null;
    if (!$row || $row['pg'] !== 'toss' || empty($state['expected_amount']) || $row['payment_key'] === '' ||
        !in_array($row['status'], array('approving','approved','finalizing','unknown','cancel_pending'), true)) throw new RuntimeException('승인 대조가 필요한 Toss 주문이 아닙니다.');
    if (!$toss->getPaymentByOrderId($id)) throw new RuntimeException('PG 조회 실패: 상태를 변경하지 않았습니다.');
    $p = $toss->responseData;
    if (!isset($p['orderId'],$p['paymentKey'],$p['totalAmount'],$p['status']) || $p['orderId'] !== $id ||
        $p['paymentKey'] !== $row['payment_key'] || (int)$p['totalAmount'] !== (int)$state['expected_amount']) throw new RuntimeException('PG 주문번호·키·금액이 일치하지 않습니다.');
    if ($p['status'] === 'CANCELED') {
        shop_order_state_write($id, array('status'=>'cancelled'));
        return array('order'=>$id, 'status'=>'cancelled');
    }
    if (!($p['status'] === 'DONE' || ($p['status'] === 'WAITING_FOR_DEPOSIT' && isset($p['method']) && $p['method'] === '가상계좌'))) throw new RuntimeException('PG 거래를 재승인하지 말고 별도 확인해 주십시오.');
    $saved = $state['personal'] ? sql_fetch("select pp_tno as tno from {$g5['g5_shop_personalpay_table']} where pp_id='$id'") : sql_fetch("select od_tno as tno from {$g5['g5_shop_order_table']} where od_id='$id'");
    if (($state['personal'] && !empty($saved['tno'])) || (!$state['personal'] && $saved)) {
        shop_order_state_write($id, array('status'=>'finalizing'));
        return array('order'=>$id, 'status'=>'finalizing', 'action'=>'주문·장바구니·포인트·쿠폰의 부분 저장 대조 필요');
    }
    shop_order_state_approved($id, $p);
    return array('order'=>$id, 'status'=>'approved', 'action'=>'원래 복귀 토큰으로 재시도 가능, 신규 승인 호출 없음');
}
