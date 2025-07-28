<?php
include_once './_common.php';

include_once G5_SUBSCRIPTION_PATH.'/settle_tosspayments.inc.php';

echo $_SERVER['REQUEST_URI'];

//$apiSecretKey = "test_sk_zXLkKEypNArWmo50nX3lmeaxYG5R";

$apiSecretKey = get_subs_option('su_tosspayment_api_secretkey');

$encryptedWidgetSecretKey = "Basic " . base64_encode($widgetSecretKey . ":");
$encryptedApiSecretKey = "Basic " . base64_encode($apiSecretKey . ":");

$billingKeyMap = array();

// Set JSON content type header for all responses
header("Content-Type: application/json");

// Main router logic
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

switch ($requestUri) {
    
    case '/callback-auth':
        if ($requestMethod === 'GET') {
            getAuthToken($encryptedApiSecretKey);
        }
        break;

    case '/issue-billing-key':
        if ($requestMethod === 'POST') {
            issueBillingKey($encryptedApiSecretKey);
        }
        break;
    
    case '/confirm-billing':
        if ($requestMethod === 'POST') {
            confirmBilling($encryptedApiSecretKey, $billingKeyMap);
        }
        break;

    default:
        echo json_encode(["error" => "Route not found"]);
        http_response_code(404);
        break;
}

function confirmPayment($authKey) {
    $data = json_decode(file_get_contents("php://input"), true);    
    $postData = json_encode(array(
        'orderId' => $data['orderId'],
        'amount' => $data['amount'],
        'paymentKey' => $data['paymentKey']
    ));

    $response = sendRequest("https://api.tosspayments.com/v1/payments/confirm", $authKey, $postData);
    echo $response;
}

function confirmBrandPay($authKey) {
    $data = json_decode(file_get_contents("php://input"), true);

    $postData = json_encode(array(
        'orderId' => $data['orderId'],
        'amount' => $data['amount'],
        'paymentKey' => $data['paymentKey'],
        'customerKey' => $data['customerKey']
    ));

    $response = sendRequest("https://api.tosspayments.com/v1/brandpay/payments/confirm", $authKey, $postData);
    echo $response;
}

function getAuthToken($authKey) {
    $customerKey = $_GET['customerKey'];
    $code = $_GET['code'];

    $postData = json_encode([
        'grantType' => 'AuthorizationCode',
        'customerKey' => $customerKey,
        'code' => $code
    ]);

    $response = sendRequest("https://api.tosspayments.com/v1/brandpay/authorizations/access-token", $authKey, $postData);
    echo $response;
}

function issueBillingKey($authKey) {
    global $billingKeyMap;
    $data = json_decode(file_get_contents("php://input"), true);

    $postData = json_encode([
        'customerKey' => $data['customerKey'],
        'authKey' => $data['authKey']
    ]);

    $response = sendRequest("https://api.tosspayments.com/v1/billing/authorizations/issue", $authKey, $postData);
    $result = json_decode($response, true);

    if (isset($result['billingKey'])) {
        $billingKeyMap[$data['customerKey']] = $result['billingKey'];
    }

    echo $response;
}

function confirmBilling($authKey, $billingKeyMap) {
    $data = json_decode(file_get_contents("php://input"), true);

    $billingKey = $billingKeyMap[$data['customerKey']] ?? null;

    if ($billingKey) {
        $postData = json_encode(array(
            'customerKey' => $data['customerKey'],
            'amount' => $data['amount'],
            'orderId' => $data['orderId'],
            'orderName' => $data['orderName'],
            'customerEmail' => $data['customerEmail'],
            'customerName' => $data['customerName']
        ));

        $response = sendRequest("https://api.tosspayments.com/v1/billing/$billingKey", $authKey, $postData);
        echo $response;
    } else {
        echo json_encode(["error" => "Billing key not found for customerKey"]);
        http_response_code(404);
    }
}

function sendRequest($url, $authKey, $postData) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: $authKey",
        "Content-Type: application/json"
    ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}