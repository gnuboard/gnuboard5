<?php
if (!defined('_GNUBOARD_')) exit;
require_once(dirname(__FILE__).'/shop_order_access.lib.php');
require_once(dirname(__FILE__).'/shop_cash.lib.php');

// 원장·토큰·조건부 갱신은 PG 공통 함수(lib/shop_cash.lib.php)를 쓴다. KCP 동작과 SQL은 바꾸지 않는다.
class KcpCashRequestException extends ShopCashRequestException {}

// KVE-2026-2513: 현금영수증은 화면의 hidden 값이 아닌 주문 원장으로 발급한다.
function kcp_cash_fail($message = '현금영수증을 발급할 수 없는 요청입니다.')
{
    throw new KcpCashRequestException($message);
}

// 공통 함수의 실패를 KCP 예외로 바꿔 기존 호출부의 처리를 유지한다.
function kcp_cash_shared($function, $args)
{
    try {
        return call_user_func_array($function, $args);
    } catch (KcpCashRequestException $e) {
        throw $e;
    } catch (ShopCashRequestException $e) {
        kcp_cash_fail($e->getMessage());
    }
}

function kcp_cash_key($type, $id)
{
    return shop_cash_key('kcp', $type, $id);
}

function kcp_cash_token($type, $id)
{
    return shop_cash_token('kcp', $type, $id);
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
    return kcp_cash_shared('shop_cash_source', array($type));
}

function kcp_cash_order($type, $id)
{
    return kcp_cash_shared('shop_cash_order', array($type, $id));
}

function kcp_cash_data($row, $type)
{
    $data = kcp_cash_shared('shop_cash_data', array($row, $type, 'kcp'));
    // KCP 게이트웨이에는 기존과 같은 필드만 넘긴다.
    unset($data['amt_free']);
    return $data;
}

function kcp_cash_snapshot_where($row, $p)
{
    return shop_cash_snapshot_where($row, $p);
}

function kcp_cash_update($table, $set, $where)
{
    return shop_cash_update($table, $set, $where);
}

function kcp_cash_audit($attempt, $state)
{
    shop_cash_audit('kcp', $attempt, $state);
}

function kcp_cash_matches($table, $where)
{
    return shop_cash_matches($table, $where);
}

function kcp_cash_process($request, $gateway)
{
    $type = $request['tx'];
    $id = $request['ordr_idxx'];
    $row = kcp_cash_order($type, $id);
    $data = kcp_cash_data($row, $type);
    $key = kcp_cash_key($type, $id);
    $session = get_session($key);
    if (!is_array($session) || !isset($session['expires'], $session['token']) || $session['expires'] < time() || !shop_order_equals($session['token'], $request['cash_token'])) kcp_cash_fail('요청이 만료되었습니다. 발급 화면을 다시 열어 주십시오.');
    set_session($key, '');
    list($table, $p) = kcp_cash_source($type);
    $attempt = shop_order_random_hex(16);
    $pending = serialize(array('kcp_cash_state'=>'pending', 'attempt'=>$attempt, 'requested_at'=>date('c'), 'amount'=>$data['amt_tot']));
    $pending_sql = sql_escape_string($pending);
    $snapshot = kcp_cash_snapshot_where($row, $p);
    if (!kcp_cash_update($table, "{$p}_cash_info='{$pending_sql}'", $snapshot." AND {$p}_cash=0 AND {$p}_cash_no='' AND {$p}_cash_info=''")) kcp_cash_fail('이미 처리 중이거나 주문 상태가 변경되었습니다.');
    // 프로세스 종료/타임아웃 때도 pending을 유지하여 불확실한 거래의 재발급을 차단한다.
    $owned = "{$p}_id='".sql_escape_string($id)."' AND {$p}_cash=0 AND {$p}_cash_no='' AND {$p}_cash_info='{$pending_sql}'";
    $data['tr_code'] = $request['tr_code'];
    $data['id_info'] = $request['id_info'];
    try { $response = call_user_func($gateway, 'pay', $data); }
    catch (Exception $e) { $response = array(); }
    catch (Throwable $e) { $response = array(); }
    if (!is_array($response) || !isset($response['res_cd']) || $response['res_cd'] !== '0000' || empty($response['cash_no']) || !preg_match('/\A[0-9]{1,40}\z/D', (string)$response['cash_no'])) {
        kcp_cash_audit($attempt, 'reconcile_required');
        return array('state'=>'pending', 'message'=>'발급 결과 확인이 필요합니다. 재발급하지 말고 관리자에게 문의해 주십시오.', 'attempt'=>$attempt);
    }
    $cash = array('receipt_no'=>(string)(isset($response['receipt_no']) ? $response['receipt_no'] : ''), 'app_time'=>(string)(isset($response['app_time']) ? $response['app_time'] : ''),
        'reg_stat'=>(string)(isset($response['reg_stat']) ? $response['reg_stat'] : ''), 'reg_desc'=>(string)(isset($response['reg_desc']) ? $response['reg_desc'] : ''),
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
    catch (Exception $e) { $cancel = array(); }
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
    return shop_cash_html($value);
}

// 관리자 화면 안내. KCP 문구는 그대로이고, 다른 PG의 처리 중·확인 필요 상태도 함께 보여준다.
function kcp_cash_pending_notice($info)
{
    return shop_cash_pending_notice($info);
}

// KCP 통신은 검증과 영속 처리 선점 이후에만 호출한다.
function kcp_cash_gateway($operation, $data)
{
    global $default;
    require_once dirname(__FILE__).'/kcp_rest.lib.php';
    if (!in_array($operation, array('pay', 'cancel'), true)) throw new RuntimeException('KCP cash operation');
    if (kcp_rest_enabled()) {
        $client = new G5_KCP_REST_Client(kcp_rest_config());
        return $operation === 'pay' ? $client->cash_issue(kcp_rest_site_code(), $data) :
            $client->cash_cancel(kcp_rest_site_code(), $data['cash_no']);
    }
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
