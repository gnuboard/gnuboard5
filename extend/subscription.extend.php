<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//------------------------------------------------------------------------------
// 정기결제 상수 모음 시작
//------------------------------------------------------------------------------

define('G5_USE_SUBSCRIPTION', true);

define('G5_SUBSCRIPTION_DIR',             'subscription');
define('G5_SUBSCRIPTION_PATH',            G5_PATH.'/'.G5_SUBSCRIPTION_DIR);
define('G5_SUBSCRIPTION_URL',             G5_URL.'/'.G5_SUBSCRIPTION_DIR);
define('G5_MSUBSCRIPTION_PATH', G5_MOBILE_PATH.'/'.G5_SUBSCRIPTION_DIR);
define('G5_MSUBSCRIPTION_URL',  G5_MOBILE_URL.'/'.G5_SUBSCRIPTION_DIR);


define('G5_SUBSCRIPTION_ADMIN_DIR',        'subscription_admin');
define('G5_SUBSCRIPTION_ADMIN_PATH',       G5_ADMIN_PATH.'/'.G5_SUBSCRIPTION_ADMIN_DIR);
define('G5_SUBSCRIPTION_ADMIN_URL',        G5_ADMIN_URL.'/'.G5_SUBSCRIPTION_ADMIN_DIR);

// NHN_KCP 정기결제 기본 pay_method 
define('SUBSCRIPTION_DEFAULT_PAYMETHOD',        'CARD');

// 정기결제 결제일 또는 배송일에 뒤에 붙는 기본 시분초 
define('SUBSCRIPTION_DEFAULT_TIME_SUFFIX',        '08:00:01');

// 정기결제에서 주문한 상품의 가격이 수정되면 메일을 보낸다.
define('IS_MAIL_SUBSCRIPTION_ITEM_PRICECHANGED', true);

// 정기결제 테이블명
if (!isset($g5['subscription_prefix'])) {
    $g5['subscription_prefix']                = G5_TABLE_PREFIX.'subscription_';
}
$g5['g5_subscription_config_table'] = $g5['subscription_prefix'] .'config';     // 정기결제 설정 테이블
$g5['g5_subscription_cart_table']          = $g5['subscription_prefix'] . 'cart';

$g5['g5_subscription_pay_table']            = $g5['subscription_prefix'] . 'pay';                   // 정기결제 결제 테이블
$g5['g5_subscription_pay_basket_table']            = $g5['subscription_prefix'] . 'pay_basket';     // 정기결제 결제 장바구니 기록 테이블
$g5['g5_subscription_order_table']            = $g5['subscription_prefix'] . 'order';
$g5['g5_subscription_order_data_table']   = $g5['subscription_prefix'] . 'order_data'; // 결제정보 임시저장 테이블
$g5['g5_subscription_mb_cardinfo_table']   = $g5['subscription_prefix'] . 'mb_cardinfo'; // 사용자 카드번호 키 저장테이블

$g5['g5_subscription_order_history_table'] = $g5['subscription_prefix'] . 'order_history'; // 주문정보 히스토리 테이블
$g5['g5_subscription_delay_schedules_table'] = $g5['subscription_prefix'] . 'delay_schedules'; // 구독 미루기 기록 테이블

$g5['g5_subscription_uniqid_table'] = $g5['subscription_prefix'] . 'uniqid'; // 정기구독 uniqid 테이블

$subscriptions_default = array(
'su_card_test' => 1,
'su_inicis_mid' => '',
'su_inicis_sign_key' => '',
'su_inicis_iniapi_key' => '',
'su_inicis_iniapi_iv' => '',
'de_subscription_skin' => 'basic',
'de_subscription_mobile_skin' => 'basic',
'su_pg_service' => 'kcp',    // kcp, inicis
// 'su_pg_service' => 'inicis',    // kcp, inicis
'su_hope_date_use' => 0,
'su_hope_date_after' => 0,
'su_card_use' => 1,     // 세금계산서
'su_bank_use' => 1,     // 무통장
'su_kcp_mid' => '',     // kcp 정기결제 사이트키
'su_kcp_group_id' => '', // kcp kcp_group_id
'su_kcp_cert_info' => '',   // kcp 인증서 정보
'su_nice_clientid' => '',   // 나이스페이 clientId
'su_nicepay_secretkey' => '',   // 나이스페이 secretKey
'su_tax_flag_use' => '',
);

$config['g5_subscriptions_options'] = array_merge($subscriptions_default, (array) sql_fetch("select * from `{$g5['g5_subscription_config_table']}` limit 1", false));

function get_subs_option($key) {
    global $config;
    
    if (isset($config['g5_subscriptions_options'][$key])) {
        return $config['g5_subscriptions_options'][$key];
    }
    
    return null;
}

function set_subs_option($key, $value) {
    global $config;
    
    if (isset($config['g5_subscriptions_options'][$key])) {
        $config['g5_subscriptions_options'][$key] = $value;
    }
}

function get_subscription_info_inputs() {
    
    $opts = get_subs_option('su_opt_settings');
    
    if ($opts) {
        return unserialize(base64_decode($opts));
    }
    
    // 기본값 지정
    return array ( 0 => array ( 'opt_id' => '1', 'opt_chk' => '', 'opt_input' => '1', 'opt_date_format' => 'week', 'opt_etc' => '', 'opt_print' => '', 'opt_use' => '1' ) );
}

function get_subscription_use_inputs() {
    
    $uses = get_subs_option('su_use_settings');
    
    if ($uses) {
        return unserialize(base64_decode($uses));
    }
    
    // 기본값 지정
    return array ( 0 => array ( 'use_id' => '1', 'use_chk' => '', 'use_input' => '1', 'use_print' => '', 'num_use' => '1', ) );
}

// 보안서버주소 설정
if (G5_HTTPS_DOMAIN) {
    define('G5_HTTPS_SUBSCRIPTION_URL', G5_HTTPS_DOMAIN.'/'.G5_SUBSCRIPTION_DIR);
    define('G5_HTTPS_MSUBSCRIPTION_URL', G5_HTTPS_DOMAIN.'/'.G5_MOBILE_DIR.'/'.G5_SUBSCRIPTION_DIR);
} else {
    define('G5_HTTPS_SUBSCRIPTION_URL', G5_SUBSCRIPTION_URL);
    define('G5_HTTPS_MSUBSCRIPTION_URL', G5_MSUBSCRIPTION_URL);
}

if(!defined('_THEME_PREVIEW_')) {
    // 테마 경로 설정
    if(defined('G5_THEME_PATH')) {
        define('G5_THEME_SUBSCRIPTION_PATH',   G5_THEME_PATH.'/'.G5_SUBSCRIPTION_DIR);
        define('G5_THEME_SUBSCRIPTION_URL',    G5_THEME_URL.'/'.G5_SUBSCRIPTION_DIR);
        define('G5_THEME_MSUBSCRIPTION_PATH',  G5_THEME_PATH.'/'.G5_MOBILE_DIR.'/'.G5_SUBSCRIPTION_DIR);
        define('G5_THEME_MSUBSCRIPTION_URL',   G5_THEME_URL.'/'.G5_MOBILE_DIR.'/'.G5_SUBSCRIPTION_DIR);
    }

    // 스킨 경로 설정
    if(preg_match('#^theme/(.+)$#', get_subs_option('de_subscription_skin'), $match)) {
		if(defined('G5_THEME_PATH')) {
			define('G5_SUBSCRIPTION_SKIN_PATH',  G5_THEME_PATH.'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$match[1]);
			define('G5_SUBSCRIPTION_SKIN_URL',   G5_THEME_URL .'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$match[1]);
		} else {
			define('G5_SUBSCRIPTION_SKIN_PATH',  G5_PATH.'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$match[1]);
			define('G5_SUBSCRIPTION_SKIN_URL',   G5_URL .'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$match[1]);
		}
    } else {
        define('G5_SUBSCRIPTION_SKIN_PATH',  G5_PATH.'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.get_subs_option('de_subscription_skin'));
        define('G5_SUBSCRIPTION_SKIN_URL',   G5_URL .'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.get_subs_option('de_subscription_skin'));
    }

    if(preg_match('#^theme/(.+)$#', get_subs_option('de_subscription_mobile_skin'), $match)) {
		if(defined('G5_THEME_PATH')) {
			define('G5_MSUBSCRIPTION_SKIN_PATH', G5_THEME_MOBILE_PATH.'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$match[1]);
			define('G5_MSUBSCRIPTION_SKIN_URL',  G5_THEME_URL .'/'.G5_MOBILE_DIR.'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$match[1]);
		} else {
			define('G5_MSUBSCRIPTION_SKIN_PATH', G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$match[1]);
			define('G5_MSUBSCRIPTION_SKIN_URL',  G5_MOBILE_URL .'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$match[1]);
		}
    } else {
        define('G5_MSUBSCRIPTION_SKIN_PATH', G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.get_subs_option('de_subscription_mobile_skin'));
        define('G5_MSUBSCRIPTION_SKIN_URL',  G5_MOBILE_URL .'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.get_subs_option('de_subscription_mobile_skin'));
    }
}

include_once(G5_LIB_PATH.'/subscription.lib.php');
include_once(G5_LIB_PATH.'/subscription.schedule.php');
include_once(G5_SUBSCRIPTION_PATH.'/subscription.hook.php');
include_once(G5_SUBSCRIPTION_ADMIN_PATH.'/admin.subscription.hook.php');

// KCP 매출전표 url 설정
if (get_subs_option('su_card_test')) {
    // 테스트
    define('G5_SUBSCRIPTION_KCP_BILL_RECEIPT_URL', 'https://testadmin8.kcp.co.kr/assist/bill.BillActionNew.do?cmd=');
} else {
    // 실결제 https://admin8.kcp.co.kr/assist/bill.BillActionNew.do?cmd=card_bill&tno=[NHN KCP거래번호]&order_no=[주문번호]&trade_mony=[거래금액]
    define('G5_SUBSCRIPTION_KCP_BILL_RECEIPT_URL', 'https://admin8.kcp.co.kr/assist/bill.BillActionNew.do?cmd=');
}

if (defined('IS_SUBSCRIPTION_ORDER_FORM') && IS_SUBSCRIPTION_ORDER_FORM) {
    add_event('common_header', 'nocache_nostore_subscription_headers', 1, 0);
}

add_event('admin_notice_messages', 'subscription_check_admin_warning', 1, 0);

function subscription_check_admin_warning() {
    global $is_admin;
    
    if (!$is_admin) {
        return;
    }
    
    if (defined('G5_USE_SUBSCRIPTION') && G5_USE_SUBSCRIPTION && get_subs_option('su_cron_updatetime')) {
        
        $targetTime = strtotime(get_subs_option('su_cron_updatetime'));

        // 시간 차이 계산 (초 단위)
        $diffInSeconds = abs(G5_SERVER_TIME - $targetTime);

        if ($diffInSeconds >= 7200) {   // 2시간 이상 차이가 난다면
            ?>
              <div class="subscription-admin-warning" id="adminWarning">
                중요: 정기결제 CRON 등록이 현시각보다 2시간 이상 차이가 납니다. 정기결제를 사용하려면 반드시 CRON을 점검해 주세요. 마지막 업데이트 : <?php echo get_subs_option('su_cron_updatetime'); ?>
              </div>
            <?php
        }

    }
}