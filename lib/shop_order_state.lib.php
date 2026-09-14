<?php
if (!defined('_GNUBOARD_')) exit;

// 주문별 복귀 인증과 승인 기록. DDL은 설치 SQL/명시적 마이그레이션에서만 실행한다.
function &shop_order_runtime()
{
    static $runtime = array('locks'=>array(), 'rows'=>array(), 'active'=>'');
    return $runtime;
}

function shop_order_state_table()
{
    global $g5;
    return isset($g5['g5_shop_order_access_table']) ? $g5['g5_shop_order_access_table'] : G5_SHOP_TABLE_PREFIX.'order_access';
}

function shop_order_lock_name($kind, $id)
{
    return substr('g5oa_'.hash('sha256', G5_MYSQL_DB.G5_SHOP_TABLE_PREFIX.$kind.$id), 0, 64);
}

function shop_order_state_lock($kind, $id)
{
    $r =& shop_order_runtime();
    $key = shop_order_lock_name($kind, $id);
    shop_order_state_check_locks();
    if (isset($r['locks'][$key])) return;
    // MySQL 5.0은 연결당 이름 잠금 하나만 유지한다. 업무/포인트 연결과도 분리한다.
    $mysqli = function_exists('mysqli_connect') && G5_MYSQLI_USE;
    $host = G5_MYSQL_HOST;
    if (substr($host, 0, 2) === 'p:') $host = substr($host, 2);
    if ($mysqli) {
        try { $link = @mysqli_connect($host, G5_MYSQL_USER, G5_MYSQL_PASSWORD, G5_MYSQL_DB); }
        catch (Exception $e) { $link = false; }
    } else {
        $link = @mysql_connect($host, G5_MYSQL_USER, G5_MYSQL_PASSWORD, true);
    }
    if (!$link) shop_order_access_fail();
    $lock = array('link'=>$link, 'mysqli'=>$mysqli);
    // 실패나 예외에서도 이 연결만 닫아 획득 중인 잠금까지 해제한다.
    if (empty($r['shutdown_registered'])) {
        register_shutdown_function('shop_order_state_unlock_all');
        $r['shutdown_registered'] = true;
    }
    $r['locks'][$key] = $lock;
    if ($mysqli) {
        try { $result = @mysqli_query($link, "SELECT GET_LOCK('$key', 3) AS acquired"); }
        catch (Exception $e) { $result = false; }
        $row = $result ? mysqli_fetch_assoc($result) : false;
    } else {
        $result = @mysql_query("SELECT GET_LOCK('$key', 3) AS acquired", $link);
        $row = $result ? mysql_fetch_assoc($result) : false;
    }
    if (!$row || (int)$row['acquired'] !== 1) {
        shop_order_state_unlock($kind, $id);
        shop_order_access_fail();
    }
}

// 잠금 연결이 끊겼거나 자동 재접속되면 기존 잠금을 보유한 것으로 취급하지 않는다.
function shop_order_state_check_locks()
{
    $r =& shop_order_runtime();
    foreach ($r['locks'] as $key=>$lock) {
        $sql = "SELECT IS_USED_LOCK('$key') = CONNECTION_ID() AS owned";
        if ($lock['mysqli']) {
            try { $result = @mysqli_query($lock['link'], $sql); }
            catch (Exception $e) { $result = false; }
            $row = $result ? mysqli_fetch_assoc($result) : false;
        } else {
            $result = @mysql_query($sql, $lock['link']);
            $row = $result ? mysql_fetch_assoc($result) : false;
        }
        if (!$row || (int)$row['owned'] !== 1) shop_order_access_fail();
    }
}

function shop_order_state_unlock($kind, $id)
{
    $r =& shop_order_runtime();
    $key = shop_order_lock_name($kind, $id);
    if (!isset($r['locks'][$key])) return;
    $lock = $r['locks'][$key];
    unset($r['locks'][$key]);
    // 비영속 연결 종료 자체가 해당 연결의 잠금을 해제한다.
    if ($lock['mysqli']) mysqli_close($lock['link']);
    else mysql_close($lock['link']);
}

function shop_order_state_unlock_all()
{
    $r =& shop_order_runtime();
    foreach ($r['locks'] as $lock) {
        if ($lock['mysqli']) mysqli_close($lock['link']);
        else mysql_close($lock['link']);
    }
    $r['locks'] = array();
}

function shop_order_state_row($id)
{
    if (!shop_order_access_id((string)$id)) shop_order_access_fail();
    return sql_fetch("select * from ".shop_order_state_table()." where od_id='$id'", false);
}

function shop_order_state_meta($id)
{
    if (!shop_order_access_id((string)$id)) shop_order_access_fail();
    return sql_fetch("select od_id,token_hash,pg,status,expires from ".shop_order_state_table()." where od_id='$id'", false);
}

function shop_order_state_write($id, $fields)
{
    shop_order_state_check_locks();
    $allowed = array('status','payment_key','response_json','state_json','token_hash','expires','updated_at');
    $set = array();
    foreach ($fields as $key=>$value) {
        if (!in_array($key, $allowed, true)) shop_order_access_fail();
        $set[] = "$key='".sql_escape_string((string)$value)."'";
    }
    $set[] = 'updated_at='.G5_SERVER_TIME;
    if (!sql_query("update ".shop_order_state_table()." set ".implode(',', $set)." where od_id='$id'", false)) shop_order_access_fail();
}

function shop_order_state_token($id)
{
    $token = isset($_REQUEST['g5_order_state']) ? $_REQUEST['g5_order_state'] : '';
    if ($token === '') {
        $states = get_session('ss_order_data_access');
        $token = isset($states[$id]['token']) ? $states[$id]['token'] : '';
    }
    if (!is_string($token) || !preg_match('/\A'.preg_quote((string)$id, '/').'\.[a-f0-9]{64}\z/D', $token)) return '';
    return $token;
}

function shop_order_checkout_fields($id, $personal = false)
{
    if (!shop_order_access_id((string)$id)) return '';
    $map = get_session('ss_order_checkouts');
    if (!is_array($map)) $map = array();
    foreach ($map as $key=>$value) if ($value['expires'] <= G5_SERVER_TIME) unset($map[$key]);
    if (count($map) >= 30) array_shift($map);
    $nonce = shop_order_random_hex(24);
    $map[(string)$id] = array('nonce'=>$nonce, 'expires'=>G5_SERVER_TIME+7200,
        'cart'=>(string)get_session(get_session('ss_direct') ? 'ss_cart_direct' : 'ss_cart_id'),
        'direct'=>(bool)get_session('ss_direct'), 'personal'=>$personal,
        'personal_hash'=>get_session('ss_personalpay_hash'));
    set_session('ss_order_checkouts', $map);
    return '<input type="hidden" name="g5_checkout_id" value="'.get_text($id).'">'.
        '<input type="hidden" name="g5_checkout_nonce" value="'.$nonce.'">';
}

function shop_order_checkout_restore()
{
    if (!isset($_POST['g5_checkout_id'])) return; // 기존 사용자 스킨은 현재 세션 바인딩을 사용한다.
    $id = $_POST['g5_checkout_id']; $nonce = isset($_POST['g5_checkout_nonce']) ? $_POST['g5_checkout_nonce'] : '';
    $map = get_session('ss_order_checkouts');
    if (!shop_order_access_id($id) || !is_string($nonce) || !isset($map[$id]) ||
        !shop_order_equals($map[$id]['nonce'], $nonce) || $map[$id]['expires'] <= G5_SERVER_TIME) shop_order_access_fail();
    $c = $map[$id];
    if ($c['personal'] !== !empty($_POST['pp_id']) || ($c['personal'] && (string)$_POST['pp_id'] !== $id)) shop_order_access_fail();
    set_session('ss_order_id', $id); set_session('ss_direct', $c['direct']);
    set_session($c['direct'] ? 'ss_cart_direct' : 'ss_cart_id', $c['cart']);
    if ($c['personal']) {
        set_session('ss_personalpay_id', $id); set_session('ss_personalpay_hash', $c['personal_hash']);
    }
}

function shop_order_state_save($id, $state)
{
    shop_order_state_lock('order', $id);
    $previous = shop_order_state_meta($id);
    if ($previous) {
        $token = shop_order_state_token($id);
        if (!$token || !shop_order_equals($previous['token_hash'], hash('sha256', $token)) ||
            $previous['status'] !== 'pending') shop_order_access_fail();
    }
    $token = $id.'.'.shop_order_random_hex(32);
    $state['version'] = 2;
    unset($state['token'], $state['payment_key']);
    $json = json_encode($state);
    if ($json === false) shop_order_access_fail();
    $table = shop_order_state_table();
    $fields = "token_hash='".hash('sha256', $token)."', pg='".sql_escape_string($state['pg'])."', ".
        "cart_id='".sql_escape_string($state['cart'])."', status='pending', expires=".(int)$state['expires'].
        ", state_json='".sql_escape_string($json)."', payment_key='', response_json='', updated_at=".G5_SERVER_TIME;
    $query = $previous ? "update $table set $fields where od_id='$id'" : "insert into $table set od_id='$id', $fields";
    if (!sql_query($query, false)) shop_order_access_fail();
    $state['token'] = $token;
    $states = get_session('ss_order_data_access'); if (!is_array($states)) $states = array();
    $states[$id] = $state; set_session('ss_order_data_access', $states);
    header('Cache-Control: no-store, private'); header('Referrer-Policy: no-referrer');
    header('X-G5-Order-State: '.$token);
    return $state;
}

function shop_order_state_can_save($id)
{
    shop_order_state_lock('order', $id);
    $old = shop_order_state_meta($id);
    if (!$old) return;
    $token = shop_order_state_token($id);
    if ($old['status'] !== 'pending' || !$token || !shop_order_equals($old['token_hash'], hash('sha256', $token))) shop_order_access_fail();
}

function shop_order_state_load($id, $pg)
{
    global $g5, $member, $is_member, $is_guest;
    if (!shop_order_access_id($id)) shop_order_access_fail();
    shop_order_state_lock('order', $id);
    $row = shop_order_state_meta($id);
    if (!$row) {
        // 1차 패치가 만든 세션/본문 해시가 검증되는 요청만 전환한다.
        // PAYREQ_MAP 또는 주문번호만 있는 과거 요청은 legacy 검증에서도 거부된다.
        $data = shop_order_access_load_legacy($id, $pg);
        $states = get_session('ss_order_data_access');
        shop_order_state_save($id, $states[$id]);
        $row = shop_order_state_meta($id);
    }
    $token = shop_order_state_token($id);
    if (!$token || $row['pg'] !== $pg || !shop_order_equals($row['token_hash'], hash('sha256', $token))) shop_order_access_fail();
    if (!in_array($row['status'], array('pending','approving','approved','finalizing'), true)) shop_order_access_fail();
    if ((int)$row['expires'] <= G5_SERVER_TIME && $row['status'] === 'pending') shop_order_access_fail();
    $row = shop_order_state_row($id);
    $state = json_decode($row['state_json'], true);
    if (!is_array($state) || !isset($state['version']) || $state['version'] !== 2) shop_order_access_fail();
    $current = isset($member['mb_id']) ? (string)$member['mb_id'] : '';
    if ($current !== '' && $current !== $state['member']) shop_order_access_fail();
    // 결제 토큰은 해당 주문 처리에만 회원 컨텍스트를 제공한다. 로그인 세션을 발급하지 않는다.
    if ($current === '' && $state['member'] !== '') {
        $owner = get_member($state['member']);
        if (empty($owner['mb_id']) || !empty($owner['mb_leave_date']) || !empty($owner['mb_intercept_date'])) shop_order_access_fail();
        $member = $owner; $is_member = true; $is_guest = false;
    }
    if ($state['personal']) {
        $done = sql_fetch("select pp_tno, pp_use, pp_price, pp_time from {$g5['g5_shop_personalpay_table']} where pp_id='$id'");
        if (!$done || !$done['pp_use'] || $done['pp_tno'] ||
            !shop_order_equals($state['personal_hash'], md5($id.$done['pp_price'].$done['pp_time']))) shop_order_access_fail();
    } else {
        $done = sql_fetch("select od_id from {$g5['g5_shop_order_table']} where od_id='$id'");
        if (!empty($done['od_id'])) shop_order_access_fail(); // 부분 저장도 자동 재승인하지 않는다.
    }
    if ($row['status'] === 'finalizing') {
        if ($state['personal']) shop_order_access_fail();
        $cart_id = sql_escape_string($state['cart']);
        $remaining = sql_fetch("select count(*) as cnt from {$g5['g5_shop_cart_table']} where od_id='$cart_id' and ct_select='1' and ct_status='쇼핑'");
        if (empty($remaining['cnt']) || $row['response_json'] === '') shop_order_access_fail();
        shop_order_state_write($id, array('status'=>'approved'));
        $row['status'] = 'approved';
    }
    $rows = sql_query("select cart_id,mb_id,dt_pg,dt_time from {$g5['g5_shop_order_data_table']} where od_id='$id'");
    if (sql_num_rows($rows) !== 1) shop_order_access_fail();
    $meta = sql_fetch_array($rows);
    if ((string)$meta['cart_id'] !== $state['cart'] || $meta['mb_id'] !== $state['member'] ||
        $meta['dt_pg'] !== $pg || $meta['dt_time'] !== $state['time']) shop_order_access_fail();
    $temp = sql_fetch("select dt_data from {$g5['g5_shop_order_data_table']} where od_id='$id'");
    if (empty($temp['dt_data']) || !shop_order_equals($state['digest'], hash('sha256', $temp['dt_data']))) shop_order_access_fail();
    $data = shop_order_decode_data($temp['dt_data']);
    if (!is_array($data) || !empty($data['pp_id']) !== $state['personal'] ||
        ($state['personal'] && (string)$data['pp_id'] !== $id)) shop_order_access_fail();
    unset($data['od_pwd']);
    $state['token'] = $token;
    if ($row['payment_key'] !== '') $state['payment_key'] = $row['payment_key'];
    $states = get_session('ss_order_data_access'); if (!is_array($states)) $states = array();
    $states[$id] = $state; set_session('ss_order_data_access', $states);
    set_session('ss_order_id', $id); set_session('ss_direct', $state['direct']);
    set_session($state['direct'] ? 'ss_cart_direct' : 'ss_cart_id', $state['cart']);
    if ($state['personal']) { set_session('ss_personalpay_id', $id); set_session('ss_personalpay_hash', $state['personal_hash']); }
    $r =& shop_order_runtime(); $r['rows'][$id] = $row; $r['active'] = $id;
    $data['g5_order_state'] = $token;
    return $data;
}

function shop_order_state_prepare($personal)
{
    global $default;
    $token = isset($_REQUEST['g5_order_state']) ? $_REQUEST['g5_order_state'] : '';
    $id = (string)get_session($personal ? 'ss_personalpay_id' : 'ss_order_id');
    if ($token !== '') {
        if (!is_string($token) || !preg_match('/\A([0-9]{1,20})\.[a-f0-9]{64}\z/D', $token, $m)) shop_order_access_fail();
        $id = $m[1];
    }
    $states = get_session('ss_order_data_access');
    if ($token === '' && empty($states[$id])) return; // 임시 저장 없는 기존 PG/무통장 흐름
    $row = shop_order_state_meta($id);
    $pg = $row ? $row['pg'] : (isset($states[$id]['pg']) ? $states[$id]['pg'] : '');
    $data = shop_order_access_load($id, $pg);
    if (!empty($data['pp_id']) !== $personal) shop_order_access_fail();
    $default['de_pg_service'] = $pg;
    // 결제 복귀의 업무 필드는 저장 당시 서버 데이터만 사용한다.
    $fields = array_merge(shop_order_data_fields($personal ? 1 : 0), array('pp_id','sw_direct','od_temp_point','od_coupon','od_send_coupon','cp_id','cp_price','it_id','od_cp_id','sc_cp_id','od_hope_date','ad_default','ad_subject','good_mny','amountValue','comm_tax_mny','comm_vat_mny','comm_free_mny'));
    foreach ($fields as $key) {
        $value = isset($data[$key]) ? $data[$key] : '';
        $_POST[$key] = $value; $GLOBALS[$key] = $value;
    }
    if (!$personal) {
        $s = get_session('ss_order_data_access');
        shop_order_state_lock('cart', $s[$id]['cart']);
        $cart = sql_escape_string($s[$id]['cart']);
        $busy = sql_fetch("select od_id from ".shop_order_state_table()." where cart_id='$cart' and od_id<>'$id' and status in ('approving','approved','finalizing','unknown','cancel_pending') limit 1");
        if ($busy) shop_order_access_fail();
    }
}

function shop_order_state_begin($id, $key, $expected)
{
    shop_order_state_lock('order', $id);
    $row = shop_order_state_row($id);
    if (!$row || !in_array($row['status'], array('pending','approving','approved'), true)) shop_order_access_fail();
    if ($row['payment_key'] !== '' && !shop_order_equals($row['payment_key'], $key)) shop_order_access_fail();
    $state = json_decode($row['state_json'], true);
    if (!is_array($state) || (int)$expected <= 0 || (isset($state['expected_amount']) && (int)$state['expected_amount'] !== (int)$expected)) shop_order_access_fail();
    if ($row['status'] === 'pending') {
        $state['expected_amount'] = (int)$expected;
        $state['approval_started'] = G5_SERVER_TIME;
        shop_order_state_write($id, array('status'=>'approving','payment_key'=>$key,'state_json'=>json_encode($state)));
    }
    return $row;
}

function shop_order_state_approved($id, $response)
{
    $json = json_encode($response);
    if ($json === false) shop_order_access_fail();
    shop_order_state_write($id, array('status'=>'approved','response_json'=>$json));
}

function shop_order_state_finalizing()
{
    $r =& shop_order_runtime();
    if ($r['active'] === '') return;
    $row = shop_order_state_row($r['active']);
    if ($row && $row['status'] === 'approved') shop_order_state_write($r['active'], array('status'=>'finalizing'));
}

function shop_order_state_complete($id)
{
    $row = shop_order_state_row($id);
    if (!$row) return;
    shop_order_state_write($id, array('status'=>'completed','state_json'=>'','response_json'=>'','token_hash'=>'','payment_key'=>''));
}

function shop_order_state_cancel($status)
{
    $r =& shop_order_runtime();
    if ($r['active'] !== '') shop_order_state_write($r['active'], array('status'=>$status));
}

function shop_order_toss_approve($toss, $id, $key, $expected)
{
    $row = shop_order_state_begin($id, $key, $expected);
    $toss->headers[] = 'Idempotency-Key: g5-confirm-'.hash('sha256', (defined('G5_MYSQL_DB') ? G5_MYSQL_DB : '').G5_SHOP_TABLE_PREFIX.$id.$key);
    // 승인 응답을 놓쳤거나 저장 후 중단된 경우에도 먼저 PG에 현재 상태를 조회한다.
    if ($row['status'] !== 'pending') {
        if (!$toss->getPaymentByOrderId($id)) shop_order_access_fail();
        $response = $toss->responseData;
        if (!isset($response['orderId'],$response['paymentKey'],$response['totalAmount'],$response['status']) ||
            $response['orderId'] !== $id || $response['paymentKey'] !== $key || (int)$response['totalAmount'] !== (int)$expected) shop_order_access_fail();
        if (in_array($response['status'], array('CANCELED','ABORTED','EXPIRED'), true)) {
            shop_order_state_write($id, array('status'=>$response['status'] === 'CANCELED' ? 'cancelled' : 'failed'));
            shop_order_access_fail();
        }
        if ($response['status'] === 'IN_PROGRESS' && $row['status'] === 'approving') {
            $state = json_decode($row['state_json'], true);
            // PG가 미승인을 확인한 경우에만 동일 본문/멱등 키로 재시도한다(15일보다 짧게 제한).
            if (empty($state['approval_started']) || G5_SERVER_TIME - $state['approval_started'] > 14*86400 || !$toss->approvePayment()) shop_order_access_fail();
            $response = $toss->responseData;
        }
    } else {
        if (!$toss->approvePayment()) {
            // 네트워크 오류를 결제 실패로 단정하지 않는다. 다음 시도는 조회부터 시작한다.
            shop_order_access_fail();
        }
        $response = $toss->responseData;
    }
    if (!isset($response['orderId'],$response['paymentKey'],$response['totalAmount'],$response['status'],$response['method']) ||
        $response['orderId'] !== $id || $response['paymentKey'] !== $key || (int)$response['totalAmount'] !== (int)$expected ||
        !($response['status'] === 'DONE' || ($response['status'] === 'WAITING_FOR_DEPOSIT' && $response['method'] === '가상계좌'))) shop_order_access_fail();
    shop_order_state_approved($id, $response);
    return true;
}

function shop_order_kcp_result_fields()
{
    return explode(' ', 'tno amount pnt_issue card_cd card_name app_time app_no noinf quota partcanc_yn bankname bank_name bank_code depositor account pt_idno pnt_amount pnt_app_time pnt_app_no add_pnt use_pnt rsv_pnt commid mobile_no tk_van_code tk_app_no cash_authno cash_tr_code escw_yn res_cd res_msg app_kakaomny_time kakaomny_mny kcp_pay_method od_other_pay_type');
}

function shop_order_state_abort_pending()
{
    $token = isset($_REQUEST['g5_order_state']) ? $_REQUEST['g5_order_state'] : '';
    if (!is_string($token) || !preg_match('/\A([0-9]{1,20})\.[a-f0-9]{64}\z/D', $token, $m)) shop_order_access_fail();
    shop_order_state_lock('order', $m[1]);
    $row = shop_order_state_meta($m[1]);
    if (!$row || !shop_order_equals($row['token_hash'], hash('sha256', $token))) shop_order_access_fail();
    // PG 승인 여부가 불명확한 상태에는 실패 복귀가 와도 데이터를 지우지 않는다.
    if ($row['status'] === 'pending') shop_order_state_write($m[1], array('status'=>'failed'));
    shop_order_access_fail();
}
