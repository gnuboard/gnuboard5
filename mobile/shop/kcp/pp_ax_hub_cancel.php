<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// locale ko_KR.euc-kr 로 설정
setlocale(LC_CTYPE, 'ko_KR.euc-kr');

/* ============================================================================== */
/* =   07. 승인 결과 DB처리 실패시 : 자동취소                                   = */
/* = -------------------------------------------------------------------------- = */
/* =         승인 결과를 DB 작업 하는 과정에서 정상적으로 승인된 건에 대해      = */
/* =         DB 작업을 실패하여 DB update 가 완료되지 않은 경우, 자동으로       = */
/* =         승인 취소 요청을 하는 프로세스가 구성되어 있습니다.                = */
/* =                                                                            = */
/* =         DB 작업이 실패 한 경우, bSucc 라는 변수(String)의 값을 "false"     = */
/* =         로 설정해 주시기 바랍니다. (DB 작업 성공의 경우에는 "false" 이외의 = */
/* =         값을 설정하시면 됩니다.)                                           = */
/* = -------------------------------------------------------------------------- = */

$bSucc = "false"; // DB 작업 실패 또는 금액 불일치의 경우 "false" 로 세팅

/* = -------------------------------------------------------------------------- = */
/* =   07-1. DB 작업 실패일 경우 자동 승인 취소                                 = */
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
            $c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip                     );  // 변경 요청자 IP
            $c_PayPlus->mf_set_modx_data( "mod_desc", $cancel_msg );  // 변경 사유

            $c_PayPlus->mf_do_tx( "",  $g_conf_home_dir, $g_conf_site_cd,
                                  $g_conf_site_key,  $tran_cd,    "",
                                  $g_conf_gw_url,  $g_conf_gw_port,  "payplus_cli_slib",
                                  $ordr_idxx, $cust_ip,    $g_conf_log_level,
                                  0, 0 );

            $res_cd  = $c_PayPlus->m_res_cd;
            $res_msg = $c_PayPlus->m_res_msg;
        }
    }
} // End of [res_cd = "0000"]
/* ============================================================================== */

// locale 설정 초기화
setlocale(LC_CTYPE, '');