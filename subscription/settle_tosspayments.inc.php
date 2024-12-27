<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 테스트이면
if (get_subs_option('su_card_test')) {
    // 토스 아이디
    // set_subs_option('su_kcp_mid', 'A52Q7');
    
    set_subs_option('su_tosspayments_api_secretkey', 'test_sk_zXLkKEypNArWmo50nX3lmeaxYG5R');
} else {
    // 실 사용이면
    set_subs_option('su_tosspayments_mid', 'si_'.get_subs_option('su_tosspayment_mid'));
}
