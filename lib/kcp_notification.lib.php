<?php
if (!defined('_GNUBOARD_')) exit;

// KVE-2026-2346: 테스트 통보도 발신지와 저장된 거래를 모두 검증한다.
function kcp_noti_request($post, $server, $default)
{
    if (!isset($server['REQUEST_METHOD']) || $server['REQUEST_METHOD'] !== 'POST') return false;
    // https://developer.kcp.co.kr/guide/webhook (2026-09-10 확인)
    $ips = !empty($default['de_card_test'])
        ? array('210.122.176.144')
        : array('103.215.144.173', '103.215.144.174', '210.122.72.173');
    if (!isset($server['REMOTE_ADDR']) || !in_array($server['REMOTE_ADDR'], $ips, true)) return false;
    // 전달 헤더나 로그인 세션은 PG 발신 인증에 사용하지 않는다.
    $patterns = array(
        'site_cd' => '/\A[A-Z0-9]{5}\z/', 'tno' => '/\A[0-9]{14}\z/',
        'order_no' => '/\A[1-9][0-9]{0,19}\z/', 'tx_cd' => '/\ATX0[0-7]\z/',
        'tx_tm' => '/\A[0-9]{14}\z/'
    );
    if (isset($post['tx_cd']) && $post['tx_cd'] === 'TX00') {
        $patterns += array('ipgm_mnyx' => '/\A[0-9]{1,12}\z/',
            'account' => '/\A[T]?[0-9]{1,19}\z/', 'noti_id' => '/\A[0-9]{20}\z/',
            'op_cd' => '/\A(?:50|13)\z/');
    }
    $data = array();
    foreach ($patterns as $name => $pattern) {
        if (!isset($post[$name]) || !is_string($post[$name]) || !preg_match($pattern, $post[$name])) return false;
        $data[$name] = $post[$name];
    }
    $time = $data['tx_tm'];
    if ((int)substr($time, 0, 4) < 1000 || !checkdate((int)substr($time, 4, 2), (int)substr($time, 6, 2), (int)substr($time, 0, 4))
        || substr($time, 8, 2) > 23 || substr($time, 10, 2) > 59 || substr($time, 12, 2) > 59) return false;
    $test = in_array($data['site_cd'], array('T0000', 'T0007'), true);
    if ($test !== !empty($default['de_card_test'])) return false;
    if (!$test && substr($data['site_cd'], 0, 2) !== 'SR') return false;
    if ($data['tx_cd'] === 'TX00') {
        // 주문 금액 컬럼은 signed INT이다. 변환 전에 범위를 검사한다.
        if ((float)$data['ipgm_mnyx'] < 1 || (float)$data['ipgm_mnyx'] > 2147483647) return false;
        $data['ipgm_mnyx'] = (string)(int)$data['ipgm_mnyx'];
    }
    return $data;
}

function kcp_noti_query($sql)
{
    $result = sql_query($sql, false);
    if (!$result) throw new Exception('KCP notification database failure');
    return $result;
}

function kcp_noti_rows($sql)
{
    $result = kcp_noti_query($sql);
    $rows = array();
    while ($row = sql_fetch_array($result)) $rows[] = $row;
    return $rows;
}

function kcp_noti_quote($value)
{
    return "'".sql_escape_string((string)$value)."'";
}

function kcp_noti_tables()
{
    global $g5;
    return array('order' => $g5['g5_shop_order_table'], 'personal' => $g5['g5_shop_personalpay_table'],
        'cart' => $g5['g5_shop_cart_table'],
        'event' => isset($g5['g5_shop_kcp_noti_table']) ? $g5['g5_shop_kcp_noti_table'] : G5_SHOP_TABLE_PREFIX.'kcp_noti');
}

function kcp_noti_process($data)
{
    $tables = kcp_noti_tables();
    $locked = false;
    try {
        // 기본 설치의 MyISAM에서도 다른 통보 및 관리자 UPDATE와 경쟁하지 않게 한다.
        // DDL은 관리자 DB 업그레이드에서만 실행하며 스키마/권한이 없으면 실패 응답한다.
        $locks = array();
        foreach ($tables as $table) $locks[] = '`'.$table.'` WRITE';
        kcp_noti_query('LOCK TABLES '.implode(', ', $locks));
        $locked = true;
        $success = kcp_noti_locked($data, $tables);
    } catch (Exception $e) {
        error_log('KCP notification: processing failed; reconciliation/retry required');
        $success = false;
    } catch (Throwable $e) {
        error_log('KCP notification: processing failed; reconciliation/retry required');
        $success = false;
    }
    if ($locked) {
        try {
            kcp_noti_query('UNLOCK TABLES');
        } catch (Exception $e) {
            $success = false;
        } catch (Throwable $e) {
            $success = false;
        }
    }
    return $success;
}

// 변경 전/후 값을 함께 기록한다. 재시도는 가산 SQL 대신 목표값을 대입한다.
function kcp_noti_step($table, $id_field, $row, $changes, $guards)
{
    $before = array();
    foreach (array_unique(array_merge(array($id_field), array_keys($changes), $guards)) as $field) {
        if (!array_key_exists($field, $row)) throw new Exception('Missing notification schema');
        $before[$field] = (string)$row[$field];
    }
    $after = $before;
    foreach ($changes as $field => $value) $after[$field] = (string)$value;
    return array('table' => $table, 'id_field' => $id_field, 'id' => (string)$row[$id_field],
        'before' => $before, 'after' => $after);
}

function kcp_noti_matches($row, $expected)
{
    foreach ($expected as $field => $value) {
        if (!array_key_exists($field, $row) || (string)$row[$field] !== $value) return false;
    }
    return true;
}

function kcp_noti_apply($plan, $tables)
{
    // 먼저 모든 행을 검사한다. 실패 후 외부에서 수정한 행을 과거 값으로 덮어쓰지 않는다.
    foreach ($plan as $step) {
        $rows = kcp_noti_rows('SELECT * FROM `'.$tables[$step['table']].'` WHERE `'.$step['id_field'].'` = '.kcp_noti_quote($step['id']));
        if (count($rows) !== 1 || (!kcp_noti_matches($rows[0], $step['before']) && !kcp_noti_matches($rows[0], $step['after']))) return false;
    }
    foreach ($plan as $step) {
        $sets = array();
        foreach ($step['after'] as $field => $value) {
            if ($value !== $step['before'][$field]) $sets[] = '`'.$field.'` = '.kcp_noti_quote($value);
        }
        if ($sets) kcp_noti_query('UPDATE `'.$tables[$step['table']].'` SET '.implode(', ', $sets)
            .' WHERE `'.$step['id_field'].'` = '.kcp_noti_quote($step['id']));
    }
    foreach ($plan as $step) {
        $rows = kcp_noti_rows('SELECT * FROM `'.$tables[$step['table']].'` WHERE `'.$step['id_field'].'` = '.kcp_noti_quote($step['id']));
        if (count($rows) !== 1 || !kcp_noti_matches($rows[0], $step['after'])) return false;
    }
    return true;
}

function kcp_noti_locked($data, $tables)
{
    $order_no = kcp_noti_quote($data['order_no']);
    $personal = kcp_noti_rows("SELECT * FROM `{$tables['personal']}` WHERE pp_id = $order_no");
    $orders = kcp_noti_rows("SELECT * FROM `{$tables['order']}` WHERE od_id = $order_no");
    // 동일 번호가 두 종류에 존재하면 임의로 한쪽을 선택하지 않는다.
    if ($personal && $orders) return false;
    $pp = $personal ? $personal[0] : null;
    if ($pp) {
        $prefix = 'pp'; $payment = $pp;
        $orders = $pp['od_id'] ? kcp_noti_rows("SELECT * FROM `{$tables['order']}` WHERE od_id = ".kcp_noti_quote($pp['od_id'])) : array();
        if ($pp['od_id'] && !$orders) return false;
    } else {
        if (!$orders) return false;
        $prefix = 'od'; $payment = $orders[0];
    }
    $od = $orders ? $orders[0] : null;
    if ($payment[$prefix.'_pg'] !== 'kcp' || $payment[$prefix.'_tno'] !== $data['tno']
        || empty($payment[$prefix.'_kcp_site_cd']) || $payment[$prefix.'_kcp_site_cd'] !== $data['site_cd']) return false;
    // 처리하지 않는 기존 에스크로 이벤트는 거래를 확인한 뒤 수신만 확인한다.
    if ($data['tx_cd'] !== 'TX00') return true;
    if ($payment[$prefix.'_settle_case'] !== '가상계좌') return false;
    if ($od && (bool)$od['od_test'] !== in_array($data['site_cd'], array('T0000', 'T0007'), true)) return false;
    $account_parts = preg_split('/\s+/', trim($payment[$prefix.'_bank_account']));
    if (end($account_parts) !== $data['account']) return false;

    $trade = hash('sha256', $data['site_cd'].'|'.$data['tno'].'|'.$data['order_no']);
    $key = hash('sha256', $trade.'|'.$data['noti_id'].'|'.$data['op_cd']);
    $payload = hash('sha256', $data['ipgm_mnyx'].'|'.$data['account']);
    $events = kcp_noti_rows("SELECT * FROM `{$tables['event']}` WHERE kn_trade = '$trade'");
    $deposit = null; $cancel = null; $active = false;
    $by_id = array();
    foreach ($events as $event) {
        if ($event['kn_key'] === $key) {
            if ($event['kn_payload'] !== $payload) return false;
            if ($event['kn_done']) return true;
            $plan = json_decode($event['kn_plan'], true);
            if (!is_array($plan) || !kcp_noti_apply($plan, $tables)) return false;
            kcp_noti_query("UPDATE `{$tables['event']}` SET kn_done = 1 WHERE kn_key = '$key'");
            return true;
        }
        if (!$event['kn_done']) return false;
        $by_id[$event['kn_noti_id']][$event['kn_op_cd']] = $event;
        if ($event['kn_noti_id'] === $data['noti_id']) {
            if ($event['kn_payload'] !== $payload) return false;
            if ($event['kn_op_cd'] === '50') $deposit = $event;
            else $cancel = $event;
        }
    }
    foreach ($by_id as $pair) {
        if (isset($pair['50']) && !isset($pair['13'])) $active = true;
    }
    // 망취소가 먼저 도착했거나 취소 뒤 입금 통보가 재전송되어도 재입금하지 않는다.
    if ($data['op_cd'] === '50' && $cancel) return true;
    $canceling = $data['op_cd'] === '13';
    if (!$canceling && $active) return false;
    if ($canceling && !$deposit && $active) return false;
    if ($pp && !$pp['pp_use']) return false;
    $amount = (int)$data['ipgm_mnyx'];
    $receipt = (int)$payment[$prefix.'_receipt_price'];
    if ($pp && ((int)$pp['pp_price'] !== $amount || (int)$pp['pp_price'] <= 0)) return false;
    if (!$canceling && $receipt !== 0) return false;
    if ($canceling && $deposit && $receipt < $amount) return false;
    if ($canceling && !$deposit && $receipt !== 0) return false;

    $od_id = $od ? $od['od_id'] : '0';
    if ($od) {
        $pending = kcp_noti_rows("SELECT kn_key FROM `{$tables['event']}` WHERE od_id = ".kcp_noti_quote($od_id)." AND kn_done = 0");
        if ($pending) return false;
        $due = (int)$od['od_cart_price'] + (int)$od['od_send_cost'] + (int)$od['od_send_cost2']
            - (int)$od['od_cart_coupon'] - (int)$od['od_coupon'] - (int)$od['od_send_coupon']
            - (int)$od['od_cancel_price'] - (int)$od['od_receipt_point'] + (int)$od['od_refund_price'];
        $misu = $due - (int)$od['od_receipt_price'];
        if ($misu !== (int)$od['od_misu'] || $due <= 0) return false;
        if (!$pp && ($due !== $amount || (int)$od['od_cancel_price'] !== 0 || (int)$od['od_refund_price'] !== 0)) return false;
        if (!$canceling && ($od['od_status'] !== '주문' || $misu < $amount)) return false;
        if ($canceling && !in_array($od['od_status'], array('주문', '입금', '준비', '배송', '완료'), true)) return false;
        if ($canceling && $deposit && (int)$od['od_receipt_price'] < $amount) return false;
    }
    $time = substr($data['tx_tm'], 0, 4).'-'.substr($data['tx_tm'], 4, 2).'-'.substr($data['tx_tm'], 6, 2)
        .' '.substr($data['tx_tm'], 8, 2).':'.substr($data['tx_tm'], 10, 2).':'.substr($data['tx_tm'], 12, 2);
    $delta = $canceling ? ($deposit ? -$amount : 0) : $amount;
    $plan = array();
    if ($pp && $delta) {
        $plan[] = kcp_noti_step('personal', 'pp_id', $pp,
            array('pp_receipt_price' => $receipt + $delta, 'pp_receipt_time' => $time),
            array('od_id', 'pp_pg', 'pp_tno', 'pp_kcp_site_cd', 'pp_settle_case', 'pp_price', 'pp_use', 'pp_bank_account'));
    }
    if ($od && $delta) {
        $new_receipt = (int)$od['od_receipt_price'] + $delta;
        $new_misu = $due - $new_receipt;
        if ($new_receipt < 0 || $new_receipt > 2147483647 || $new_misu > 2147483647) return false;
        $status = $od['od_status'];
        if (!$canceling && $new_misu === 0) $status = '입금';
        if ($canceling && $status === '입금') $status = '주문';
        $cart = kcp_noti_rows("SELECT ct_id, od_id, ct_status FROM `{$tables['cart']}` WHERE od_id = ".kcp_noti_quote($od_id));
        if (!$cart) return false;
        foreach ($cart as $item) {
            if (!$canceling && $item['ct_status'] !== '주문') return false;
            if ($status !== $od['od_status'] && $item['ct_status'] === $od['od_status']) {
                $plan[] = kcp_noti_step('cart', 'ct_id', $item, array('ct_status' => $status), array('od_id'));
            }
        }
        // 마지막에 주문 상태를 변경하여 중간 실패 시 출고 판정 노출을 줄인다.
        $plan[] = kcp_noti_step('order', 'od_id', $od,
            array('od_receipt_price' => $new_receipt, 'od_receipt_time' => $time, 'od_misu' => $new_misu, 'od_status' => $status),
            array('od_pg', 'od_tno', 'od_kcp_site_cd', 'od_settle_case', 'od_test', 'od_cart_price', 'od_send_cost',
                'od_send_cost2', 'od_cart_coupon', 'od_coupon', 'od_send_coupon', 'od_cancel_price', 'od_receipt_point', 'od_refund_price'));
    }
    $json = json_encode($plan);
    if ($json === false) return false;
    kcp_noti_query("INSERT INTO `{$tables['event']}` SET kn_key = '$key', kn_trade = '$trade', kn_noti_id = ".kcp_noti_quote($data['noti_id'])
        .", kn_op_cd = ".kcp_noti_quote($data['op_cd']).", kn_payload = '$payload', od_id = ".kcp_noti_quote($od_id)
        .", kn_plan = ".kcp_noti_quote($json).", kn_created_at = ".kcp_noti_quote($time));
    if (!kcp_noti_apply($plan, $tables)) return false;
    kcp_noti_query("UPDATE `{$tables['event']}` SET kn_done = 1 WHERE kn_key = '$key'");
    return true;
}
