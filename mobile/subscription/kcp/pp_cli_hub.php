<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

//print_r2($_POST);
//exit;

/* ============================================================================== */
/* =   PAGE : 등록/변경 처리 PAGE                                               = */
/* = -------------------------------------------------------------------------- = */
/* =   Copyright (c)  2013   KCP Inc.   All Rights Reserverd.                  = */
/* ============================================================================== */

/* ============================================================================== */
/* = 라이브러리 및 사이트 정보 include                                          = */
/* = -------------------------------------------------------------------------- = */
include_once(G5_MSUBSCRIPTION_PATH.'/settle_kcp.inc.php');
require_once(G5_MSUBSCRIPTION_PATH.'/kcp/pp_cli_hub_lib.php');

    /* ============================================================================== */
    /* =   02. 인증 요청 정보 설정                                                  = */
    /* = -------------------------------------------------------------------------- = */
    $req_tx      = isset($_POST['req_tx']) ? $_POST['req_tx'] : ''; // 요청 종류
    $tran_cd     = isset($_POST['tran_cd']) ? preg_replace('/[^0-9A-Za-z_\-\.]/i', '', $_POST['tran_cd']) : ''; // 처리 종류
    $cust_ip     = $_SERVER['REMOTE_ADDR'];                  // 요청 IP (옵션값)
    /* = -------------------------------------------------------------------------- = */
    $pay_method  = isset($_POST['pay_method']) ? clean_xss_tags($_POST['pay_method']) : ''; // 결제 방법
    $ordr_idxx   = isset($_POST['ordr_idxx']) ? preg_replace('/[^0-9A-Za-z_\-\.]/i', '', $_POST['ordr_idxx']) : ''; // 쇼핑몰 주문번호
    $buyr_name   = isset($_POST['buyr_name']) ? addslashes($_POST['buyr_name']) : ''; // 요청자이름
    
    $good_name      = isset($_POST['pay_method']) ? clean_xss_tags($_POST[ "good_name"      ]) : ''; // 상품명
    $good_mny       = isset($_POST['pay_method']) ? $_POST[ "good_mny"       ] : 0; // 결제 총금액
    
    /* = -------------------------------------------------------------------------- = */
    $res_cd      = "";                                       // 결과 코드
    $res_msg     = "";                                       // 결과 메시지
    /* = -------------------------------------------------------------------------- = */
    $card_cd     = "";                                       // 카드 코드
    $card_name      = "";                                                     // 신용카드 명
    $batch_key   = "";                                       // 배치 인증키
    /* ============================================================================== */
    $param_opt_1    = isset($_POST[ "param_opt_1" ]) ? clean_xss_tags($_POST['param_opt_1']) : '';
    $param_opt_2    = isset($_POST[ "param_opt_2" ]) ? clean_xss_tags($_POST['param_opt_2']) : '';
    $param_opt_3    = isset($_POST[ "param_opt_3" ]) ? clean_xss_tags($_POST['param_opt_3']) : '';

    /* ============================================================================== */
    /* =   03. 인스턴스 생성 및 초기화                                              = */
    /* = -------------------------------------------------------------------------- = */
    $c_PayPlus = new C_PP_CLI;
    
    $c_PayPlus->mf_clear();
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   04. 처리 요청 정보 설정, 실행                                            = */
    /* = -------------------------------------------------------------------------- = */

    /* = -------------------------------------------------------------------------- = */
    /* =   04-1. 인증 요청                                                          = */
    /* = -------------------------------------------------------------------------- = */
    $post_enc_data = isset($_POST['enc_data']) ? $_POST['enc_data'] : '';
    $post_enc_info = isset($_POST['enc_info']) ? $_POST['enc_info'] : '';
    $c_PayPlus->mf_set_encx_data( $post_enc_data , $post_enc_info );

    /* = -------------------------------------------------------------------------- = */
    /* =   04-2. 실행                                                               = */
    /* = -------------------------------------------------------------------------- = */
    if ( $tran_cd != "" )
    {
        $c_PayPlus->mf_do_tx( $trace_no, $g_conf_home_dir, $g_conf_site_cd, $g_conf_site_key, $tran_cd, "",
                              $g_conf_gw_url, $g_conf_gw_port, "payplus_cli_slib", $ordr_idxx,
                              $cust_ip, $g_conf_log_level, 0, 0, $g_conf_log_path ); // 응답 전문 처리
    }
    else
    {
        $c_PayPlus->m_res_cd  = "9562";
        $c_PayPlus->m_res_msg = "연동 오류|tran_cd값이 설정되지 않았습니다.";
    }

    $res_cd    = $c_PayPlus->m_res_cd;
    $res_msg   = $c_PayPlus->m_res_msg;
    /* ============================================================================== */


if ($res_cd != '0000')
{
    $res_msg = iconv("euc-kr", "utf-8", $res_msg);

    /*
    echo "<script>
    var openwin = window.open( './kcp/proc_win.php', 'proc_win', '' );
    openwin.close();
    </script>";
    */
    alert("$res_cd : $res_msg");
    exit;
}

    /* ============================================================================== */
    /* =   05. 인증 결과 처리                                                       = */
    /* = -------------------------------------------------------------------------- = */
    if( $res_cd == "0000" )
    {
        $card_cd   = $c_PayPlus->mf_get_res_data( "card_cd"   );    // 카드사 코드
        $card_name = $c_PayPlus->mf_get_res_data( "card_name" );       // 카드명
        $batch_key = $c_PayPlus->mf_get_res_data( "batch_key" );
        
        // 승인결과
        // 일반 폼에서 batch_cardno_return_yn 를 Y, L 값을 주는것에 따라 다름
        $card_mask_number = isset($_POST['card_mask_no']) ? clean_xss_tags($_POST['card_mask_no']) : '';
        $card_billkey = $batch_key;
        
        // 카드사 이름이 존재하면 카드사 이름을 저장하고 그렇지 않으면 카드사 코드를 저장
        $card_name = $card_cd && isset($kcp_card_codes[$card_cd]) ? $kcp_card_codes[$card_cd] : $card_cd;
        // NHN_KCP는 batch키를 발급받는 것에 tno값을 보내지 않는다.
        $tno = '';
        $amount = $order_price;
        $app_no = '';
        $app_time = '';
    }
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   06. 폼 구성 및 결과페이지 호출                                           = */
    /* ============================================================================== */

if (! $card_billkey) {
    alert('오류 : KCP 배치키를 받지 못했습니다.');
    exit;
}