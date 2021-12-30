<?php
// if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if ($default['de_card_test']) {

    $default['de_nicepay_mid'] = 'nicepay00m';
    // $default['de_nicepay_admin_key'] = '1111';
    $default['de_nicepay_sign_key'] = 'EYzu8jGGMfqaDEp76gSckuvnaHHu+bC4opsSN6lHv3b2lurNYkVXrZ7Z1AoqQnXI3eLuaUFyoRNC6FkrzVjceg==';
}
// else {
//     if( !defined('G5_MOBILE_nicepay_SETTLE') ){
//         $default['de_nicepay_mid'] = "SIR".$default['de_nicepay_mid'];
//     }

//     if ($default['de_escrow_use'] == 1) {
//         // 에스크로결제
//         $useescrow = ':useescrow';
//     }
//     else {
//         // 일반결제
//         $useescrow = '';
//     }
// }



?>