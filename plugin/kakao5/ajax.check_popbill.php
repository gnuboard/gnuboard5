<?php
include_once('../../common.php');
include_once('./_common.php');
include_once(G5_KAKAO5_PATH.'/kakao5.lib.php');

// 팝빌 정보 확인
$check_result = get_popbill_service_info();

if (isset($check_result['error'])) {
    die(json_encode(array('error' => $check_result['error'])));
} else {
    $charge_url = get_popbill_point_URL(); // 포인트 충전 팝업 URL
    die(json_encode(array('balance' => $check_result['balance'], 'charge_url' => $charge_url)));
}