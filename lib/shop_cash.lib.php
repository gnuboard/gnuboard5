<?php
if (!defined('_GNUBOARD_')) exit;
require_once(dirname(__FILE__).'/shop_order_access.lib.php');

class ShopCashRequestException extends RuntimeException {}

// KVE-2026-2513·KVE-2026-3247: 현금영수증은 화면의 hidden 값이 아닌 주문 원장으로 발급한다(PG 공통).
// 일회용 토큰, POST·출처·형식 검사, 원장 상태 재검사, PG 호출 전 조건부 선점, 조건부 저장(정확히 1행)을 모든 PG에 쓴다.
function shop_cash_fail($message = '현금영수증을 발급할 수 없는 요청입니다.')
{
    throw new ShopCashRequestException($message);
}

function shop_cash_key($pg, $type, $id)
{
    return 'ss_'.$pg.'_cash_'.hash('sha256', $type.':'.$id.':pay');
}

// 주문 유형·주문번호에 묶인 10분 일회용 토큰
function shop_cash_token($pg, $type, $id)
{
    $token = shop_order_random_hex(32);
    set_session(shop_cash_key($pg, $type, $id), array('token'=>$token, 'expires'=>time()+600));
    return $token;
}

// 토큰은 결과와 관계없이 한 번 쓰면 폐기한다.
function shop_cash_consume_token($pg, $type, $id, $token)
{
    $key = shop_cash_key($pg, $type, $id);
    $session = get_session($key);
    set_session($key, '');
    return is_array($session) && isset($session['expires'], $session['token']) && $session['expires'] >= time()
        && is_string($token) && shop_order_equals($session['token'], $token);
}

function shop_cash_source($type)
{
    global $g5;
    if ($type === 'personalpay') return array($g5['g5_shop_personalpay_table'], 'pp');
    if ($type === 'order') return array($g5['g5_shop_order_table'], 'od');
    shop_cash_fail();
}

function shop_cash_order($type, $id)
{
    list($table, $p) = shop_cash_source($type);
    $row = sql_fetch("SELECT * FROM {$table} WHERE {$p}_id='".sql_escape_string($id)."'", false);
    if (!$row || empty($row[$p.'_id']) || !function_exists('is_shop_order_owner') || !is_shop_order_owner($row, $type)) shop_cash_fail('해당 주문 정보에 접근 권한이 없습니다.');
    return $row;
}

// 발급 가능 조건: 미발급·처리 중 아님, PG·설정·결제수단, 수납·환불·주문 상태, 금액 일치
function shop_cash_data($row, $type, $pg)
{
    global $default;
    $p = $type === 'personalpay' ? 'pp' : 'od';
    if ((int)$row[$p.'_cash'] !== 0 || $row[$p.'_cash_no'] !== '' || $row[$p.'_cash_info'] !== '') shop_cash_fail('이미 발급되었거나 처리 결과를 확인 중인 주문입니다. 관리자에게 문의해 주십시오.');
    $row_pg = $row[$p.'_pg'] !== '' ? $row[$p.'_pg'] : $default['de_pg_service'];
    if ($row_pg !== $pg || empty($default['de_taxsave_use'])) shop_cash_fail();
    $methods = array('무통장'=>'account', '계좌이체'=>'transfer', '가상계좌'=>'vbank');
    $method = $row[$p.'_settle_case'];
    if (!isset($methods[$method]) || strpos($default['de_taxsave_types'], $methods[$method]) === false) shop_cash_fail();
    $total = (int)$row[$p.'_receipt_price'];
    $free = 0;
    if ($type === 'personalpay') {
        if (!empty($row['od_id'])) shop_cash_fail('주문에 연결된 개인결제는 주문 내역에서 현금영수증을 확인해 주십시오.');
        if ($total <= 0 || $total !== (int)$row['pp_price']) shop_cash_fail('개인결제 수납 금액을 확인해 주십시오.');
        $supply = (int)round($total / 1.1);
        $tax = $total - $supply;
        $name = $row['pp_name'].'님 개인결제';
    } else {
        $total -= (int)$row['od_refund_price'];
        if ($total <= 0 || (int)$row['od_misu'] > 0 || !in_array($row['od_status'], array('입금','준비','배송','완료'), true)) shop_cash_fail('주문 수납 및 환불 상태를 확인해 주십시오.');
        $supply = (int)$row['od_tax_mny'] + (int)$row['od_free_mny'];
        $tax = (int)$row['od_vat_mny'];
        $free = (int)$row['od_free_mny'];
        if ((int)$row['od_tax_mny'] < 0 || (int)$row['od_free_mny'] < 0 || $tax < 0 || $supply+$tax !== $total) shop_cash_fail('수납 금액과 과세 정보가 일치하지 않습니다. 관리자에게 문의해 주십시오.');
        $goods = get_goods($row['od_id']);
        $name = $goods['full_name'];
    }
    return array('ordr_idxx'=>(string)$row[$p.'_id'], 'good_name'=>$name, 'buyr_name'=>$row[$p.'_name'],
        'buyr_mail'=>$row[$p.'_email'], 'buyr_tel1'=>$row[$p.($p==='pp' ? '_hp' : '_tel')],
        'amt_tot'=>$total, 'amt_sup'=>$supply, 'amt_tax'=>$tax, 'amt_svc'=>0, 'amt_free'=>$free, 'trad_time'=>date('YmdHis'));
}

// PG별 발급 용도 값(소득공제, 지출증빙). 기존 발급 화면이 보내던 값을 그대로 쓴다.
function shop_cash_tr_codes($pg)
{
    $codes = array('lg'=>array('1', '2'), 'toss'=>array('0', '1'), 'inicis'=>array('0', '1'), 'nicepay'=>array('1', '2'), 'kcp'=>array('0', '1'));
    return isset($codes[$pg]) ? $codes[$pg] : array();
}

// 비KCP 발급 처리기의 요청 값. $_POST만 읽고 형식을 확인한다.
function shop_cash_request($post, $method, $pg)
{
    if ($method !== 'POST') shop_cash_fail('올바른 방법으로 이용해 주십시오.');
    foreach (array('tx', 'od_id', 'cash_token', 'tr_code', 'id_info') as $name) {
        if (!isset($post[$name]) || !is_string($post[$name])) shop_cash_fail();
    }
    $tx = $post['tx'] === 'personalpay' ? 'personalpay' : ($post['tx'] === '' || $post['tx'] === 'order' ? 'order' : '');
    if ($tx === '') shop_cash_fail();
    if (!preg_match('/\A[0-9A-Za-z_.-]{1,20}\z/D', $post['od_id'])) shop_cash_fail();
    if (!preg_match('/\A[0-9a-f]{64}\z/D', $post['cash_token'])) shop_cash_fail('요청이 만료되었습니다. 발급 화면을 다시 열어 주십시오.');
    $codes = shop_cash_tr_codes($pg);
    if (!$codes || !in_array($post['tr_code'], $codes, true)) shop_cash_fail('현금영수증 발행용도가 올바르지 않습니다.');
    $pattern = $post['tr_code'] === $codes[0] ? '/\A(?:[0-9]{10,11}|[0-9]{13})\z/D' : '/\A[0-9]{10}\z/D';
    if (!preg_match($pattern, $post['id_info'])) shop_cash_fail('현금영수증 식별번호를 확인해 주십시오.');
    $email = isset($post['buyeremail']) && is_string($post['buyeremail']) ? trim($post['buyeremail']) : '';
    $tel = isset($post['buyertel']) && is_string($post['buyertel']) ? trim($post['buyertel']) : '';
    if ($email !== '' && (strlen($email) > 100 || !preg_match('/\A[^@\s<>"\'\\\\]+@[^@\s<>"\'\\\\]+\z/D', $email))) shop_cash_fail('이메일 주소를 확인해 주십시오.');
    if ($tel !== '' && !preg_match('/\A[0-9-]{1,20}\z/D', $tel)) shop_cash_fail('휴대폰 번호를 확인해 주십시오.');
    return array('tx'=>$tx, 'od_id'=>$post['od_id'], 'cash_token'=>$post['cash_token'], 'tr_code'=>$post['tr_code'],
        'id_info'=>$post['id_info'], 'buyeremail'=>$email, 'buyertel'=>$tel);
}

// 발급 직전과 저장 직후의 금액/상태 경합도 검출한다. 외부 통신 중 DB 트랜잭션은 열지 않는다.
function shop_cash_snapshot_where($row, $p)
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

function shop_cash_update($table, $set, $where)
{
    try {
        $ok = sql_query("UPDATE {$table} SET {$set} WHERE {$where}", false);
        return $ok && (int)get_sql_affected_rows() === 1;
    } catch (Exception $e) {
        return false;
    } catch (Throwable $e) {
        return false;
    }
}

function shop_cash_matches($table, $where)
{
    try {
        $row = sql_fetch("SELECT 1 AS matched FROM {$table} WHERE {$where}", false);
        return $row && (int)$row['matched'] === 1;
    } catch (Exception $e) {
        return false;
    } catch (Throwable $e) {
        return false;
    }
}

function shop_cash_audit($pg, $attempt, $state)
{
    // 식별번호, 주문자, 가맹점 키 및 PG 원문 응답을 로그에 남기지 않는다.
    error_log(strtoupper(preg_replace('/[^a-z]/', '', $pg)).' cash '.$attempt.' '.$state);
}

function shop_cash_call($gateway, $operation, $data)
{
    try { $response = call_user_func($gateway, $operation, $data); }
    catch (Exception $e) { $response = array(); }
    catch (Throwable $e) { $response = array(); }
    return is_array($response) ? $response : array();
}

// 비KCP PG 공통 발급 처리. 게이트웨이는 array('ok'=>bool, 'cash_no', 'info'=>array, 'display'=>array, 'definite'=>bool)를 돌려준다.
// definite는 PG가 형식이 맞는 응답으로 발급 거절을 명시한 경우만 true다. 그때만 선점을 풀고, 나머지 실패는 확인 대기로 남긴다.
function shop_cash_process($request, $pg, $gateway)
{
    $type = $request['tx'];
    $id = $request['od_id'];
    $row = shop_cash_order($type, $id);
    if (!shop_cash_consume_token($pg, $type, $id, $request['cash_token'])) shop_cash_fail('요청이 만료되었습니다. 발급 화면을 다시 열어 주십시오.');
    $data = shop_cash_data($row, $type, $pg);
    list($table, $p) = shop_cash_source($type);
    $attempt = shop_order_random_hex(16);
    $pending = serialize(array('shop_cash_state'=>'pending', 'pg'=>$pg, 'attempt'=>$attempt, 'requested_at'=>date('c'), 'amount'=>$data['amt_tot']));
    $pending_sql = sql_escape_string($pending);
    $snapshot = shop_cash_snapshot_where($row, $p);
    // PG 호출 전에 한 행만 선점한다. 0행이면(동시 요청·상태 변경) PG를 호출하지 않는다.
    if (!shop_cash_update($table, "{$p}_cash_info='{$pending_sql}'", $snapshot." AND {$p}_cash=0 AND {$p}_cash_no='' AND {$p}_cash_info=''")) shop_cash_fail('이미 처리 중이거나 주문 상태가 변경되었습니다.');
    // 프로세스 종료/타임아웃 때도 pending을 유지하여 불확실한 거래의 재발급을 차단한다.
    $owned = "{$p}_id='".sql_escape_string($id)."' AND {$p}_cash=0 AND {$p}_cash_no='' AND {$p}_cash_info='{$pending_sql}'";
    $data['type'] = $type;
    $data['row'] = $row;
    foreach (array('tr_code', 'id_info', 'buyeremail', 'buyertel') as $field) $data[$field] = $request[$field];
    $response = shop_cash_call($gateway, 'pay', $data);
    $display = isset($response['display']) && is_array($response['display']) ? $response['display'] : array();
    if (empty($response['ok']) || !isset($response['cash_no'], $response['info']) || !is_array($response['info'])
        || !preg_match('/\A[0-9A-Za-z]{1,40}\z/D', (string)$response['cash_no'])) {
        if (!empty($response['definite']) && shop_cash_update($table, "{$p}_cash_info=''", $owned)) {
            shop_cash_audit($pg, $attempt, 'declined');
            $message = isset($response['message']) && is_string($response['message']) && $response['message'] !== '' ? $response['message'] : '현금영수증 발급 요청이 거절되었습니다.';
            return array('state'=>'declined', 'message'=>$message, 'attempt'=>$attempt, 'display'=>$display);
        }
        shop_cash_audit($pg, $attempt, 'reconcile_required');
        return array('state'=>'pending', 'message'=>'발급 결과 확인이 필요합니다. 재발급하지 말고 관리자에게 문의해 주십시오.', 'attempt'=>$attempt, 'display'=>$display);
    }
    $info = $response['info'];
    $info['attempt'] = $attempt;
    $cash_no = sql_escape_string((string)$response['cash_no']);
    $cash_info = sql_escape_string(serialize($info));
    $saved = "{$p}_id='".sql_escape_string($id)."' AND {$p}_cash=1 AND {$p}_cash_no='{$cash_no}' AND {$p}_cash_info='{$cash_info}'";
    // UPDATE 응답 유실을 미저장으로 단정하지 않는다. 이번 시도의 승인·원장 상태를 재확인한다.
    if (shop_cash_update($table, "{$p}_cash=1, {$p}_cash_no='{$cash_no}', {$p}_cash_info='{$cash_info}'", $owned.' AND '.$snapshot)
        || shop_cash_matches($table, $saved.' AND '.$snapshot)) {
        shop_cash_audit($pg, $attempt, 'issued');
        return array('state'=>'issued', 'message'=>'현금영수증이 발급되었습니다.', 'amount'=>$data['amt_tot'], 'attempt'=>$attempt, 'display'=>$display);
    }
    // 취소 전에 확인 필요 상태를 영속화한다. 이번 승인만 전환하며 다른 거래는 건드리지 않는다.
    $reconcile = sql_escape_string(serialize(array('shop_cash_state'=>'reconcile', 'pg'=>$pg, 'attempt'=>$attempt, 'requested_at'=>date('c'), 'cash_no'=>(string)$response['cash_no'], 'amount'=>$data['amt_tot'])));
    $cancelling = "{$p}_id='".sql_escape_string($id)."' AND {$p}_cash=0 AND {$p}_cash_no='' AND {$p}_cash_info='{$reconcile}'";
    $prepared = shop_cash_update($table, "{$p}_cash=0, {$p}_cash_no='', {$p}_cash_info='{$reconcile}'", '('.$owned.') OR ('.$saved.')');
    if (!$prepared && !shop_cash_matches($table, $cancelling)) {
        shop_cash_audit($pg, $attempt, 'cancel_not_started_reconcile_required');
        return array('state'=>'pending', 'message'=>'발급 결과 확인이 필요합니다. 재발급하지 말고 관리자에게 문의해 주십시오.', 'attempt'=>$attempt, 'display'=>$display);
    }
    // 이 요청에서 받은 승인 정보로만 보상 취소한다. 요청자가 지정한 거래번호는 사용하지 않는다. 취소 API가 없는 PG는 확인 대기로 남긴다.
    $data['cash_no'] = (string)$response['cash_no'];
    $data['response'] = $response;
    $cancel = shop_cash_call($gateway, 'cancel', $data);
    if (!empty($cancel['ok'])) {
        $cleared = shop_cash_update($table, "{$p}_cash_info=''", $cancelling);
        shop_cash_audit($pg, $attempt, $cleared ? 'compensated' : 'compensated_reconcile_required');
        return array('state'=>$cleared ? 'compensated' : 'pending', 'message'=>$cleared ? '주문 저장에 실패하여 발급을 취소했습니다. 주문 상태를 확인한 후 다시 신청해 주십시오.' : '발급은 취소되었으나 주문 상태 확인이 필요합니다. 관리자에게 문의해 주십시오.', 'attempt'=>$attempt, 'display'=>$display);
    }
    shop_cash_audit($pg, $attempt, 'cancel_failed_reconcile_required');
    return array('state'=>'pending', 'message'=>'발급 결과와 취소 결과 확인이 필요합니다. 재발급하지 말고 관리자에게 문의해 주십시오.', 'attempt'=>$attempt, 'display'=>$display);
}

// 비KCP 처리기 진입점: POST·출처·토큰·형식·발급 조건을 확인한 뒤 발급한다. 발급되지 않았으면 안내 후 종료한다.
function shop_cash_handle($pg, $gateway)
{
    $method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
    if ($method !== 'POST') alert('올바른 방법으로 이용해 주십시오.', G5_SHOP_URL);
    if (function_exists('check_request_origin')) check_request_origin(G5_SHOP_URL);
    try {
        $request = shop_cash_request($_POST, $method, $pg);
        $result = shop_cash_process($request, $pg, $gateway);
    } catch (ShopCashRequestException $e) {
        alert($e->getMessage());
        exit;
    } catch (Exception $e) {
        error_log(strtoupper($pg).' cash request internal failure');
        alert('현금영수증 처리 상태를 확인할 수 없습니다. 관리자에게 문의해 주십시오.');
        exit;
    } catch (Throwable $e) {
        error_log(strtoupper($pg).' cash request internal failure');
        alert('현금영수증 처리 상태를 확인할 수 없습니다. 관리자에게 문의해 주십시오.');
        exit;
    }
    if ($result['state'] !== 'issued') {
        $message = $result['message'];
        if ($result['state'] !== 'declined') $message .= '\\n처리 확인번호: '.$result['attempt'];
        alert($message);
        exit;
    }
    return $result;
}

// 발급 화면: 같은 원장/정책 검증을 하고 토큰을 만든다.
function shop_cash_form_token($pg, $type, $id)
{
    try {
        $row = shop_cash_order($type, (string)$id);
        shop_cash_data($row, $type, $pg);
        return shop_cash_token($pg, $type, (string)$id);
    } catch (ShopCashRequestException $e) {
        alert($e->getMessage());
    } catch (Exception $e) {
        alert('주문 정보를 확인할 수 없습니다. 관리자에게 문의해 주십시오.');
    } catch (Throwable $e) {
        alert('주문 정보를 확인할 수 없습니다. 관리자에게 문의해 주십시오.');
    }
    exit;
}

function shop_cash_html($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES | (defined('ENT_SUBSTITUTE') ? ENT_SUBSTITUTE : 0), 'UTF-8');
}

// 관리자 화면 안내: 처리 중·확인 필요 상태(KCP 기존 형식 포함)
function shop_cash_pending_notice($info)
{
    if (!is_string($info) || $info === '') return '';
    $state = shop_order_decode_data(base64_encode($info));
    if (!is_array($state) || !isset($state['attempt'])) return '';
    if (isset($state['kcp_cash_state']) && in_array($state['kcp_cash_state'], array('pending','reconcile'), true)) {
        return 'KCP 현금영수증 처리 확인 필요 (확인번호: '.shop_cash_html($state['attempt']).'). KCP 거래 내역과 주문을 대조하기 전에는 재발급하지 마십시오.';
    }
    $names = array('lg'=>'토스페이먼츠(LG U+)', 'toss'=>'토스페이먼츠', 'inicis'=>'KG이니시스', 'nicepay'=>'나이스페이');
    if (isset($state['shop_cash_state'], $state['pg']) && in_array($state['shop_cash_state'], array('pending','reconcile'), true) && isset($names[$state['pg']])) {
        $name = $names[$state['pg']];
        return $name.' 현금영수증 처리 확인 필요 (확인번호: '.shop_cash_html($state['attempt']).'). '.$name.' 거래 내역과 주문을 대조하기 전에는 재발급하지 마십시오.';
    }
    return '';
}

// ---------- PG별 게이트웨이(검증·선점 이후에만 호출) ----------

// LG U+(토스페이먼츠 eCredit) XPay
function shop_cash_gateway_lg($operation, $data)
{
    global $config, $default, $configPath, $CST_PLATFORM, $LGD_MID;
    $row = $data['row'];
    $p = $data['type'] === 'personalpay' ? 'pp' : 'od';
    $pay_types = array('가상계좌'=>'SC0040', '계좌이체'=>'SC0030', '무통장'=>'SC0100');
    $pay_type = $pay_types[$row[$p.'_settle_case']];
    $xpay = new XPay($configPath, $CST_PLATFORM);
    $xpay->set_config_value('t'.$LGD_MID, $config['cf_lg_mert_key']);
    $xpay->set_config_value($LGD_MID, $config['cf_lg_mert_key']);
    $xpay->Init_TX($LGD_MID);
    $xpay->Set("LGD_TXNAME", "CashReceipt");
    $xpay->Set("LGD_PAYTYPE", $pay_type);
    if ($operation === 'cancel') {
        $xpay->Set("LGD_METHOD", "CANCEL");
        $xpay->Set("LGD_TID", $row[$p.'_tno']);
        if ($pay_type == "SC0040") $xpay->Set("LGD_SEQNO", $row[$p.'_casseqno']);
        return array('ok'=>$xpay->TX() && $xpay->Response_Code() === '0000');
    }
    $xpay->Set("LGD_METHOD", "AUTH");
    $xpay->Set("LGD_OID", $data['ordr_idxx']);
    $xpay->Set("LGD_AMOUNT", $data['amt_tot']);
    $xpay->Set("LGD_CASHCARDNUM", $data['id_info']);
    $xpay->Set("LGD_CUSTOM_MERTNAME", $default['de_admin_company_name']);
    $xpay->Set("LGD_CUSTOM_CEONAME", $default['de_admin_company_owner']);
    $xpay->Set("LGD_CUSTOM_BUSINESSNUM", $default['de_admin_company_saupja_no']);
    $xpay->Set("LGD_CUSTOM_MERTPHONE", $default['de_admin_company_tel']);
    $xpay->Set("LGD_CASHRECEIPTUSE", $data['tr_code']);
    $xpay->Set("LGD_ENCODING", "UTF-8");
    if ($p === 'od' && isset($row['od_tax_flag']) && $row['od_tax_flag'] && $row['od_free_mny'] > 0) {
        $xpay->Set("LGD_TAXFREEAMOUNT", $row['od_free_mny']); //비과세 금액
    }
    if ($pay_type == "SC0030") {              //기결제된 계좌이체건 현금영수증 발급요청시 필수
        $xpay->Set("LGD_TID", $row[$p.'_tno']);
    } else if ($pay_type == "SC0040") {       //기결제된 가상계좌건 현금영수증 발급요청시 필수
        $xpay->Set("LGD_TID", $row[$p.'_tno']);
        $xpay->Set("LGD_SEQNO", $row[$p.'_casseqno']);
    } else {                                  //무통장입금 단독건 발급요청
        $xpay->Set("LGD_PRODUCTINFO", $data['good_name']);
    }
    if (!$xpay->TX()) {
        // 전송·HTTP·JSON 오류는 발급 여부를 알 수 없다.
        return array('ok'=>false, 'definite'=>false);
    }
    $code = (string)$xpay->Response_Code();
    $display = array('code'=>$code, 'msg'=>(string)$xpay->Response_Msg(), 'tid'=>(string)$xpay->Response("LGD_TID",0),
        'cash_no'=>(string)$xpay->Response("LGD_CASHRECEIPTNUM",0), 'respdate'=>(string)$xpay->Response("LGD_RESPDATE",0),
        'oid'=>$data['ordr_idxx'], 'pay_type'=>$pay_type, 'casseqno'=>(string)$row[$p.'_casseqno']);
    if ($code !== '0000') return array('ok'=>false, 'definite'=>true, 'message'=>'현금영수증 발급 요청이 거절되었습니다. (코드 '.preg_replace('/[^0-9A-Za-z]/', '', $code).')', 'display'=>$display);
    // 응답 주문번호가 요청 주문과 다르면 저장하지 않는다(확인 대기).
    if ((string)$xpay->Response("LGD_OID",0) !== $data['ordr_idxx']) return array('ok'=>false, 'definite'=>false, 'display'=>$display);
    $cash = array('LGD_TID'=>$display['tid'], 'LGD_TIMESTAMP'=>(string)$xpay->Response("LGD_TIMESTAMP",0), 'LGD_RESPDATE'=>$display['respdate']);
    return array('ok'=>true, 'cash_no'=>$display['cash_no'], 'info'=>$cash, 'display'=>$display);
}

// 토스페이먼츠
function shop_cash_gateway_toss($operation, $data)
{
    global $toss;
    if ($operation === 'cancel') {
        $info = $data['response']['info'];
        return array('ok'=>isset($info['receiptKey']) && $toss->cancelCashReceipt($info['receiptKey']));
    }
    $codes = shop_cash_tr_codes('toss');
    $toss->setCashReceiptsData(array(
        'orderId' => $data['ordr_idxx'],
        'amount' => $data['amt_tot'],
        'type' => ($data['tr_code'] === $codes[0]) ? '소득공제' : '지출증빙',
        'customerIdentityNumber' => $data['id_info'],
        'orderName' => $data['good_name'],
    ));
    $ok = $toss->issueCashReceipt();
    $res = is_array($toss->responseData) ? $toss->responseData : array();
    if (!$ok || !isset($res['receiptKey'])) {
        // 토스가 오류 코드로 명시적으로 거절한 경우만 확정 실패로 본다. 내부 오류·알 수 없음은 확인 대기.
        $code = isset($res['code']) && is_string($res['code']) ? $res['code'] : '';
        $definite = $code !== '' && !preg_match('/INTERNAL|UNKNOWN|TIMEOUT|PROCESSING/i', $code);
        return array('ok'=>false, 'definite'=>$definite, 'message'=>'현금영수증 발급 요청이 거절되었습니다. (코드 '.preg_replace('/[^0-9A-Za-z_]/', '', $code).')');
    }
    foreach (array('receiptKey', 'issueNumber', 'orderId') as $field) {
        if (!isset($res[$field]) || !is_scalar($res[$field]) || !preg_match('/\A[0-9A-Za-z_.:-]{1,100}\z/D', (string)$res[$field])) return array('ok'=>false, 'definite'=>false);
    }
    if ((string)$res['orderId'] !== $data['ordr_idxx'] || (isset($res['amount']) && (int)$res['amount'] !== (int)$data['amt_tot'])) return array('ok'=>false, 'definite'=>false);
    $cash = array('receiptKey'=>(string)$res['receiptKey'], 'approvedAt'=>isset($res['requestedAt']) ? (string)$res['requestedAt'] : '',
        'orderId'=>(string)$res['orderId'], 'amount'=>(int)$data['amt_tot'], 'type'=>isset($res['type']) ? (string)$res['type'] : '',
        'receiptUrl'=>isset($res['receiptUrl']) && is_string($res['receiptUrl']) ? $res['receiptUrl'] : '');
    $display = array('cash_no'=>(string)$res['issueNumber'], 'oid'=>(string)$res['orderId'], 'type'=>$cash['type'], 'amount'=>(int)$data['amt_tot'], 'approved'=>$cash['approvedAt']);
    return array('ok'=>true, 'cash_no'=>(string)$res['issueNumber'], 'info'=>$cash, 'display'=>$display);
}

// KG이니시스 INIAPI(KVE-2026-3248: 인증서 검증·응답 형식 검증)
function shop_cash_gateway_inicis($operation, $data)
{
    global $default;
    // 이니시스 현금영수증 취소 API는 연동하지 않았다. 저장 실패 시 확인 대기로 남긴다.
    if ($operation !== 'pay') return array('ok'=>false);
    $key = get_inicis_iniapi_key();
    $iv = get_inicis_iniapi_iv();
    $type = "Issue";
    $paymethod = "Receipt";
    $timestamp = date("YmdHis");
    $clientIp = $_SERVER['SERVER_ADDR'];
    $mid = $default['de_inicis_mid'];
    if ($data['type'] === 'personalpay') {
        // 기존 개인결제 금액 계산을 유지한다.
        $crPrice = (int)$data['amt_tot'];
        $supPrice = (int)round(($crPrice * 10) / 11);
        $tax = (int)($crPrice - $supPrice);
    } else {
        $crPrice = (int)$data['amt_tot'];
        $supPrice = (int)$data['amt_sup'];
        $tax = (int)$data['amt_tax'];
    }
    $srcvPrice = 0;
    $regNum = $data['id_info'];
    if (function_exists('openssl_encrypt')) {
        $enregNum = base64_encode(openssl_encrypt($regNum, 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $iv));
    } else if (function_exists('mcrypt_encrypt')) {
        $padSize = 16 - (strlen($regNum) % 16);
        $value = $regNum.str_repeat(chr($padSize), $padSize);
        $enregNum = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $value, MCRYPT_MODE_CBC, $iv));
    } else {
        throw new RuntimeException('INICIS encryption unavailable');
    }
    // INIAPIKey + type + paymethod + timestamp + clientIp + mid + crPrice + supPrice + srcvPrice + enregNum
    $hashData = hash("sha512", (string)$key.(string)$type.(string)$paymethod.(string)$timestamp.(string)$clientIp.(string)$mid.(string)$crPrice.(string)$supPrice.(string)$srcvPrice.(string)$enregNum);
    $post = array(
        'type' => $type, 'paymethod' => $paymethod, 'timestamp' => $timestamp, 'clientIp' => $clientIp, 'mid' => $mid,
        'goodName' => $data['good_name'], 'crPrice' => $crPrice, 'supPrice' => $supPrice, 'tax' => $tax, 'srcvPrice' => $srcvPrice,
        'buyerName' => $data['buyr_name'], 'buyerEmail' => $data['buyeremail'], 'buyerTel' => $data['buyertel'],
        'regNum' => $enregNum, 'useOpt' => $data['tr_code'], 'compayNumber' => '', 'hashData'=> $hashData,
    );
    // 인증서·호스트명을 검증해 요청합니다. 전송 실패·HTTP 오류·JSON 오류는 발급 여부를 알 수 없으므로 확인 대기로 남깁니다.
    $response = g5_tls_http_post("https://iniapi.inicis.com/api/v1/receipt", http_build_query($post), array('Content-Type: application/x-www-form-urlencoded; charset=utf-8'));
    $res = ($response !== false) ? json_decode($response, true) : null;
    if (!is_array($res) || !isset($res['resultCode']) || !is_scalar($res['resultCode'])) return array('ok'=>false, 'definite'=>false);
    $result_code = (string)$res['resultCode'];
    $result_msg = (isset($res['resultMsg']) && is_scalar($res['resultMsg'])) ? cut_str(strip_tags((string)$res['resultMsg']), 200, '') : '';
    $display = array('code'=>$result_code, 'msg'=>$result_msg, 'tid'=>'', 'cash_no'=>'', 'appl_time'=>'');
    if ($result_code !== '00') return array('ok'=>false, 'definite'=>true, 'message'=>'현금영수증 발급 요청이 거절되었습니다. (코드 '.preg_replace('/[^0-9A-Za-z]/', '', $result_code).')', 'display'=>$display);
    // 응답 필드 형식을 확인합니다. 하나라도 어긋나면 저장하지 않습니다.
    $fields = array(
        'tid'        => '/\A[A-Za-z0-9]{1,64}\z/',
        'authNo'     => '/\A[A-Za-z0-9]{1,20}\z/',
        'authDate'   => '/\A[0-9]{8}\z/',
        'authTime'   => '/\A[0-9]{6}\z/',
        'authUseOpt' => '/\A[A-Za-z0-9]{0,2}\z/',
    );
    $values = array();
    foreach ($fields as $field => $pattern) {
        $value = (isset($res[$field]) && is_scalar($res[$field])) ? (string)$res[$field] : '';
        if (!preg_match($pattern, $value)) return array('ok'=>false, 'definite'=>false, 'display'=>$display);
        $values[$field] = $value;
    }
    if (!checkdate((int)substr($values['authDate'], 4, 2), (int)substr($values['authDate'], 6, 2), (int)substr($values['authDate'], 0, 4))
        || (int)substr($values['authTime'], 0, 2) > 23 || (int)substr($values['authTime'], 2, 2) > 59 || (int)substr($values['authTime'], 4, 2) > 59) {
        return array('ok'=>false, 'definite'=>false, 'display'=>$display);
    }
    // 응답에 금액·MID가 있으면 요청 값과 대조합니다.
    if ((isset($res['crPrice']) && (string)$res['crPrice'] !== (string)$crPrice) || (isset($res['mid']) && (string)$res['mid'] !== (string)$mid)) {
        return array('ok'=>false, 'definite'=>false, 'display'=>$display);
    }
    $cash = array('TID'=>$values['tid'], 'ApplNum'=>$values['authNo'], 'ApplDate'=>$values['authDate'], 'ApplTime'=>$values['authTime'], 'CSHR_Type'=>$values['authUseOpt']);
    $display['tid'] = $values['tid'];
    $display['cash_no'] = $values['authNo'];
    $display['appl_time'] = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $values['authDate'].$values['authTime']);
    return array('ok'=>true, 'cash_no'=>$values['authNo'], 'info'=>$cash, 'display'=>$display);
}

// 나이스페이
function shop_cash_gateway_nicepay($operation, $data)
{
    global $default;
    // 나이스페이 현금영수증 취소 API는 연동하지 않았다. 저장 실패 시 확인 대기로 남긴다.
    if ($operation !== 'pay') return array('ok'=>false);
    $amt_tot = (int)$data['amt_tot'];
    if ($data['type'] === 'personalpay') {
        $supPrice = (int)round(($amt_tot * 10) / 11);
        $tax = (int)($amt_tot - $supPrice);
        $free = 0;
    } else {
        // 기존 계산을 유지한다: 공급가액은 과세분, 면세분은 따로 보낸다.
        $supPrice = (int)$data['row']['od_tax_mny'];
        $tax = (int)$data['amt_tax'];
        $free = (int)$data['amt_free'];
    }
    $ediDate = preg_replace('/[^0-9]/', '', G5_TIME_YMDHIS);
    $moid = $data['ordr_idxx'];
    // 04 (현금영수증), 01 (매체구분 일반), 시간정보 (12자리), 랜덤 4자리숫자
    $tid = $default['de_nicepay_mid'].'04'.'01'.substr($ediDate, 2).rand(1000, 9999);
    $signData = bin2hex(hash('sha256', $default['de_nicepay_mid'].$amt_tot.$ediDate.$moid.$default['de_nicepay_key'], true));
    $codes = shop_cash_tr_codes('nicepay');
    $post = array(
        'MID' => $default['de_nicepay_mid'], 'TID' => $tid, 'EdiDate' => $ediDate, 'Moid' => $moid, 'SignData' => $signData,
        'GoodsName' => iconv('utf-8', 'euc-kr', $data['good_name']), 'ReceiptAmt' => $amt_tot,
        'ReceiptType' => ($data['tr_code'] === $codes[1]) ? 2 : 1, 'ReceiptTypeNo' => $data['id_info'],
        'ReceiptSupplyAmt' => $supPrice, 'ReceiptVAT' => $tax, 'ReceiptServiceAmt' => 0, 'ReceiptTaxFreeAmt' => $free, 'CharSet' => 'utf-8',
    );
    $response = nicepay_reqPost($post, "https://pg-api.nicepay.co.kr/webapi/cash_receipt.jsp");
    $res = is_string($response) ? json_decode($response, true) : null;
    if (!is_array($res) || !isset($res['ResultCode']) || !is_scalar($res['ResultCode'])) return array('ok'=>false, 'definite'=>false);
    $code = (string)$res['ResultCode'];
    $display = array('code'=>$code, 'msg'=>isset($res['ResultMsg']) && is_scalar($res['ResultMsg']) ? cut_str(strip_tags((string)$res['ResultMsg']), 200, '') : '',
        'tid'=>'', 'cash_no'=>'', 'auth_date'=>'');
    if ($code !== '7001') return array('ok'=>false, 'definite'=>true, 'message'=>'현금영수증 발급 요청이 거절되었습니다. (코드 '.preg_replace('/[^0-9A-Za-z]/', '', $code).')', 'display'=>$display);
    $result_tid = isset($res['TID']) && is_scalar($res['TID']) ? (string)$res['TID'] : '';
    $auth_code = isset($res['AuthCode']) && is_scalar($res['AuthCode']) ? (string)$res['AuthCode'] : '';
    $auth_date = isset($res['AuthDate']) && is_scalar($res['AuthDate']) ? (string)$res['AuthDate'] : '';
    if (!preg_match('/\A[0-9A-Za-z]{1,40}\z/D', $result_tid) || !preg_match('/\A[0-9A-Za-z]{1,40}\z/D', $auth_code) || !preg_match('/\A[0-9]{0,14}\z/D', $auth_date)
        || (isset($res['Moid']) && (string)$res['Moid'] !== $moid)) {
        return array('ok'=>false, 'definite'=>false, 'display'=>$display);
    }
    $display['tid'] = $result_tid;
    $display['cash_no'] = $auth_code;
    $display['auth_date'] = $auth_date;
    return array('ok'=>true, 'cash_no'=>$auth_code, 'info'=>array('TID'=>$result_tid, 'ApplNum'=>$auth_code, 'AuthDate'=>$auth_date), 'display'=>$display);
}
