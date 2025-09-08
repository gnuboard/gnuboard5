<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
if (!defined("_ORDERALIMTALK_")) exit;
include_once(G5_KAKAO5_PATH.'/kakao5.lib.php');

$it_name_str = get_alimtalk_cart_item_name($od_id); // 상품명

// 입금 알림
if($od_alimtalk_ipgum_check){
    // 알림톡 발송 BEGIN: 입금완료(CU-OR03 / AD-OR03) ------------------------------
    $conditions = ['od_id' => $od_id, 'od_name' => $od_name, 'it_name' => $it_name_str]; // 변수 치환 정보
    $cu_atk = send_alimtalk_preset('CU-OR03', ['rcv' => $od_hp ?: $od['od_tel'], 'rcvnm' => $od_name], $conditions); // 회원
    // 알림톡 발송 END --------------------------------------------------------
}

// 배송 알림
if($od_alimtalk_baesong_check){
    // 알림톡 발송 BEGIN: 배송중(CU-DE02) ------------------------------
    $conditions = ['od_id' => $od_id, 'od_name' => $od_name, 'it_name' => $it_name_str]; // 변수 치환 정보
    $cu_atk = send_alimtalk_preset('CU-DE02', ['rcv' => $od_hp ?: $od['od_tel'], 'rcvnm' => $od_name], $conditions); // 회원
    // 알림톡 발송 END --------------------------------------------------------
}