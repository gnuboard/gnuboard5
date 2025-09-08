<?php
/* 팝빌 URL 출력 */
include_once('../../common.php');
include_once('./_common.php');
include_once(G5_KAKAO5_PATH.'/kakao5.lib.php'); // 팝빌 카카오톡 솔루션 라이브러리

$url = null;
$width = '1200';
$height = '800';
$get_url = isset($_POST['get_url']) ? $_POST['get_url'] : null;

if ($config['cf_kakaotalk_use'] == 'popbill') {
    switch ($get_url) {
        case '1': // 템플릿 목록 URL
            $url = get_popbill_template_manage_URL();
            break;
        case '2': // 전송내역 URL
            $url = get_popbill_send_manage_URL();
            $width = '1350';
            break;
        case '3': // 플러스친구 관리 URL
            $url = get_popbill_plusfriend_manage_URL();
            break;
        case '4': // 발신번호 등록 URL
            $url = get_popbill_sender_number_URL();
            break;
        case '5': // 포인트 충전 URL
            $url = get_popbill_point_URL();
            $width = '800';
            $height = '700';
            break;
        default:
            $url = null;
            break;
    }
}

die(json_encode(array('url' => $url, 'width' => $width, 'height' => $height)));