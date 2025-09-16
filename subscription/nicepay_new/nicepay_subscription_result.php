<?php

if (!defined('_GNUBOARD_')) {
    exit;
} // 개별 페이지 접근 불가

// 나이스페이 공통 설정
require_once G5_SUBSCRIPTION_PATH.'/settle_nicepay.inc.php';

function billing($bid)
{
    global $nicepay_clientid;
    global $nicepay_secretkey;

    try {
        $res = requestPost(
            'https://sandbox-api.nicepay.co.kr/v1/subscribe/'.$bid.'/payments',
            json_encode(
                ['orderId' => uniqid(),
                    'amount' => 1004,
                    'goodsName' => 'test',
                    'cardQuota' => 0,
                    'useShopInterest' => false]
            ),
            $nicepay_clientid.':'.$nicepay_secretkey
        );

        return $res;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}
function encrypt($text, $key, $iv)
{
    $encrypted = openssl_encrypt($text, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

    return bin2hex($encrypted);
}

// CURL: Basic auth, json, post
function requestPost($url, $json, $userpwd)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);

    curl_close($ch);

    return $response;
}

$key = substr($nicepay_secretkey, 0, 32);
$iv = substr($nicepay_secretkey, 0, 16);
$resObject = '';

$cardNo = isset($_POST['cardNo']) ? clean_xss_tags($_POST['cardNo']) : '';
$expYear = isset($_POST['expYear']) ? clean_xss_tags($_POST['expYear']) : '';
$expMonth = isset($_POST['expMonth']) ? clean_xss_tags($_POST['expMonth']) : '';
$idNo = isset($_POST['idNo']) ? clean_xss_tags($_POST['idNo']) : '';
$cardPw = isset($_POST['cardPw']) ? clean_xss_tags($_POST['cardPw']) : '';

$plainText = 'cardNo='.$cardNo.
            '&expYear='.$expYear.
            '&expMonth='.$expMonth.
            '&idNo='.$idNo.
            '&cardPw='.$cardPw;

try {
    $res = requestPost(
        get_nicepay_api_url().'/v1/subscribe/regist',
        json_encode(
            ['encData' => encrypt($plainText, $key, $iv),
                'orderId' => get_session('subs_order_id'),
                'encMode' => 'A2']
        ),
        $nicepay_clientid.':'.$nicepay_secretkey
    );

    $resObject = json_decode($res, true);
    // $bid = $resObject->{'bid'};

    // billing($bid); // 빌키 승인
    // expire($bid); // 빌키 삭제
} catch (Exception $e) {
    alert($e->getMessage(), G5_SHOP_URL);
}

// 0000이 아니면 실패
if ($resObject['resultCode'] !== '0000') {
    alert($resObject['resultMsg'], G5_SHOP_URL);
}

$od_tno = $resObject['tid'];

// 성공시 아래의 형식으로 응답 받음
// resultCode=0000
// resultMsg=정상 처리되었습니다.
// tid=UT0000115m0*******************
// orderId=66b**************
// bid=BIKY***********************
// authDate=202*-08-1*T00:00:00.000+0900
// cardCode=0*
// cardName=삼*
// messageSource=nicepay
// status=issued

$card_mask_number = mask_card_number($cardNo);
$card_billkey = $resObject['bid'];
$tno = $resObject['tid'];
$amount = $_POST['good_mny'] ? (int) $_POST['good_mny'] : 0;