<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once("$g4[path]/shop.config.php");
include_once("$g4[path]/lib/shop.lib.php");
include_once("$g4[path]/lib/fs.lib.php");

$g4['yc4_uniqid_table'] = "yc4_uniqid";

//==============================================================================
// 쇼핑몰 필수 실행코드 모음 시작
//==============================================================================
// 쇼핑몰 설정값 배열변수
$default = sql_fetch(" select * from {$g4['yc4_default_table']} ");

// 비회원장바구니 사용할 때
if($default['de_guest_cart_use']) {
    $tmp_uq_id = get_cookie('ck_guest_cart_uqid');

    if($tmp_uq_id) {
        set_session('ss_uniqid', $tmp_uq_id);
    }

    unset($tmp_uq_id);
}
//==============================================================================
// 쇼핑몰 필수 실행코드 모음 끝
//==============================================================================

// 프로그램 전반에 걸쳐 사용하는 유일한 키 (주문번호 키)
if (!get_session('ss_uniqid')) {
    set_session('ss_uniqid', get_uniqid());
}
?>