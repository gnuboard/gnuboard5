<?php
if (!defined('_GNUBOARD_')) exit;

function get_shop_skin_dirs($is_mobile=false)
{
    global $config;

    $skin_path = $is_mobile ? G5_MOBILE_PATH.'/'.G5_SKIN_DIR : G5_SKIN_PATH;
    $skins = get_skin_dir('shop', $skin_path);

    if(defined('G5_THEME_PATH') && $config['cf_theme']) {
        $theme_skin_path = $is_mobile ? G5_THEME_MOBILE_PATH.'/'.G5_SKIN_DIR : G5_THEME_PATH.'/'.G5_SKIN_DIR;
        $dirs = get_skin_dir('shop', $theme_skin_path);
        if(!empty($dirs)) {
            foreach($dirs as $dir) {
                $skins[] = 'theme/'.$dir;
            }
        }
    }

    return $skins;
}

function check_shop_skin_dir($skin_dir, $label, $is_mobile=false)
{
    if( $skin_dir === '' ) {
        return;
    }

    if( preg_match('#\.+(\/|\\\)#', $skin_dir) ){
        alert($label.' 폴더명에 포함될수 없는 문자가 들어있습니다.');
    }

    if( ! is_include_path_check($skin_dir, 1) ){
        alert('오류 : 데이터폴더가 포함된 path 또는 잘못된 path 를 포함할수 없습니다.');
    }

    if( ! in_array($skin_dir, get_shop_skin_dirs($is_mobile), true) ){
        alert($label.'을 올바르게 선택해 주십시오.');
    }
}

// 상품옵션별재고 또는 상품재고에 더하기
function add_io_stock($it_id, $ct_qty, $io_id="", $io_type=0)
{
    global $g5;

    if($io_id) {
        $sql = " update {$g5['g5_shop_item_option_table']}
                    set io_stock_qty = io_stock_qty + '{$ct_qty}'
                    where it_id = '{$it_id}'
                      and io_id = '{$io_id}'
                      and io_type = '{$io_type}' ";
    } else {
        $sql = " update {$g5['g5_shop_item_table']}
                    set it_stock_qty = it_stock_qty + '{$ct_qty}'
                    where it_id = '{$it_id}' ";
    }
    return sql_query($sql);
}


// 상품옵션별재고 또는 상품재고에서 빼기
function subtract_io_stock($it_id, $ct_qty, $io_id="", $io_type=0)
{
    global $g5;

    if($io_id) {
        $sql = " update {$g5['g5_shop_item_option_table']}
                    set io_stock_qty = io_stock_qty - '{$ct_qty}'
                    where it_id = '{$it_id}'
                      and io_id = '{$io_id}'
                      and io_type = '{$io_type}' ";
    } else {
        $sql = " update {$g5['g5_shop_item_table']}
                    set it_stock_qty = it_stock_qty - '{$ct_qty}'
                    where it_id = '{$it_id}' ";
    }
    return sql_query($sql);
}


// 주문과 장바구니의 상태를 변경한다.
function change_status($od_id, $current_status, $change_status)
{
    global $g5;

    $sql = " update {$g5['g5_shop_order_table']} set od_status = '{$change_status}' where od_id = '{$od_id}' and od_status = '{$current_status}' ";
    sql_query($sql, true);

    $sql = " update {$g5['g5_shop_cart_table']} set ct_status = '{$change_status}' where od_id = '{$od_id}' and ct_status = '{$current_status}' ";
    sql_query($sql, true);
}


// 주문서에 입금시 update
function order_update_receipt($od_id)
{
    global $g5;

    $sql = " update {$g5['g5_shop_order_table']} set od_receipt_price = od_misu, od_misu = 0, od_receipt_time = '".G5_TIME_YMDHIS."' where od_id = '$od_id' and od_status = '입금' ";
    return sql_query($sql);
}


// 주문서에 배송시 update
function order_update_delivery($od_id, $mb_id, $change_status, $delivery)
{
    global $g5;

    if($change_status != '배송')
        return;

    $sql = " update {$g5['g5_shop_order_table']} set od_delivery_company = '".sql_real_escape_string($delivery['delivery_company'])."', od_invoice = '".sql_real_escape_string($delivery['invoice'])."', od_invoice_time = '".sql_real_escape_string($delivery['invoice_time'])."' where od_id = '$od_id' and od_status = '준비' ";
    sql_query($sql);

    $sql = " select * from {$g5['g5_shop_cart_table']} where od_id = '$od_id' ";
    $result = sql_query($sql);

    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        // 재고를 사용하지 않았다면
        $stock_use = $row['ct_stock_use'];

        if(!$row['ct_stock_use'])
        {
            // 재고에서 뺀다.
            subtract_io_stock($row['it_id'], $row['ct_qty'], $row['io_id'], $row['io_type']);
            $stock_use = 1;

            $sql = " update {$g5['g5_shop_cart_table']} set ct_stock_use  = '$stock_use' where ct_id = '{$row['ct_id']}' ";
            sql_query($sql);
        }
    }
}

// 처리내용 SMS
function conv_sms_contents($od_id, $contents)
{
    global $g5, $config, $default;

    $sms_contents = '';

    if ($od_id && $config['cf_sms_use'] == 'icode')
    {
        $sql = " select od_id, od_name, od_invoice, od_receipt_price, od_delivery_company
                    from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
        $od = sql_fetch($sql);

        $sms_contents = $contents;
        $sms_contents = str_replace("{이름}", $od['od_name'], $sms_contents);
        $sms_contents = str_replace("{입금액}", number_format($od['od_receipt_price']), $sms_contents);
        $sms_contents = str_replace("{택배회사}", $od['od_delivery_company'], $sms_contents);
        $sms_contents = str_replace("{운송장번호}", $od['od_invoice'], $sms_contents);
        $sms_contents = str_replace("{주문번호}", $od['od_id'], $sms_contents);
        $sms_contents = str_replace("{회사명}", $default['de_admin_company_name'], $sms_contents);
    }

    return stripslashes($sms_contents);
}

function pg_setting_check($is_print=false){
	global $g5, $config, $default, $member;

	$msg = '';
	$pg_msg = '';
    $pg_test_conf_link = G5_ADMIN_URL.'/shop_admin/configform.php#de_card_test1';

	if( $default['de_card_test'] ){
		if( $default['de_pg_service'] === 'kcp' && $default['de_kcp_mid'] && $default['de_kcp_site_key'] ){
			$pg_msg = 'NHN KCP';
		} else if ( $default['de_pg_service'] === 'lg' && $config['cf_lg_mid'] && $config['cf_lg_mert_key'] ){
			$pg_msg = 'LG유플러스';
		} else if ( $default['de_pg_service'] === 'toss' && $config['cf_lg_mid'] && $config['cf_toss_client_key'] && $config['cf_toss_secret_key'] ){
            $msg .= '<div class="admin_pg_notice od_test_caution">(주의!) 토스페이먼츠 결제의 결제 설정이 현재 테스트결제로 되어 있습니다.<br>반드시 <a href="#lg_info_anchor">상점 API키</a>를 <u>[테스트]키</u>로 설정한 후 테스트결제를 진행해야합니다.<br>쇼핑몰 운영 시에는 실결제로 전환하여 <u>[라이브]키</u>로 설정해 주시기 바랍니다.<br>아래 링크를 클릭하여 실결제로 설정하여 운영해 주세요.<br><a href="'.$pg_test_conf_link.'" class="pg_test_conf_link">'.$pg_test_conf_link.'</a></div>';
		} else if ( $default['de_pg_service'] === 'inicis' && $default['de_inicis_mid'] && $default['de_inicis_sign_key'] ){
			$pg_msg = 'KG이니시스';
		} else if ( $default['de_pg_service'] === 'nicepay' && $default['de_nicepay_mid'] && $default['de_nicepay_key'] ){
			$pg_msg = 'NICEPAY';
		}
	}

    if( function_exists('is_use_easypay') && is_use_easypay('global_nhnkcp') ){
        if(!extension_loaded('soap') || !class_exists('SOAPClient')) {
            $msg .= '<script>'.PHP_EOL;
            $msg .= 'alert("PHP SOAP 확장모듈이 설치되어 있지 않습니다.\n모바일 쇼핑몰 결제 때 사용되오니 SOAP 확장 모듈을 설치하여 주십시오.\nNHN_KCP (네이버페이) 모바일결제가 되지 않습니다.");'.PHP_EOL;
            $msg .= '</script>'.PHP_EOL;
        }
    }

	if( $pg_msg ){
		$msg .= '<div class="admin_pg_notice od_test_caution">(주의!) '.$pg_msg.' 결제의 결제 설정이 현재 테스트결제 로 되어 있습니다.<br>테스트결제시 실제 결제가 되지 않으므로, 쇼핑몰 운영중이면 반드시 실결제로 설정하여 운영하셔야 합니다.<br>아래 링크를 클릭하여 실결제로 설정하여 운영해 주세요.<br><a href="'.$pg_test_conf_link.'" class="pg_test_conf_link">'.$pg_test_conf_link.'</a></div>';
	}
	
	if( $is_print ){
		echo $msg;
	} else{
		return $msg;
	}
}

function is_cancel_shop_pg_order($od){
    global $default;

    $is_od_pg_cancel = false;

    if (($od['od_settle_case'] == '신용카드' || $od['od_settle_case'] == '간편결제' || $od['od_settle_case'] == 'KAKAOPAY') || ($od['od_pg'] == 'inicis' && is_inicis_order_pay($od['od_settle_case']))) {
        $is_od_pg_cancel = true;
    }

    if ($od['od_pg'] === 'nicepay' && in_array($od['od_settle_case'], array('계좌이체', '휴대폰', '가상계좌'))) {
        $is_od_pg_cancel = true;
    }

    if($od['od_pg'] === 'toss' && in_array($od['od_settle_case'], array('계좌이체', '휴대폰'))) {
        $is_od_pg_cancel = true;
    }

    if ($od['od_pg'] === 'inicis' && !empty($default['de_inicis_pro_use']) && !empty($od['od_tno'])
        && in_array($od['od_settle_case'], array('신용카드', '간편결제', '가상계좌', '계좌이체', '휴대폰', '삼성페이', 'lpay', 'inicis_kakaopay'))) {
        $is_od_pg_cancel = true;
    }

    return $is_od_pg_cancel;
}

function check_order_inicis_pro_payments($run_external = true){
    include_once(G5_SHOP_PATH.'/inicis/pro/inicis_pro.lib.php');

    // 사이트 이용 흐름에서 실행되는 경량 감시는 잠금 대기 없이 즉시 시도하고,
    // 획득하지 못하면 다른 요청이 이미 처리 중이므로 그대로 넘어간다.
    $monitor_lock = inicis_pro_lock('monitor_process', $run_external ? 10 : 0);
    if ($monitor_lock === '')
        return false;

    check_order_inicis_pro_payments_run($run_external);
    inicis_pro_unlock($monitor_lock);

    return true;
}

// 서버 스케줄러 없이 사이트 접속 흐름에서 실행되는 경량 감시.
// 방문자 응답을 먼저 종료해 페이지 지연 없이 뒤에서 처리한다.
function check_order_inicis_pro_payments_inline(){
    global $default;

    if (empty($default['de_pg_service']) || $default['de_pg_service'] !== 'inicis' || empty($default['de_inicis_pro_use']))
        return;

    if (function_exists('fastcgi_finish_request'))
        @fastcgi_finish_request();
    @ignore_user_abort(true);

    check_order_inicis_pro_payments(false);
}

function check_order_inicis_pro_payments_run($run_external = true){
    global $g5, $config, $default;

    include_once(G5_SHOP_PATH.'/inicis/pro/inicis_pro.lib.php');
    include_once(G5_ADMIN_PATH.'/shop_admin/inicislog.lib.php');

    if (!inicis_pro_audit_schema_ready()) {
        // 스키마가 준비되지 않았어도 감시 시각을 전진시켜, 사이트 접속 흐름의
        // 인라인 감시가 매 요청마다 재등록·재실행되는 것을 막는다.
        if (array_key_exists('de_inicis_pro_monitor_at', $default))
            sql_query(" update {$g5['g5_shop_default_table']}
                           set de_inicis_pro_monitor_at = '".G5_TIME_YMDHIS."' ", false);
        return;
    }

    $log_days = isset($default['de_inicis_pro_log_days']) ? (int) $default['de_inicis_pro_log_days'] : 365;
    if ($log_days >= 30) {
        inicis_pro_purge_events($log_days, 500);
        inicis_pro_purge_payloads($log_days, 200);
    }
    $summary_days = isset($default['de_inicis_pro_summary_days']) ? (int) $default['de_inicis_pro_summary_days'] : 1825;
    if ($summary_days >= 365)
        inicis_pro_purge_summaries($summary_days, 50);

    $tables = inicis_pro_audit_tables();
    $expired_count = 0;
    $expired_result = sql_query(" select ip_oid from `{$tables['summary']}`
                                  where ip_status = 'request_ready'
                                    and ip_updated_at < date_sub(now(), interval 30 minute)
                                  order by ip_updated_at asc
                                  limit 50 ", false);
    if ($expired_result) {
        while ($expired = sql_fetch_array($expired_result)) {
            if (inicis_pro_audit_write($expired['ip_oid'], 'request', 'request_expired', array(
                'source' => 'system',
                'message' => '결제창 요청 후 인증결과가 없어 미진행으로 정리'
            )))
                $expired_count++;
        }
    }

    $order_amount_sql = "(o.od_cart_price + o.od_send_cost + o.od_send_cost2 - o.od_cart_coupon - o.od_coupon - o.od_send_coupon - o.od_receipt_point)";
    $anomaly_sql = inicis_admin_anomaly_sql();
    $sql_from = " from `{$tables['summary']}` p
                   left join {$g5['g5_shop_order_table']} o on p.ip_order_type <> 'personal' and o.od_id = p.ip_oid
                   left join {$g5['g5_shop_personalpay_table']} pp on p.ip_order_type = 'personal' and pp.pp_id = p.ip_oid ";
    $sql_fields = " select p.*,
                           o.od_id as order_oid, o.od_tno as order_tid, o.od_status as order_status,
                           o.od_receipt_price as order_receipt_price, o.od_misu as order_misu, o.od_cancel_price as order_cancel_price,
                           o.od_refund_price as order_refund_price, o.od_settle_case as order_settle_case, $order_amount_sql as order_amount,
                           pp.pp_id as personal_oid, pp.pp_tno as personal_tid, pp.pp_price as personal_amount, pp.pp_receipt_price as personal_receipt_price ";

    $recover_result = sql_query(" select ip_oid from `{$tables['summary']}`
                                  where ip_tid = ''
                                    and ip_status in ('authentication_received','approval_started','validation_failed','order_saving')
                                    and ip_updated_at < date_sub(now(), interval 3 minute)
                                  order by ip_updated_at asc
                                  limit 5 ", false);
    if ($recover_result) {
        while ($recover_row = sql_fetch_array($recover_result))
            inicis_pro_recover_approved_tid($recover_row['ip_oid'], 'system');
    }

    $reconcile_count = 0;
    $reconcile_failed_count = 0;
    if ($run_external && empty($default['de_card_test']) && !empty($default['de_inicis_pro_reconcile_use']) && inicis_pro_get_iniapi_key() !== '') {
        $result = sql_query(" $sql_fields $sql_from
                               where p.ip_tid <> ''
                                 and p.ip_updated_at < date_sub(now(), interval 3 minute)
                                 and (p.ip_pg_checked_at is null or p.ip_pg_checked_at < date_sub(now(), interval 1 hour))
                                 and (p.ip_refund_required = '1'
                                      or $anomaly_sql
                                      or (p.ip_updated_at > date_sub(now(), interval 7 day)
                                          and (p.ip_status in ('order_saved','paid','paid_after_cancel','vbank_issued','canceled','refund_completed')
                                               or p.ip_cancel_status <> '')))
                               order by p.ip_refund_required desc, p.ip_updated_at asc
                               limit 10 ", false);
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $inquiry = inicis_pro_inquiry($row['ip_tid'], $row['ip_oid'], $row);
                if (inicis_pro_save_inquiry($row['ip_oid'], $inquiry, 'system') && !empty($inquiry['success']))
                    $reconcile_count++;
                else
                    $reconcile_failed_count++;
            }
        }
    }

    $anomaly_count_row = sql_fetch(" select count(*) as cnt $sql_from where $anomaly_sql ");
    $anomaly_count = isset($anomaly_count_row['cnt']) ? (int) $anomaly_count_row['cnt'] : 0;
    if ($run_external)
        $monitor_message = '미진행 '.$expired_count.'건 정리 / KG 대사 성공 '.$reconcile_count.'건, 실패 '.$reconcile_failed_count.'건 / 확인 필요 '.$anomaly_count.'건';
    else
        $monitor_message = '미진행 '.$expired_count.'건 정리 / 확인 필요 '.$anomaly_count.'건';
    if (array_key_exists('de_inicis_pro_monitor_at', $default)) {
        $monitor_set = "de_inicis_pro_monitor_at = '".G5_TIME_YMDHIS."'";
        if (isset($default['de_inicis_pro_monitor_message']))
            $monitor_set .= ", de_inicis_pro_monitor_message = '".sql_escape_string($monitor_message)."'";
        sql_query(" update {$g5['g5_shop_default_table']} set $monitor_set ", false);
    }

    // 테스트 거래는 상세 화면에서 직접 조회하며 운영 메일에는 포함하지 않는다.
    if (!empty($default['de_card_test']))
        return;

    if (empty($default['de_inicis_pro_alert_use']) || empty($config['cf_email_use']) || empty($config['cf_admin_email']))
        return;

    $result = sql_query(" $sql_fields $sql_from
                           where $anomaly_sql
                           order by p.ip_updated_at asc
                           limit 20 ", false);
    if (!$result)
        return;

    $alert_rows = array();
    $mail_msg = '';
    while ($row = sql_fetch_array($result)) {
        if (!inicis_admin_is_anomaly($row))
            continue;

        $alert_key = inicis_admin_alert_key($row);
        if (!empty($row['ip_alert_key']) && $row['ip_alert_key'] === $alert_key)
            continue;

        $pg_state = !empty($row['ip_pg_checked_at'])
            ? ($row['ip_pg_result_code'] === '00' ? inicis_admin_pg_status_label($row['ip_pg_status']) : '거래조회 실패')
            : '미조회';
        $mail_msg .= '<tr>'
            .'<td style="padding:6px;border:1px solid #ddd"><a href="'.G5_ADMIN_URL.'/shop_admin/inicislogview.php?oid='.urlencode($row['ip_oid']).'">'.get_text($row['ip_oid']).'</a></td>'
            .'<td style="padding:6px;border:1px solid #ddd">'.get_text(inicis_admin_status_label(inicis_admin_primary_status($row))).'</td>'
            .'<td style="padding:6px;border:1px solid #ddd">'.get_text($pg_state).'</td>'
            .'<td style="padding:6px;border:1px solid #ddd;text-align:right">'.number_format((int) $row['ip_amount']).'원</td>'
            .'</tr>';
        $alert_rows[] = array('oid' => $row['ip_oid'], 'key' => $alert_key);
    }

    if (!count($alert_rows))
        return;

    include_once(G5_LIB_PATH.'/mailer.lib.php');
    $content = '<p>KG이니시스 결제와 영카트 주문을 대조해야 하는 거래가 확인됐습니다.</p>'
        .'<p>자동으로 주문을 생성하거나 결제를 취소하지 않았습니다. KG이니시스 거래조회 결과와 상점관리자 거래내역을 확인한 후 조치해 주십시오.</p>'
        .'<table style="border-collapse:collapse"><thead><tr><th style="padding:6px;border:1px solid #ddd">주문번호</th><th style="padding:6px;border:1px solid #ddd">로컬 상태</th><th style="padding:6px;border:1px solid #ddd">KG 상태</th><th style="padding:6px;border:1px solid #ddd">금액</th></tr></thead><tbody>'
        .$mail_msg.'</tbody></table>';
    $sent = mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $config['cf_admin_email'], '['.$config['cf_title'].'] KG이니시스 결제 확인 필요', $content, 1);
    if (!$sent)
        return;

    foreach ($alert_rows as $alert_row) {
        sql_query(" update `{$tables['summary']}`
                       set ip_alerted_at = '".G5_TIME_YMDHIS."',
                           ip_alert_key = '".sql_escape_string($alert_row['key'])."'
                     where ip_oid = '".sql_escape_string($alert_row['oid'])."' ", false);
    }
}

function check_order_inicis_tmps(){
    global $g5, $config, $default, $member;

    $admin_cookie_time = get_cookie('admin_visit_time');

    if( ! $admin_cookie_time ){

        if ($default['de_pg_service'] === 'inicis' && !empty($default['de_inicis_pro_use'])) {
            check_order_inicis_pro_payments();
        } elseif( $default['de_pg_service'] === 'inicis' && empty($default['de_card_test']) ){
            $sql = " select * from {$g5['g5_shop_inicis_log_table']} where P_TID <> '' and P_TYPE in ('CARD', 'ISP', 'BANK') and P_MID <> '' and P_STATUS = '00' and is_mail_send = 0 and substr(P_AUTH_DT, 1, 14) < '".date('YmdHis', strtotime('-3 minutes', G5_SERVER_TIME))."' ";

            $result = sql_query($sql, false);
            
            if( !$result ){
                return;
            }

            $mail_msg = '';

            for($i=0;$row=sql_fetch_array($result);$i++){
                
                $oid = $row['oid'];
                $p_tid = $row['P_TID'];
                $p_mid = strtolower($row['P_MID']);

                if( in_array($p_mid, array('iniescrow0', 'inipaytest')) ) continue;

                $sql = "update {$g5['g5_shop_inicis_log_table']} set is_mail_send = 1 where oid = '".$oid."' and P_TID = '".$p_tid."' ";
                sql_query($sql);

                $sql = " select od_id from {$g5['g5_shop_order_table']} where od_id = '$oid' and od_tno = '$p_tid' ";
                $tmp = sql_fetch($sql);

                if( $tmp['od_id'] ) continue;

                $sql = " select pp_id from {$g5['g5_shop_personalpay_table']} where pp_id = '$oid' and pp_tno = '$p_tid' ";
                $tmp = sql_fetch($sql);

                if( $tmp['pp_id'] ) continue;

                $mail_msg .= '<a href="'.G5_ADMIN_URL.'/shop_admin/inorderform.php?od_id='.$oid.'" target="_blank" >미완료 발생 주문번호 : '.$oid.'</a><br><br>';
                
            }
            
            if( $mail_msg ){
                include_once(G5_LIB_PATH.'/mailer.lib.php');

                $mails = array_unique(array($member['mb_email'], $config['cf_admin_email']));

                foreach($mails as $mail_address){
                    if (!preg_match("/([0-9a-zA-Z_-]+)@([0-9a-zA-Z_-]+)\.([0-9a-zA-Z_-]+)/", $mail_address)) continue;

                    mailer($member['mb_nick'], $member['mb_email'], $mail_address, $config['cf_title'].' 사이트 미완료 주문 알림', '이니시스를 통해 결제한 주문건 중에서 미완료 주문이 발생했습니다.<br><br>발생된 원인으로는 장바구니 금액와 실결제 금액이 맞지 않는 경우, 네트워크 오류, 프로그램 오류, 알수 없는 오류 등이 있습니다.<br><br>아래 내용과 실제 주문내역, 이니시스 상점 관리자 에서 결제된 내용을 확인하여 조치를 취해 주세요.<br><br>'.$mail_msg, 0);
                }
            }
        }

        if( $default['de_pg_service'] == 'lg' && function_exists('check_log_folder') ){
            check_log_folder(G5_LGXPAY_PATH.'/lgdacom/log');
        }

        set_cookie('admin_visit_time', G5_SERVER_TIME, 3600);   //1시간 간격으로 체크
    }
}   //end function check_order_inicis_tmps;
