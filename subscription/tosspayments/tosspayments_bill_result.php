<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

@header('Progma:no-cache');
@header('Cache-Control:no-cache,must-revalidate');

include_once(G5_SUBSCRIPTION_PATH.'/settle_tosspayments.inc.php');

$apiSecretKey = get_subs_option('su_tosspayments_api_secretkey');

$encryptedApiSecretKey = "Basic " . base64_encode($apiSecretKey . ":");

$billingKeyMap = array();

$postData = json_encode(array(
    'customerKey' => tosspayments_customerkey_uuidv4($member['mb_id']),
    'authKey' => $authKey
));

$response = subscription_sendRequest("https://api.tosspayments.com/v1/billing/authorizations/issue", $encryptedApiSecretKey, $postData);
$result = json_decode($response, true);

if (isset($result['billingKey']) && $result['billingKey']) { // 성공이면
    $billingKeyMap[$orderNumber] = $result['billingKey'];
    
    // 토스페이먼츠 Version 2 에는 tno가 없다. 그래서 임의적으로 생성한다.
    $tno = $result['mId'].'_'.preg_replace('/[^0-9]/', '', $result['authenticatedAt']);
    
    // 카드 코드
    $card_code = isset($result['card']['issuerCode']) ? $result['card']['issuerCode'] : '';

    /*
    "card": {
        "issuerCode": "71",
        "acquirerCode": "71",
        "number": "00001511****713*",
        "cardType": "신용",
        "ownerType": "개인"
    }
    */
    // 마스킹 된 카드번호 : 숫자8자리 마스킹* 4자리 숫자 3자리 마스킹* 1자리 이렇게 마스킹 되어 넘겨 받는다.
    $amount = $order_price;
    $pg_price = $order_price;
    $card_mask_number = $result['cardNumber'];
    $card_billkey = $result['billingKey'];
    
    // 카드이름
    $card_name = $result['cardCompany'];

} else {
    // 실패시
    
    echo '<br>구독 에러가 일어났습니다. 에러 이유는 아래와 같습니다.';
    
    if (isset($result['code'])) {
        echo '<br>code : ' . $result['code'];
    }
    
    if (isset($result['message'])) {
        echo '<br>message : ' . $result['message'];
    }
    
    print_r( $response );
    
    die('');
}
?>