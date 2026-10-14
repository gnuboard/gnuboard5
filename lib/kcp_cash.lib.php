<?php
if (!defined('_GNUBOARD_')) exit;

class KcpCashRequestException extends RuntimeException {}

// KVE-2026-2513: 현금영수증은 화면의 hidden 값이 아닌 주문 원장으로 발급한다.
function kcp_cash_fail($message = '현금영수증을 발급할 수 없는 요청입니다.')
{
    throw new KcpCashRequestException($message);
}

function kcp_cash_key($type, $id)
{
    return 'ss_kcp_cash_'.hash('sha256', $type.':'.$id.':pay');
}

function kcp_cash_token($type, $id)
{
    $token = bin2hex(random_bytes(32));
    set_session(kcp_cash_key($type, $id), array('token'=>$token, 'expires'=>time()+600));
    return $token;
}

function kcp_cash_request($post, $method)
{
    if ($method !== 'POST') kcp_cash_fail();
    foreach (array('req_tx', 'tx', 'ordr_idxx', 'cash_token', 'tr_code', 'id_info') as $name) {
        if (!isset($post[$name]) || !is_string($post[$name])) kcp_cash_fail();
    }
    // 이 엔드포인트의 유일한 호출자는 발급 화면이다. 조회/취소 API로 사용하지 않는다.
    if ($post['req_tx'] !== 'pay' || !in_array($post['tx'], array('order', 'personalpay'), true)) kcp_cash_fail();
    if (!preg_match('/\A[0-9A-Za-z_.-]{1,20}\z/D', $post['ordr_idxx'])) kcp_cash_fail();
    if (!preg_match('/\A[0-9a-f]{64}\z/D', $post['cash_token'])) kcp_cash_fail();
    if (!in_array($post['tr_code'], array('0', '1'), true)) kcp_cash_fail();
    $pattern = $post['tr_code'] === '0' ? '/\A(?:[0-9]{10,11}|[0-9]{13})\z/D' : '/\A[0-9]{10}\z/D';
    if (!preg_match($pattern, $post['id_info'])) kcp_cash_fail('현금영수증 식별번호를 확인해 주십시오.');
    return array_intersect_key($post, array_flip(array('req_tx','tx','ordr_idxx','cash_token','tr_code','id_info')));
}

function kcp_cash_source($type)
{
    global $g5;
    if ($type === 'personalpay') return array($g5['g5_shop_personalpay_table'], 'pp');
    if ($type === 'order') return array($g5['g5_shop_order_table'], 'od');
    kcp_cash_fail();
}

function kcp_cash_order($type, $id)
{
    list($table, $p) = kcp_cash_source($type);
    $row = sql_fetch("SELECT * FROM {$table} WHERE {$p}_id='".sql_escape_string($id)."'", false);
    if (!$row || empty($row[$p.'_id']) || !function_exists('is_shop_order_owner') || !is_shop_order_owner($row, $type)) kcp_cash_fail('해당 주문 정보에 접근 권한이 없습니다.');
    return $row;
}

function kcp_cash_data($row, $type)
{
    global $default;
    $p = $type === 'personalpay' ? 'pp' : 'od';
    if ((int)$row[$p.'_cash'] !== 0 || $row[$p.'_cash_no'] !== '' || $row[$p.'_cash_info'] !== '') kcp_cash_fail('이미 발급되었거나 처리 결과를 확인 중인 주문입니다. 관리자에게 문의해 주십시오.');
    $pg = $row[$p.'_pg'] !== '' ? $row[$p.'_pg'] : $default['de_pg_service'];
    if ($pg !== 'kcp' || empty($default['de_taxsave_use'])) kcp_cash_fail();
    $methods = array('무통장'=>'account', '계좌이체'=>'transfer', '가상계좌'=>'vbank');
    $method = $row[$p.'_settle_case'];
    if (!isset($methods[$method]) || strpos($default['de_taxsave_types'], $methods[$method]) === false) kcp_cash_fail();
    $total = (int)$row[$p.'_receipt_price'];
    if ($type === 'personalpay') {
        if (!empty($row['od_id'])) kcp_cash_fail('주문에 연결된 개인결제는 주문 내역에서 현금영수증을 확인해 주십시오.');
        if ($total <= 0 || $total !== (int)$row['pp_price']) kcp_cash_fail('개인결제 수납 금액을 확인해 주십시오.');
        $supply = (int)round($total / 1.1);
        $tax = $total - $supply;
        $name = $row['pp_name'].'님 개인결제';
    } else {
        $total -= (int)$row['od_refund_price'];
        if ($total <= 0 || (int)$row['od_misu'] > 0 || !in_array($row['od_status'], array('입금','준비','배송','완료'), true)) kcp_cash_fail('주문 수납 및 환불 상태를 확인해 주십시오.');
        $supply = (int)$row['od_tax_mny'] + (int)$row['od_free_mny'];
        $tax = (int)$row['od_vat_mny'];
        if ((int)$row['od_tax_mny'] < 0 || (int)$row['od_free_mny'] < 0 || $tax < 0 || $supply+$tax !== $total) kcp_cash_fail('수납 금액과 과세 정보가 일치하지 않습니다. 관리자에게 문의해 주십시오.');
        $goods = get_goods($row['od_id']);
        $name = $goods['full_name'];
    }
    return array('ordr_idxx'=>(string)$row[$p.'_id'], 'good_name'=>$name, 'buyr_name'=>$row[$p.'_name'],
        'buyr_mail'=>$row[$p.'_email'], 'buyr_tel1'=>$row[$p.($p==='pp' ? '_hp' : '_tel')],
        'amt_tot'=>$total, 'amt_sup'=>$supply, 'amt_tax'=>$tax, 'amt_svc'=>0, 'trad_time'=>date('YmdHis'));
}

// 발급 직전과 저장 직후의 금액/상태 경합도 검출한다. 외부 통신 중 DB 트랜잭션은 열지 않는다.
function kcp_cash_snapshot_where($row, $p)
{
    $fields = array('id','pg','settle_case','receipt_price');
    $fields = array_merge($fields, $p === 'pp' ? array('price') : array('refund_price','misu','status','tax_mny','vat_mny','free_mny'));
    $where = array();
    foreach ($fields as $field) $where[] = "{$p}_{$field}='".sql_escape_string((string)$row[$p.'_'.$field])."'";
    if ($p === 'od') $where[] = "mb_id='".sql_escape_string((string)$row['mb_id'])."'";
    // 개인결제에는 mb_id가 없고, 연결 주문이 없는 od_id는 정수 0이다.
    if ($p === 'pp') $where[] = "od_id=0";
    return implode(' AND ', $where);
}

function kcp_cash_update($table, $set, $where)
{
    try {
        $ok = sql_query("UPDATE {$table} SET {$set} WHERE {$where}", false);
        return $ok && (int)get_sql_affected_rows() === 1;
    } catch (Throwable $e) {
        return false;
    }
}

function kcp_cash_audit($attempt, $state)
{
    // 식별번호, 주문자, 가맹점 키 및 PG 원문 응답을 로그에 남기지 않는다.
    error_log('KCP cash '.$attempt.' '.$state);
}

function kcp_cash_matches($table, $where)
{
    try {
        $row = sql_fetch("SELECT 1 AS matched FROM {$table} WHERE {$where}", false);
        return $row && (int)$row['matched'] === 1;
    } catch (Throwable $e) {
        return false;
    }
}

function kcp_cash_process($request, $gateway)
{
    $type = $request['tx'];
    $id = $request['ordr_idxx'];
    $row = kcp_cash_order($type, $id);
    $data = kcp_cash_data($row, $type);
    $key = kcp_cash_key($type, $id);
    $session = get_session($key);
    if (!is_array($session) || !isset($session['expires'], $session['token']) || $session['expires'] < time() || !hash_equals($session['token'], $request['cash_token'])) kcp_cash_fail('요청이 만료되었습니다. 발급 화면을 다시 열어 주십시오.');
    set_session($key, '');
    list($table, $p) = kcp_cash_source($type);
    $attempt = bin2hex(random_bytes(16));
    $pending = serialize(array('kcp_cash_state'=>'pending', 'attempt'=>$attempt, 'requested_at'=>date('c'), 'amount'=>$data['amt_tot']));
    $pending_sql = sql_escape_string($pending);
    $snapshot = kcp_cash_snapshot_where($row, $p);
    if (!kcp_cash_update($table, "{$p}_cash_info='{$pending_sql}'", $snapshot." AND {$p}_cash=0 AND {$p}_cash_no='' AND {$p}_cash_info=''")) kcp_cash_fail('이미 처리 중이거나 주문 상태가 변경되었습니다.');
    // 프로세스 종료/타임아웃 때도 pending을 유지하여 불확실한 거래의 재발급을 차단한다.
    $owned = "{$p}_id='".sql_escape_string($id)."' AND {$p}_cash=0 AND {$p}_cash_no='' AND {$p}_cash_info='{$pending_sql}'";
    $data['tr_code'] = $request['tr_code'];
    $data['id_info'] = $request['id_info'];
    try { $response = call_user_func($gateway, 'pay', $data); }
    catch (Throwable $e) { $response = array(); }
    if (!is_array($response) || !isset($response['res_cd']) || $response['res_cd'] !== '0000' || empty($response['cash_no']) || !preg_match('/\A[0-9]{1,40}\z/D', (string)$response['cash_no'])) {
        kcp_cash_audit($attempt, 'reconcile_required');
        return array('state'=>'pending', 'message'=>'발급 결과 확인이 필요합니다. 재발급하지 말고 관리자에게 문의해 주십시오.', 'attempt'=>$attempt);
    }
    $cash = array('receipt_no'=>(string)($response['receipt_no'] ?? ''), 'app_time'=>(string)($response['app_time'] ?? ''),
        'reg_stat'=>(string)($response['reg_stat'] ?? ''), 'reg_desc'=>(string)($response['reg_desc'] ?? ''),
        'tr_code'=>$request['tr_code'], 'id_info'=>$request['id_info'], 'attempt'=>$attempt);
    $cash_no = sql_escape_string((string)$response['cash_no']);
    $cash_info = sql_escape_string(serialize($cash));
    // 기존 전표 링크에서도 사용하는 승인번호는 숫자만 저장한다.
    $valid_receipt = preg_match('/\A[0-9]{1,40}\z/D', $cash['receipt_no']) === 1;
    $saved = "{$p}_id='".sql_escape_string($id)."' AND {$p}_cash=1 AND {$p}_cash_no='{$cash_no}' AND {$p}_cash_info='{$cash_info}'";
    // UPDATE 응답 유실을 미저장으로 단정하지 않는다. 이번 시도의 승인·원장 상태를 재확인한다.
    if ($valid_receipt && (kcp_cash_update($table, "{$p}_cash=1, {$p}_cash_no='{$cash_no}', {$p}_cash_info='{$cash_info}'", $owned.' AND '.$snapshot)
        || kcp_cash_matches($table, $saved.' AND '.$snapshot))) {
        kcp_cash_audit($attempt, 'issued');
        return array('state'=>'issued', 'message'=>'현금영수증이 발급되었습니다.', 'amount'=>$data['amt_tot'], 'attempt'=>$attempt);
    }
    // 취소 전에 확인 필요 상태를 영속화한다. 이번 승인만 전환하며 다른 거래는 건드리지 않는다.
    $reconcile = sql_escape_string(serialize(array('kcp_cash_state'=>'reconcile', 'attempt'=>$attempt, 'requested_at'=>date('c'), 'cash_no'=>$response['cash_no'], 'amount'=>$data['amt_tot'])));
    $cancelling = "{$p}_id='".sql_escape_string($id)."' AND {$p}_cash=0 AND {$p}_cash_no='' AND {$p}_cash_info='{$reconcile}'";
    $prepared = kcp_cash_update($table, "{$p}_cash=0, {$p}_cash_no='', {$p}_cash_info='{$reconcile}'", '('.$owned.') OR ('.$saved.')');
    if (!$prepared && !kcp_cash_matches($table, $cancelling)) {
        kcp_cash_audit($attempt, 'cancel_not_started_reconcile_required');
        return array('state'=>'pending', 'message'=>'발급 결과 확인이 필요합니다. 재발급하지 말고 관리자에게 문의해 주십시오.', 'attempt'=>$attempt);
    }
    // 이 요청에서 받은 승인번호로만 보상 취소한다. 요청자가 지정한 거래번호는 사용하지 않는다.
    $data['cash_no'] = (string)$response['cash_no'];
    try { $cancel = call_user_func($gateway, 'cancel', $data); }
    catch (Throwable $e) { $cancel = array(); }
    if (is_array($cancel) && isset($cancel['res_cd']) && $cancel['res_cd'] === '0000') {
        $cleared = kcp_cash_update($table, "{$p}_cash_info=''", $cancelling);
        kcp_cash_audit($attempt, $cleared ? 'compensated' : 'compensated_reconcile_required');
        return array('state'=>$cleared ? 'compensated' : 'pending', 'message'=>$cleared ? '주문 저장에 실패하여 발급을 취소했습니다. 주문 상태를 확인한 후 다시 신청해 주십시오.' : '발급은 취소되었으나 주문 상태 확인이 필요합니다. 관리자에게 문의해 주십시오.', 'attempt'=>$attempt);
    }
    kcp_cash_audit($attempt, 'cancel_failed_reconcile_required');
    return array('state'=>'pending', 'message'=>'발급 결과와 취소 결과 확인이 필요합니다. 재발급하지 말고 관리자에게 문의해 주십시오.', 'attempt'=>$attempt);
}

function kcp_cash_html($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function kcp_cash_pending_notice($info)
{
    if (!is_string($info) || $info === '') return '';
    $state = @unserialize($info, array('allowed_classes'=>false));
    if (!is_array($state) || !isset($state['kcp_cash_state'], $state['attempt']) || !in_array($state['kcp_cash_state'], array('pending','reconcile'), true)) return '';
    return 'KCP 현금영수증 처리 확인 필요 (확인번호: '.kcp_cash_html($state['attempt']).'). KCP 거래 내역과 주문을 대조하기 전에는 재발급하지 마십시오.';
}

// KCP 통신은 검증과 영속 처리 선점 이후에만 호출한다.
function kcp_cash_gateway($operation, $data)
{
    global $default;
    // 설정 로더의 요청값 기반 간편결제 분기를 이 경로에서 사용하지 않는다.
    $site_id = $default['de_card_test'] ? ($default['de_escrow_use'] == 1 ? 'T0007' : 'T0000') : $default['de_kcp_mid'];
    $site_key = $default['de_card_test'] ? ($default['de_escrow_use'] == 1 ? '4Ho4YsuOZlLXUZUdOxM1Q7X__' : '3grptw1.zW0GSo4PQdaGvsF__') : $default['de_kcp_site_key'];
    if (strlen($site_id) === 3) $site_id = 'SR'.$site_id;
    if (!preg_match('/\A[0-9A-Za-z]{5}\z/D', $site_id) || $site_key === '') throw new RuntimeException('KCP configuration');
    require_once G5_SHOP_PATH.'/kcp/pp_cli_hub_lib.php';
    $client = new C_PAYPLUS_CLI_T();
    $client->mf_clear();
    if ($operation === 'pay') {
        $rcpt = '';
        $values = array('user_type'=>'PGNW', 'pay_type'=>'PAXX');
        foreach (array('trad_time','tr_code','id_info','amt_tot','amt_sup','amt_tax','amt_svc') as $field) $values[$field] = (string)$data[$field];
        foreach ($values as $field=>$value) $rcpt .= $client->mf_set_data_us($field, $value);
        foreach (array('ordr_idxx','good_name','buyr_name','buyr_mail','buyr_tel1') as $field) {
            // KCP CLI 전문의 구분 문자가 원장 문자열에 포함되어도 다른 필드가 되지 않게 한다.
            $value = preg_replace('/[\x00-\x1f\x7f,=]/', ' ', (string)$data[$field]);
            if ($field === 'good_name' || $field === 'buyr_name') $value = iconv('UTF-8', 'CP949//IGNORE', $value);
            $client->mf_set_ordr_data($field, $value);
        }
        $client->mf_set_ordr_data('rcpt_data', $rcpt);
        $client->mf_set_ordr_data('corp_data', $client->mf_set_data_us('corp_type', '0'));
        $code = '07010000';
    } else {
        $code = '07020000';
        foreach (array('mod_type'=>'STSC','mod_value'=>$data['cash_no'],'mod_gubn'=>'MG01','trad_time'=>$data['trad_time']) as $field=>$value) $client->mf_set_modx_data($field, $value);
    }
    $home = G5_SHOP_PATH.'/kcp';
    $key_dir = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? $home.'/bin/pub.key' : '';
    $client->mf_do_tx('', $home, $site_id, $site_key, $code, '', $default['de_card_test'] ? 'testpaygw.kcp.co.kr' : 'paygw.kcp.co.kr', '8090', 'payplus_cli_slib', $data['ordr_idxx'], $_SERVER['REMOTE_ADDR'], '0', '', 0, $key_dir, '');
    $response = array('res_cd'=>(string)$client->m_res_cd);
    foreach (array('cash_no','receipt_no','app_time','reg_stat','reg_desc') as $field) $response[$field] = isset($client->m_res_data[$field]) && is_scalar($client->m_res_data[$field]) ? (string)$client->m_res_data[$field] : '';
    $response['reg_desc'] = iconv('CP949', 'UTF-8//IGNORE', $response['reg_desc']);
    return $response;
}
