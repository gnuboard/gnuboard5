<?php
if (!defined('_GNUBOARD_')) exit;

// 설정 키 => 표시명, PG 요청 코드, CSS 클래스, 기존 주문 결제수단 값(선택).
function shop_easypay_catalog($pg)
{
    $catalog = array(
        'kcp' => array(
            'nhnkcp_payco' => array('PAYCO', 'payco', 'PAYCO'),
            'nhnkcp_naverpay' => array('네이버페이', 'naverpay', 'naverpay_icon'),
            'nhnkcp_kakaopay' => array('카카오페이', 'kakaopay', 'kakaopay_icon'),
            'nhnkcp_applepay' => array('애플페이', 'applepay', 'applepay_icon'),
        ),
        'inicis' => array(
            'inicis_samsungpay' => array('삼성페이', 'samsungpay', 'samsungpay_icon', '삼성페이'),
            'inicis_lpay' => array('L.pay', 'lpay', 'lpay_icon', 'lpay'),
            'inicis_kakaopay' => array('카카오페이', 'inicis_kakaopay', 'kakaopay_icon', 'inicis_kakaopay'),
        ),
        'toss' => array(
            'toss_tosspay' => array('토스페이', 'TOSSPAY', 'tosspay_icon'),
            'toss_naverpay' => array('네이버페이', 'NAVERPAY', 'naverpay_icon'),
            'toss_samsungpay' => array('삼성페이', 'SAMSUNGPAY', 'samsungpay_icon'),
            'toss_applepay' => array('애플페이', 'APPLEPAY', 'applepay_icon'),
            'toss_lpay' => array('L.pay', 'LPAY', 'lpay_icon'),
            'toss_kakaopay' => array('카카오페이', 'KAKAOPAY', 'kakaopay_icon'),
            'toss_pinpay' => array('핀페이', 'PINPAY', 'pinpay_icon'),
            'toss_payco' => array('PAYCO', 'PAYCO', 'PAYCO'),
            'toss_ssgpay' => array('SSG페이', 'SSG', 'ssgpay_icon'),
        ),
        'nicepay' => array(
            'nicepay_samsungpay' => array('삼성페이', 'nice_samsungpay', 'samsungpay_icon'),
            'nicepay_naverpay' => array('네이버페이', 'nice_naverpay', 'naverpay_icon'),
            'nicepay_kakaopay' => array('카카오페이', 'nice_kakaopay', 'kakaopay_icon'),
            'nicepay_applepay' => array('애플페이', 'nice_applepay', 'applepay_icon'),
            'nicepay_paycopay' => array('PAYCO', 'nice_paycopay', 'PAYCO'),
            'nicepay_skpay' => array('SK페이', 'nice_skpay', 'skpay_icon'),
            'nicepay_ssgpay' => array('SSG페이', 'nice_ssgpay', 'ssgpay_icon'),
            'nicepay_lpay' => array('L.pay', 'nice_lpay', 'lpay_icon'),
        ),
    );
    return isset($catalog[$pg]) ? $catalog[$pg] : array();
}

function shop_easypay_legacy_keys()
{
    return array('inicis_samsungpay' => 'de_samsung_pay_use', 'inicis_lpay' => 'de_inicis_lpay_use', 'inicis_kakaopay' => 'de_inicis_kakaopay_use');
}

// 첫 설정 저장 전에는 기존 개별 설정과 토스 PAYCO 사용 상태를 승계한다.
function shop_easypay_normalize($settings)
{
    $services = !empty($settings['de_easy_pay_services']) ? explode(',', $settings['de_easy_pay_services']) : array();
    if (!in_array('inicis_configured', $services, true)) {
        foreach (shop_easypay_legacy_keys() as $key => $legacy) {
            if (!empty($settings[$legacy])) {
                $services[] = $key;
                if ($settings['de_pg_service'] === 'inicis') $settings['de_easy_pay_use'] = 1;
            }
        }
    }
    if (!in_array('toss_configured', $services, true) && $settings['de_pg_service'] === 'toss' && !empty($settings['de_easy_pay_use']) && !array_intersect(array_keys(shop_easypay_catalog('toss')), $services)) {
        $services[] = 'toss_payco';
    }
    $settings['de_easy_pay_services'] = implode(',', array_unique($services));
    foreach (shop_easypay_legacy_keys() as $key => $legacy) {
        $settings[$legacy] = (int) (in_array($key, $services, true) && ($settings['de_pg_service'] !== 'inicis' || !empty($settings['de_easy_pay_use'])));
    }
    return $settings;
}

function shop_easypay_enabled($key)
{
    global $default;
    return !empty($default['de_easy_pay_use']) && in_array($key, explode(',', $default['de_easy_pay_services']), true);
}

function shop_easypay_available($key, $mobile)
{
    global $default;
    if (strpos($key, 'applepay') !== false) {
        // KCP/NICEPAY와 동일하게 iOS 모바일 브라우저에만 제공한다.
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        return $mobile && preg_match('/iPhone|iPad|iPod/i', $ua);
    }
    // 삼성페이는 PC에서 휴대폰 인증으로 연결할 수 있다. 구 INIpay만 모바일 전용이다.
    return $key !== 'inicis_samsungpay' || $mobile || !empty($default['de_inicis_pro_use']);
}

function shop_easypay_button($key, $provider, $mobile, $money = false)
{
    $label = $provider[0];
    $pay_code = $provider[1];
    $css_class = $provider[2];
    $value = isset($provider[3]) ? $provider[3] : '간편결제';
    $legacy_ids = array('inicis_samsungpay' => 'samsungpay', 'inicis_lpay' => 'inicislpay');
    $id = 'od_settle_'.(isset($legacy_ids[$key]) ? $legacy_ids[$key] : $key);
    $attrs = isset($provider[3]) ? ' data-case="'.$pay_code.'"' : '';
    if ($money) {
        $attrs .= ' data-money="1"';
    }

    $html = '<input type="radio" id="'.$id.'" name="od_settle_case"'
        .' data-pay="'.$pay_code.'" value="'.$value.'"'.$attrs.'> '
        .'<label for="'.$id.'" class="'.$css_class.' '.$key.' lb_icon"'
        .' title="'.$label.'">'.$label.'</label>';

    return $mobile ? '<li>'.$html.'</li>' : $html;
}

function shop_easypay_buttons($mobile = false)
{
    global $default;
    $buttons = array();
    $pg = $default['de_pg_service'];
    foreach (shop_easypay_catalog($pg) as $key => $provider) {
        if (!shop_easypay_enabled($key) || !shop_easypay_available($key, $mobile)) continue;
        $buttons[$key] = shop_easypay_button($key, $provider, $mobile);
    }
    if ($pg === 'lg' && !empty($default['de_easy_pay_use'])) {
        $buttons['paynow'] = shop_easypay_button('easy_pay', array('PAYNOW', '', 'PAYNOW'), $mobile);
    }
    if (is_use_easypay('global_nhnkcp')) {
        $buttons['nhnkcp_naverpay'] = shop_easypay_button('nhnkcp_naverpay', array('네이버페이', 'naverpay', 'naverpay_icon'), $mobile);
    }
    if (isset($buttons['nhnkcp_naverpay'])) {
        $services = explode(',', $default['de_easy_pay_services']);
        if (in_array('used_nhnkcp_naverpay_point', $services, true)) {
            $buttons['nhnkcp_naverpay'] = shop_easypay_button('nhnkcp_naverpay', array('네이버페이 카드결제', 'naverpay', 'naverpay_icon nhnkcp_icon nhnkcp_card'), $mobile);
            $buttons['nhnkcp_naverpay_money'] = shop_easypay_button('nhnkcp_naverpay_money', array('네이버페이 머니/포인트', 'naverpay', 'naverpay_icon nhnkcp_icon nhnkcp_money'), $mobile, true);
        }
    }
    return $buttons;
}
