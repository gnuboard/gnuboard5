<?php
if (!defined('_GNUBOARD_')) exit;
include_once(dirname(__FILE__).'/shop_order_compat.lib.php');

function lg_noti_query($sql)
{
    $result = sql_query($sql, false);
    if (!$result) {
        throw new Exception('LG notification database failure');
    }
    return $result;
}

function lg_noti_rows($sql)
{
    $result = lg_noti_query($sql);
    $rows = array();
    while ($row = sql_fetch_array($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function lg_noti_quote($value)
{
    return "'".sql_escape_string((string) $value)."'";
}

function lg_noti_tables()
{
    global $g5;
    return array('order' => $g5['g5_shop_order_table'], 'personal' => $g5['g5_shop_personalpay_table'],
        'cart' => $g5['g5_shop_cart_table'],
        'event' => isset($g5['g5_shop_lg_noti_table']) ? $g5['g5_shop_lg_noti_table'] : G5_SHOP_TABLE_PREFIX.'lg_noti');
}

function lg_noti_process($data)
{
    $tables = lg_noti_tables();
    $locked = false;
    try {
        // 기본 설치의 MyISAM에서도 다른 통보 및 관리자 UPDATE와 경쟁하지 않게 한다.
        // DDL은 관리자 DB 업그레이드에서만 실행하며 스키마/권한이 없으면 실패 응답한다.
        $locks = array();
        foreach ($tables as $table) {
            $locks[] = '`'.$table.'` WRITE';
        }
        lg_noti_query('LOCK TABLES '.implode(', ', $locks));
        $locked = true;
        $success = lg_noti_locked($data, $tables);
    } catch (Exception $e) {
        error_log('LG notification: processing failed; reconciliation/retry required');
        $success = false;
    } catch (Throwable $e) {
        error_log('LG notification: processing failed; reconciliation/retry required');
        $success = false;
    }
    if ($locked) {
        try {
            lg_noti_query('UNLOCK TABLES');
        } catch (Exception $e) {
            $success = false;
        } catch (Throwable $e) {
            $success = false;
        }
    }
    return $success;
}

// 변경 전/후 값을 함께 기록한다. 재시도는 가산 SQL 대신 목표값을 대입한다.
function lg_noti_step($table, $id_field, $row, $changes, $guards)
{
    $before = array();
    foreach (array_unique(array_merge(array($id_field), array_keys($changes), $guards)) as $field) {
        if (!array_key_exists($field, $row)) {
            throw new Exception('Missing notification schema');
        }
        $before[$field] = (string) $row[$field];
    }
    $after = $before;
    foreach ($changes as $field => $value) {
        $after[$field] = (string) $value;
    }
    return array('table' => $table, 'id_field' => $id_field, 'id' => (string) $row[$id_field],
        'before' => $before, 'after' => $after);
}

function lg_noti_matches($row, $expected)
{
    foreach ($expected as $field => $value) {
        if (!array_key_exists($field, $row) || (string) $row[$field] !== $value) {
            return false;
        }
    }
    return true;
}

function lg_noti_apply($plan, $tables)
{
    // 먼저 모든 행을 검사한다. 실패 후 외부에서 수정한 행을 과거 값으로 덮어쓰지 않는다.
    foreach ($plan as $step) {
        $rows = lg_noti_rows('SELECT * FROM `'.$tables[$step['table']].'` WHERE `'.$step['id_field'].'` = '.lg_noti_quote($step['id']));
        if (count($rows) !== 1 || (!lg_noti_matches($rows[0], $step['before']) && !lg_noti_matches($rows[0], $step['after']))) {
            return false;
        }
    }
    foreach ($plan as $step) {
        $sets = array();
        foreach ($step['after'] as $field => $value) {
            if ($value !== $step['before'][$field]) {
                $sets[] = '`'.$field.'` = '.lg_noti_quote($value);
            }
        }
        if ($sets) {
            $sql = 'UPDATE `'.$tables[$step['table']].'` SET '.implode(', ', $sets)
                .' WHERE `'.$step['id_field'].'` = '.lg_noti_quote($step['id']);
            lg_noti_query($sql);
        }
    }
    foreach ($plan as $step) {
        $rows = lg_noti_rows('SELECT * FROM `'.$tables[$step['table']].'` WHERE `'.$step['id_field'].'` = '.lg_noti_quote($step['id']));
        if (count($rows) !== 1 || !lg_noti_matches($rows[0], $step['after'])) {
            return false;
        }
    }
    return true;
}

// XPay 공통사항 6.2 및 가상계좌 가이드(2026-09-18 확인).
// MD5에는 CASFLAG/TID/순번이 포함되지 않으므로 발신지 검사도 반드시 적용한다.
function lg_noti_request($post, $server, $config)
{
    $ips = array('13.124.18.147', '13.124.108.35', '3.36.173.151', '3.38.81.32',
        '115.92.221.121', '115.92.221.122', '115.92.221.123', '115.92.221.125', '115.92.221.126', '115.92.221.127');
    if (!isset($server['REQUEST_METHOD'], $server['REMOTE_ADDR']) || $server['REQUEST_METHOD'] !== 'POST'
        || !in_array($server['REMOTE_ADDR'], $ips, true)) return false;
    // 프록시 전달 헤더, 현재 로그인/활성 PG 설정은 통보 인증에 사용하지 않는다.
    if (!isset($config['cf_lg_mid'], $config['cf_lg_mert_key'])
        || !is_string($config['cf_lg_mid']) || !preg_match('/\A[A-Za-z0-9_-]{1,100}\z/', $config['cf_lg_mid'])
        || !is_string($config['cf_lg_mert_key']) || trim($config['cf_lg_mert_key']) === '') return false;
    $patterns = array(
        'LGD_MID' => '/\A[A-Za-z0-9_-]{1,104}\z/', 'LGD_OID' => '/\A[1-9][0-9]{0,19}\z/',
        'LGD_TID' => '/\A[A-Za-z0-9_-]{1,64}\z/', 'LGD_RESPCODE' => '/\A0000\z/',
        'LGD_PAYTYPE' => '/\ASC0040\z/', 'LGD_HASHDATA' => '/\A[a-fA-F0-9]{32}\z/',
        'LGD_TIMESTAMP' => '/\A[0-9]{14}\z/', 'LGD_PAYDATE' => '/\A[0-9]{14}\z/',
        'LGD_AMOUNT' => '/\A[0-9]{1,12}\z/', 'LGD_CASTAMOUNT' => '/\A[0-9]{1,12}\z/',
        'LGD_CASCAMOUNT' => '/\A[0-9]{1,12}\z/', 'LGD_CASFLAG' => '/\A[RIC]\z/',
        'LGD_CASSEQNO' => '/\A[0-9]{1,6}\z/', 'LGD_ACCOUNTNUM' => '/\A[A-Za-z0-9-]{1,30}\z/'
    );
    $data = array();
    foreach ($patterns as $field => $pattern) {
        if (!isset($post[$field]) || !is_string($post[$field]) || !preg_match($pattern, $post[$field])) return false;
        $data[$field] = $post[$field];
    }
    $mid = 'si_'.$config['cf_lg_mid'];
    if (!shop_order_equals($mid, $data['LGD_MID']) && !shop_order_equals('t'.$mid, $data['LGD_MID'])) return false;
    $hash = md5($data['LGD_MID'].$data['LGD_OID'].$data['LGD_AMOUNT'].$data['LGD_RESPCODE'].$data['LGD_TIMESTAMP'].$config['cf_lg_mert_key']);
    if (!shop_order_equals($hash, strtolower($data['LGD_HASHDATA']))) return false;
    foreach (array('LGD_TIMESTAMP', 'LGD_PAYDATE') as $field) {
        $time = $data[$field];
        if ((int)substr($time, 0, 4) < 1000 || !checkdate((int)substr($time, 4, 2), (int)substr($time, 6, 2), (int)substr($time, 0, 4))
            || substr($time, 8, 2) > 23 || substr($time, 10, 2) > 59 || substr($time, 12, 2) > 59) return false;
    }
    // 수동 재통보를 막는 임의의 timestamp 수명 제한 대신 거래별 처리 이력을 확인한다.
    foreach (array('LGD_AMOUNT', 'LGD_CASTAMOUNT', 'LGD_CASCAMOUNT') as $field) {
        if ((float)$data[$field] > 2147483647) return false;
        $data[$field] = (int)$data[$field];
    }
    if ($data['LGD_AMOUNT'] <= 0) return false;
    $flag = $data['LGD_CASFLAG'];
    if ($flag === 'R') {
        if ($data['LGD_CASTAMOUNT'] !== 0 || $data['LGD_CASCAMOUNT'] !== 0) return false;
    } else {
        if ((int)$data['LGD_CASSEQNO'] === 0) return false;
        // 기본 통합결제창의 일반형 가상계좌는 전액 일회성 입금이다.
        if ($flag === 'I' && ($data['LGD_CASTAMOUNT'] !== $data['LGD_AMOUNT'] || $data['LGD_CASCAMOUNT'] !== $data['LGD_AMOUNT'])) return false;
        if ($flag === 'C' && ($data['LGD_CASTAMOUNT'] !== 0 || !in_array($data['LGD_CASCAMOUNT'], array(0, $data['LGD_AMOUNT']), true))) return false;
    }
    return $data;
}

function lg_noti_locked($data, $tables)
{
    $id = lg_noti_quote($data['LGD_OID']);
    $personal = lg_noti_rows("SELECT * FROM `{$tables['personal']}` WHERE pp_id = $id");
    $orders = lg_noti_rows("SELECT * FROM `{$tables['order']}` WHERE od_id = $id");
    if (count($personal) > 1 || count($orders) > 1 || ($personal && $orders)) return false;
    $pp = $personal ? $personal[0] : null;
    if ($pp) {
        $prefix = 'pp'; $payment = $pp;
        $orders = $pp['od_id'] ? lg_noti_rows("SELECT * FROM `{$tables['order']}` WHERE od_id = ".lg_noti_quote($pp['od_id'])) : array();
        if ($pp['od_id'] && count($orders) !== 1) return false;
    } else {
        if (!$orders) return false;
        $prefix = 'od'; $payment = $orders[0];
    }
    $od = $orders ? $orders[0] : null;
    if ($payment[$prefix.'_pg'] !== 'lg' || $payment[$prefix.'_settle_case'] !== '가상계좌'
        || $payment[$prefix.'_tno'] !== $data['LGD_TID'] || empty($payment[$prefix.'_lg_mid'])
        || !shop_order_equals($payment[$prefix.'_lg_mid'], $data['LGD_MID'])) return false;
    if ($od && (bool)$od['od_test'] !== (substr($data['LGD_MID'], 0, 1) === 't')) return false;
    // 저장 형식: 은행명 계좌번호 예금주명. 부분 문자열 일치는 허용하지 않는다.
    if (!in_array($data['LGD_ACCOUNTNUM'], preg_split('/\s+/', trim($payment[$prefix.'_bank_account'])), true)) return false;
    if ($data['LGD_CASFLAG'] === 'R') return true; // 발급 통보로 입금 처리하지 않는다.
    return lg_noti_deposit($data, $tables, $payment, $prefix, $pp, $od);
}

function lg_noti_deposit($data, $tables, $payment, $prefix, $pp, $od)
{
    $trade = hash('sha256', $data['LGD_MID'].'|'.$data['LGD_TID'].'|'.$data['LGD_OID']);
    $seq = (int)$data['LGD_CASSEQNO'];
    $flag = $data['LGD_CASFLAG'];
    $key = hash('sha256', $trade.'|'.$seq.'|'.$flag);
    // 재전송 timestamp는 달라질 수 있다. 업무 값을 바꾼 동일 이벤트는 거부한다.
    $payload = hash('sha256', $data['LGD_AMOUNT'].'|'.$data['LGD_CASTAMOUNT'].'|'.$data['LGD_CASCAMOUNT'].'|'.$data['LGD_ACCOUNTNUM']);
    $events = lg_noti_rows("SELECT * FROM `{$tables['event']}` WHERE ln_trade = '$trade'");
    $same = null; $latest = null;
    foreach ($events as $event) {
        if ($event['ln_key'] === $key) $same = $event;
        if (!$latest || (int)$event['ln_seq'] > (int)$latest['ln_seq']
            || ((int)$event['ln_seq'] === (int)$latest['ln_seq'] && $event['ln_flag'] === 'C')) $latest = $event;
        if (!$event['ln_done'] && $event['ln_key'] !== $key) return false;
    }
    if ($same) {
        if ($same['ln_payload'] !== $payload) return false;
        if ($same['ln_done']) return true;
        $plan = json_decode($same['ln_plan'], true);
        if (!is_array($plan) || !lg_noti_apply($plan, $tables)) return false;
        lg_noti_query("UPDATE `{$tables['event']}` SET ln_done = 1 WHERE ln_key = '$key'");
        return true;
    }
    // 은행 취소가 먼저 와도 같은/과거 입금 순번으로 다시 입금시키지 않는다.
    if ($latest && ($seq < (int)$latest['ln_seq'] || ($seq === (int)$latest['ln_seq'] && $latest['ln_flag'] === 'C'))) return true;
    $canceling = $flag === 'C';
    // 사용 중지는 신규 입금만 막는다. 이미 수납한 거래의 은행 취소는 반영해야 한다.
    if ($pp && !$pp['pp_use'] && !$canceling) return false;
    $amount = $data['LGD_AMOUNT'];
    $receipt = (int)$payment[$prefix.'_receipt_price'];
    $active = $latest && $latest['ln_flag'] === 'I';
    if ($pp && (int)$pp['pp_price'] !== $amount) return false;
    if (!$canceling && ($active || $receipt !== 0)) return false;
    // 이력 없는 기존 입금은 자동으로 취소하거나 기처리로 추정하지 않는다.
    if ($canceling && $receipt !== ($active ? $amount : 0)) return false;
    $delta = $canceling ? ($active ? -$amount : 0) : $amount;
    $od_id = $od ? $od['od_id'] : '0';
    // 관리자가 연결 주문을 바꿔도 이전 입금의 취소액을 새 주문에서 차감하지 않는다.
    // 연결 해제/추가도 충돌이며 원장 대조 후 원래 연결을 복원하고 재통보한다.
    if ($pp && $canceling && $active && (string)$latest['od_id'] !== (string)$od_id) {
        error_log('LG notification: personal payment order binding changed; reconciliation required');
        return false;
    }
    if ($od) {
        $pending = lg_noti_rows("SELECT ln_key FROM `{$tables['event']}` WHERE od_id = ".lg_noti_quote($od_id)." AND ln_done = 0");
        if ($pending) return false;
        $due = (int)$od['od_cart_price'] + (int)$od['od_send_cost'] + (int)$od['od_send_cost2']
            - (int)$od['od_cart_coupon'] - (int)$od['od_coupon'] - (int)$od['od_send_coupon']
            - (int)$od['od_cancel_price'] - (int)$od['od_receipt_point'] + (int)$od['od_refund_price'];
        $misu = $due - (int)$od['od_receipt_price'];
        if ($due <= 0 || $due > 2147483647 || $misu !== (int)$od['od_misu']) return false;
        if (!$pp && ($due !== $amount || (int)$od['od_cancel_price'] !== 0 || (int)$od['od_refund_price'] !== 0)) return false;
        if (!$canceling && ($od['od_status'] !== '주문' || $misu < $amount)) return false;
        if ($canceling && (!in_array($od['od_status'], array('주문', '입금', '준비', '배송', '완료'), true)
            || (int)$od['od_receipt_price'] + $delta < 0)) return false;
    }
    $raw_time = $data['LGD_PAYDATE'];
    $time = substr($raw_time, 0, 4).'-'.substr($raw_time, 4, 2).'-'.substr($raw_time, 6, 2)
        .' '.substr($raw_time, 8, 2).':'.substr($raw_time, 10, 2).':'.substr($raw_time, 12, 2);
    $plan = array();
    if ($pp && $delta) {
        $plan[] = lg_noti_step('personal', 'pp_id', $pp,
            array('pp_receipt_price'=>$receipt + $delta, 'pp_receipt_time'=>$time, 'pp_casseqno'=>$data['LGD_CASSEQNO']),
            array('od_id', 'pp_pg', 'pp_tno', 'pp_lg_mid', 'pp_settle_case', 'pp_price', 'pp_use', 'pp_bank_account'));
    }
    if ($od && $delta) {
        $new_receipt = (int)$od['od_receipt_price'] + $delta;
        $new_misu = $due - $new_receipt;
        if ($new_receipt < 0 || $new_receipt > 2147483647 || $new_misu < 0 || $new_misu > 2147483647) return false;
        $status = $od['od_status'];
        if (!$canceling && $new_misu === 0) $status = '입금';
        if ($canceling && in_array($status, array('입금', '준비'), true)) $status = '주문';
        $cart = lg_noti_rows("SELECT ct_id, od_id, ct_status FROM `{$tables['cart']}` WHERE od_id = ".lg_noti_quote($od_id));
        if (!$cart) return false;
        foreach ($cart as $item) {
            if (!$canceling && $item['ct_status'] !== '주문') return false;
            if ($status !== $od['od_status'] && ($item['ct_status'] === $od['od_status']
                || ($canceling && in_array($item['ct_status'], array('입금', '준비'), true)))) {
                $plan[] = lg_noti_step('cart', 'ct_id', $item, array('ct_status'=>$status), array('od_id'));
            }
        }
        // 출고에 사용하는 주문 상태는 장바구니 반영 뒤 마지막에 바꾼다.
        $plan[] = lg_noti_step('order', 'od_id', $od,
            array('od_receipt_price'=>$new_receipt, 'od_receipt_time'=>$time, 'od_misu'=>$new_misu,
                'od_status'=>$status, 'od_casseqno'=>$data['LGD_CASSEQNO']),
            array('od_pg', 'od_tno', 'od_lg_mid', 'od_settle_case', 'od_test', 'od_bank_account', 'od_cart_price',
                'od_send_cost', 'od_send_cost2', 'od_cart_coupon', 'od_coupon', 'od_send_coupon', 'od_cancel_price', 'od_receipt_point', 'od_refund_price'));
    }
    $json = json_encode($plan);
    if ($json === false) return false;
    lg_noti_query("INSERT INTO `{$tables['event']}` SET ln_key='$key', ln_trade='$trade', ln_seq='$seq', ln_flag='$flag', ln_payload='$payload', od_id=".lg_noti_quote($od_id)
        .", ln_plan=".lg_noti_quote($json).", ln_created_at=".lg_noti_quote($time));
    if (!lg_noti_apply($plan, $tables)) return false;
    lg_noti_query("UPDATE `{$tables['event']}` SET ln_done = 1 WHERE ln_key = '$key'");
    if ($canceling && $od && in_array($od['od_status'], array('배송', '완료'), true)) {
        error_log('LG notification: bank cancellation after shipment; operator reconciliation required');
    }
    return true;
}
