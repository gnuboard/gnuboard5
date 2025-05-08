<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

    /* ============================================================================== */
    /* =   PAGE : 지불 요청 PAGE                                                    = */
    /* = -------------------------------------------------------------------------- = */
    /* =   Copyright (c)  2013   KCP Inc.   All Rights Reserved.                    = */
    /* ============================================================================== */

    /* ============================================================================== */
    /* = 라이브러리 및 사이트 정보 include                                          = */
    /* = -------------------------------------------------------------------------- = */

// locale ko_KR.euc-kr 로 설정
setlocale(LC_CTYPE, 'ko_KR.euc-kr');

include_once(G5_SUBSCRIPTION_PATH.'/settle_kcp.inc.php');
require_once(G5_SUBSCRIPTION_PATH.'/kcp/pay_pp_cli_hub_lib.php');
    /* ============================================================================== */

    /* ============================================================================== */
    /* =   01. 지불 요청 정보 설정                                                  = */
    /* = -------------------------------------------------------------------------- = */
    $pay_method = isset($posts['pay_method']) ? clean_xss_tags($posts['pay_method']) : '';  // 결제 방법
    $ordr_idxx  = isset($posts["ordr_idxx"]) ? preg_replace('/[^0-9A-Za-z_\-\.]/i', '', $posts["ordr_idxx"]) : '';  // 주문 번호
    $good_name  = isset($posts["good_name"]) ? iconv("utf-8", "cp949", clean_xss_tags($posts["good_name"])) : '';  // 상품 정보
    $good_mny   = isset($posts['good_mny']) ? (int) $posts['good_mny'] : 0;  // 결제 금액
    $buyr_name  = isset($posts['buyr_name']) ? iconv("utf-8", "cp949", clean_xss_tags($posts['buyr_name'])) : '';  // 주문자 이름
    $buyr_mail  = isset($posts['buyr_mail']) ? clean_xss_tags($posts['buyr_mail']) : '';  // 주문자 E-Mail
    $buyr_tel1  = isset($posts['buyr_tel1']) ? clean_xss_tags($posts['buyr_tel1']) : '';  // 주문자 전화번호
    $buyr_tel2  = isset($posts['buyr_tel2']) ? clean_xss_tags($posts['buyr_tel2']) : '';  // 주문자 휴대폰번호
    $req_tx     = $posts['req_tx'];  // 요청 종류
    $currency   = $posts['currency'];  // 화폐단위 (WON/USD)
    $mod_type      = $posts['mod_type'];                         // 변경TYPE(승인취소시 필요)
    $mod_desc      = $posts['mod_desc'];                         // 변경사유
    $card_pay_method = $posts['card_pay_method'];                    // 카드 결제 방법
    $amount        = "";                                               // 총 금액
    
    $panc_mod_mny  = "";                                               // 부분취소 요청금액
    $panc_rem_mny  = "";                                               // 부분취소 가능금액
    /* = -------------------------------------------------------------------------- = */
    $tran_cd       = "";                                               // 트랜잭션 코드
    $bSucc         = "";                                               // DB 작업 성공 여부
    /* = -------------------------------------------------------------------------- = */
    $res_cd        = "";                                               // 결과코드
    $res_msg       = "";                                               // 결과메시지
    $tno           = "";                                               // 거래번호
    /* = -------------------------------------------------------------------------- = */
    $card_cd         = "";                                             // 카드 코드
    $card_no         = "";                                             // 카드 번호
    $card_name       = "";                                             // 카드명
    $app_time        = "";                                             // 승인시간
    $app_no          = "";                                             // 승인번호
    $noinf           = "";                                             // 무이자여부
    $quota           = "";                                             // 할부개월
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   02. 인스턴스 생성 및 초기화                                              = */
    /* = -------------------------------------------------------------------------- = */

    $c_PayPlus  = new C_PAYPLUS_CLI;
    $c_PayPlus->mf_clear();
    
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   03. 처리 요청 정보 설정, 실행                                            = */
    /* = -------------------------------------------------------------------------- = */

    /* = -------------------------------------------------------------------------- = */
    /* =   03-1. 승인 요청                                                          = */
    /* = -------------------------------------------------------------------------- = */
    // 업체 환경 정보
    $cust_ip = $_SERVER['REMOTE_ADDR']; // 요청 IP (옵션값)

    if ( $req_tx == "pay" )
    {
    $tran_cd = "00100000";

    $common_data_set = "";

    $common_data_set .= $c_PayPlus->mf_set_data_us( "amount",   $good_mny    );
    $common_data_set .= $c_PayPlus->mf_set_data_us( "currency", $currency    );
    $common_data_set .= $c_PayPlus->mf_set_data_us( "cust_ip",  $cust_ip );
    $common_data_set .= $c_PayPlus->mf_set_data_us( "escw_mod", "N"      );

    $c_PayPlus->mf_add_payx_data( "common", $common_data_set );

    // 주문 정보
    $c_PayPlus->mf_set_ordr_data( "ordr_idxx", $ordr_idxx );
    $c_PayPlus->mf_set_ordr_data( "good_name", $good_name );
    $c_PayPlus->mf_set_ordr_data( "good_mny",  $good_mny  );
    $c_PayPlus->mf_set_ordr_data( "buyr_name", $buyr_name );
    $c_PayPlus->mf_set_ordr_data( "buyr_tel1", $buyr_tel1 );
    $c_PayPlus->mf_set_ordr_data( "buyr_tel2", $buyr_tel2 );
    $c_PayPlus->mf_set_ordr_data( "buyr_mail", $buyr_mail );

        if ( $pay_method == "CARD" )
        {
            $card_data_set;

            $card_data_set .= $c_PayPlus->mf_set_data_us( "card_mny", $good_mny );        // 결제 금액

                if ( $card_pay_method == "Batch" )
                {
                    $card_data_set .= $c_PayPlus->mf_set_data_us( "card_tx_type",   "11511000" );
                    $card_data_set .= $c_PayPlus->mf_set_data_us( "quota",          $posts[ "quotaopt"     ] );
                    $card_data_set .= $c_PayPlus->mf_set_data_us( "bt_group_id",    $posts[ "bt_group_id"  ] );
                    $card_data_set .= $c_PayPlus->mf_set_data_us( "bt_batch_key",   $posts[ "bt_batch_key" ] );
                }
            $c_PayPlus->mf_add_payx_data( "card", $card_data_set );
        }
    }

    /* = -------------------------------------------------------------------------- = */
    /* =   03-2. 취소/매입 요청                                                     = */
    /* = -------------------------------------------------------------------------- = */
        else if ( $req_tx == "mod" )
        {

            $tran_cd = "00200000";

            $c_PayPlus->mf_set_modx_data( "tno",      $posts[ "tno" ]      );      // KCP 원거래 거래번호
            $c_PayPlus->mf_set_modx_data( "mod_type", $mod_type            );      // 원거래 변경 요청 종류
            $c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip             );      // 변경 요청자 IP
            $c_PayPlus->mf_set_modx_data( "mod_desc", $posts[ "mod_desc" ] );      // 변경 사유

            if ( $mod_type == "STPC" ) // 부분취소의 경우
            {
                $c_PayPlus->mf_set_modx_data( "mod_mny", $posts[ "mod_mny" ] ); // 취소요청금액
                $c_PayPlus->mf_set_modx_data( "rem_mny", $posts[ "rem_mny" ] ); // 취소가능잔액
            }
        }
    /* ============================================================================== */


    /* ============================================================================== */
    /* =   03-3. 실행                                                               = */
    /* ------------------------------------------------------------------------------ */
        if ( $tran_cd != "" )
        {
            $c_PayPlus->mf_do_tx( $trace_no, $g_conf_home_dir, $g_conf_site_cd, "", $tran_cd, "",
                                  $g_conf_gw_url, $g_conf_gw_port, "payplus_cli_slib", $ordr_idxx,
                                  $cust_ip, "3" , 0, 0, $g_conf_log_path ); // 응답 전문 처리

            $res_cd  = $c_PayPlus->m_res_cd;  // 결과 코드
            $res_msg = $c_PayPlus->m_res_msg; // 결과 메시지
        }
        else
        {
            $c_PayPlus->m_res_cd  = "9562";
            $c_PayPlus->m_res_msg = "연동 오류|Payplus Plugin이 설치되지 않았거나 tran_cd값이 설정되지 않았습니다.";
        }

    /* ============================================================================== */


    /* ============================================================================== */
    /* =   04. 승인 결과 처리                                                       = */
    /* = -------------------------------------------------------------------------- = */
        if ( $req_tx == "pay" )
        {
            if ( $res_cd == "0000" )
            {
                $tno   = $c_PayPlus->mf_get_res_data( "tno"       ); // KCP 거래 고유 번호

    /* = -------------------------------------------------------------------------- = */
    /* =   04-1. 신용카드 승인 결과 처리                                            = */
    /* = -------------------------------------------------------------------------- = */
                if ( $pay_method == "CARD" )
                {
                    $card_cd   = $c_PayPlus->mf_get_res_data( "card_cd"   ); // 카드사 코드
                    $card_no   = $c_PayPlus->mf_get_res_data( "card_no"   ); // 카드 번호
                    $card_name = $c_PayPlus->mf_get_res_data( "card_name" ); // 카드 종류
                    $app_time  = $c_PayPlus->mf_get_res_data( "app_time"  ); // 승인 시간
                    $app_no    = $c_PayPlus->mf_get_res_data( "app_no"    ); // 승인 번호
                    $noinf     = $c_PayPlus->mf_get_res_data( "noinf"     ); // 무이자 여부 ( 'Y' : 무이자 )
                    $quota     = $c_PayPlus->mf_get_res_data( "quota"     ); // 할부 개월 수
                }

    /* = -------------------------------------------------------------------------- = */
    /* =   04-2. 승인 결과를 업체 자체적으로 DB 처리 작업하시는 부분입니다.         = */
    /* = -------------------------------------------------------------------------- = */
    /* =         승인 결과를 DB 작업 하는 과정에서 정상적으로 승인된 건에 대해      = */
    /* =         DB 작업을 실패하여 DB update 가 완료되지 않은 경우, 자동으로       = */
    /* =         승인 취소 요청을 하는 프로세스가 구성되어 있습니다.                = */
    /* =         DB 작업이 실패 한 경우, bSucc 라는 변수(String)의 값을 "false"     = */
    /* =         로 세팅해 주시기 바랍니다. (DB 작업 성공의 경우에는 "false" 이외의 = */
    /* =         값을 세팅하시면 됩니다.)                                           = */
    /* = -------------------------------------------------------------------------- = */
		    $bSucc = "";             // DB 작업 실패일 경우 "false" 로 세팅

    /* = -------------------------------------------------------------------------- = */
    /* =   04-3. DB 작업 실패일 경우 자동 승인 취소                                 = */
    /* = -------------------------------------------------------------------------- = */
            if ( $req_tx == "pay" )
            {
                if( $res_cd == "0000" )
                {
                    if ( $bSucc == "false" )
                    {
                        $c_PayPlus->mf_clear();

                        $tran_cd = "00200000";

                        $c_PayPlus->mf_set_modx_data( "tno",      $tno                         );  // KCP 원거래 거래번호
                        $c_PayPlus->mf_set_modx_data( "mod_type", "STSC"                       );  // 원거래 변경 요청 종류
                        $c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip                     );  // 변경 요청자 IP (옵션값)
                        $c_PayPlus->mf_set_modx_data( "mod_desc", "결과 처리 오류 - 자동 취소" );  // 변경 사유

                         $c_PayPlus->mf_do_tx( $trace_no, $g_conf_home_dir, $g_conf_site_cd, "", $tran_cd, "",
                                  $g_conf_gw_url, $g_conf_gw_port, "payplus_cli_slib", $ordr_idxx,
                                  $cust_ip, "3" , 0, 0, $g_conf_log_path ); // 응답 전문 처리

                        $res_cd  = $c_PayPlus->m_res_cd;
                        $res_msg = $c_PayPlus->m_res_msg;
                    }
                }
            } // End of [res_cd = "0000"]
            }
        }

    /* ============================================================================== */
    /* =   05. 취소/매입 결과 처리                                                  = */
    /* = -------------------------------------------------------------------------- = */
        else if ( $req_tx == "mod" )
        {
            if ( $res_cd == "0000" )
            {
                if ( $mod_type == "STPC" )
                {
                $amount       = $c_PayPlus->mf_get_res_data( "amount"       ); // 총 금액
                $panc_mod_mny = $c_PayPlus->mf_get_res_data( "panc_mod_mny" ); // 부분취소 요청금액
                $panc_rem_mny = $c_PayPlus->mf_get_res_data( "panc_rem_mny" ); // 부분취소 가능금액
                }
            }
        }
    /* ============================================================================== */

    /* ============================================================================== */
    /* =   06. 폼 구성 및 결과페이지 호출                                           = */
    /* ============================================================================== */
    
$results = array(
    'req_tx' => $req_tx,           // 요청 구분
    'pay_method' => $pay_method,   // 사용한 결제 수단
    'bSucc' => $bSucc,             // 쇼핑몰 DB 처리 성공 여부
    'mod_type' => $mod_type,       // 수정 타입
    'amount' => $amount,           // 총 금액
    'panc_mod_mny' => $panc_mod_mny, // 부분취소 요청금액
    'panc_rem_mny' => $panc_rem_mny, // 부분취소 가능금액
    'res_cd' => $res_cd,           // 결과 코드
    'res_msg' => iconv("euc-kr", "utf-8", $res_msg),         // 결과 메세지
    'ordr_idxx' => $ordr_idxx,     // 주문번호
    'tno' => $tno,                 // KCP 거래번호
    'good_mny' => $good_mny,       // 결제금액
    'good_name' => $good_name,     // 상품명
    'buyr_name' => $buyr_name,     // 주문자명
    'buyr_tel1' => $buyr_tel1,     // 주문자 전화번호
    'buyr_tel2' => $buyr_tel2,     // 주문자 휴대폰번호
    'buyr_mail' => $buyr_mail,     // 주문자 E-mail
    'card_cd' => $card_cd,         // 카드코드
    'card_no' => $card_no,         // 카드번호
    'card_name' => iconv("euc-kr", "utf-8", $card_name),     // 카드명
    'app_time' => $app_time,       // 승인시간
    'app_no' => $app_no,           // 승인번호
    'quota' => $quota,             // 할부개월
    'noinf' => $noinf,             // 무이자여부
);

// locale 설정 초기화
setlocale(LC_CTYPE, '');