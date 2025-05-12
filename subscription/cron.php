<?php
include_once './_common.php';

// 상품명과 건수를 반환
function get_subscription_goods($cart_id)
{
    global $g5;

    // 상품명만들기
    $row = sql_fetch(" select a.it_id, b.it_name from {$g5['g5_subscription_cart_table']} a, {$g5['g5_shop_item_table']} b where a.it_id = b.it_id and a.od_id = '$cart_id' order by ct_id limit 1 ");
    // 상품명에 "(쌍따옴표)가 들어가면 오류 발생함
    $goods['it_id'] = $row['it_id'];
    $goods['full_name'] = $goods['name'] = addslashes($row['it_name']);
    // 특수문자제거
    $goods['full_name'] = preg_replace("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", '', $goods['full_name']);

    // 상품건수
    $row = sql_fetch(" select count(*) as cnt from {$g5['g5_subscription_cart_table']} where od_id = '$cart_id' ");
    $cnt = $row['cnt'] - 1;
    if ($cnt) {
        $goods['full_name'] .= ' 외 '.$cnt.'건';
    }
    $goods['count'] = $row['cnt'];

    return $goods;
}

function subscription_inicis_billing($subscription_order_id)
{
    global $g5, $config, $default;

    $subscription_item = get_subscription_order($subscription_order_id);

    if (!$subscription_item) {
        return;
    }

    // step1. 요청을 위한 파라미터 설정
    $key = 'rKnPljRn5m6J9Mzz';
    $iv = 'W2KLNKra6Wxc1P==';
    $mid = 'INIBillTst';
    $type = 'billing';
    $paymethod = 'Card';
    $timestamp = date('YmdHis', G5_SERVER_TIME);
    $clientIp = $_SERVER['REMOTE_ADDR'];

    $postdata = [];
    $postdata['mid'] = $mid;
    // 요청서비스 ["billing" 고정]
    $postdata['type'] = $type;
    // 지불수단 코드 [card:신용카드, HPP:휴대폰]
    $postdata['paymethod'] = $paymethod;
    // 전문생성시간 [YYYYMMDDhhmmss]
    $postdata['timestamp'] = $timestamp;
    // 가맹점 요청 서버IP (추후 거래 확인 등에 사용됨)
    $postdata['clientIp'] = $clientIp;

    $goods = get_subscription_goods($subscription_order_id);

    // https://manual.inicis.com/pay/bill_pc.html 참고
    // // Data 상세
    $detail = [];
    // 가맹점 URL
    $detail['url'] = $_SERVER['SERVER_NAME'];       // or $_SERVER['REQUEST_URI']
    // 주문번호
    $detail['moid'] = $subscription_item['od_id'];
    // 상품명
    $detail['goodName'] = $goods['full_name'];
    // 구매자명
    $detail['buyerName'] = $subscription_item['od_name'];
    $detail['buyerEmail'] = $subscription_item['od_email'];
    $detail['buyerTel'] = $subscription_item['od_hp'];
    // 결제금액
    $detail['price'] = $subscription_item['od_receipt_price'];
    // 승인요청할 빌링키 값
    $detail['billKey'] = $subscription_item['card_billkey'];
    // 본인인증 여부 ["00" 고정], 본인인증 안함 가맹점으로 별도계약된 경우 "99" 로 세팅
    $detail['authentification'] = '00';
    // 할부기간 ["00":일시불, 그 외 : 02, 03 ...]
    $detail['cardQuota'] = '00';
    // 무이자구분 ["1":무이자], "상점부담무이자" 계약 가맹점만 사용
    $detail['quotaInterest'] = '0';

    // 부가세 ("부가세 업체정함" 계약가맹점만 설정필요)
    // $detail['tax'] =
    // 비과세 ("부가세 업체정함" 계약가맹점만 설정필요)
    // $detail['taxFree'] =

    $postdata['data'] = $detail;

    $details = str_replace('\\/', '/', json_encode($detail, JSON_UNESCAPED_UNICODE));

    // // Hash Encryption
    $plainTxt = $key.$mid.$type.$timestamp.$details;
    $hashData = hash('sha512', $plainTxt);

    $postdata['hashData'] = $hashData;

    echo 'plainTxt : '.$plainTxt.'<br/><br/>';
    echo 'hashData : '.$hashData.'<br/><br/>';

    $post_data = json_encode($postdata, JSON_UNESCAPED_UNICODE);

    echo '**** 요청전문 **** <br/>';
    echo str_replace(',', ',<br>', $post_data).'<br/><br/>';

    // step2. 요청전문 POST 전송

    $url = 'https://iniapi.inicis.com/v2/pg/billing';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json;charset=utf-8']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    curl_close($ch);

    // step3. 결과출력

    echo '**** 응답전문 **** <br/>';
    echo str_replace(',', ',<br>', $response).'<br><br>';
}

// $subscription_order_id = '2024080210240864';

// subscription_inicis_billing($subscription_order_id);
