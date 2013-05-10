<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
/* ============================================================================== */
/* =   06. 승인 및 실패 결과 DB처리                                             = */
/* = -------------------------------------------------------------------------- = */
/* =       결과를 업체 자체적으로 DB처리 작업하시는 부분입니다.                 = */
/* = -------------------------------------------------------------------------- = */

if ( $req_tx == "pay" )
{
    if( $res_cd == "0000" )
    {
        // 06-1-1. 신용카드
        if ( $use_pay_method == "100000000000" )
        {
            $trade_ymd = substr($app_time,0,4)."-".substr($app_time,4,2)."-".substr($app_time,6,2);
            $trade_hms = substr($app_time,8,2).":".substr($app_time,10,2).":".substr($app_time,12,2);

            // 카드내역 INSERT
            $sql = "insert {$g4['shop_card_history_table']}
                       set od_id = '$ordr_idxx',
                           uq_id = '$tmp_uq_id',
                           cd_mall_id = '$g_conf_site_cd',
                           cd_amount = '$good_mny',
                           cd_app_no = '$app_no',
                           cd_app_rt = '$res_cd',
                           cd_trade_ymd = '$trade_ymd',
                           cd_trade_hms = '$trade_hms',
                           cd_opt01 = '$buyr_name',
                           cd_time = NOW(),
                           cd_ip = '$cust_ip' ";
            $result = sql_query($sql, TRUE);
        }
        // 06-1-2. 계좌이체
        if ( $use_pay_method == "010000000000" )
        {
            $trade_ymd = date("Y-m-d", time());
            $trade_hms = date("H:i:s", time());

            // 계좌이체내역 INSERT
            $sql = "insert {$g4['shop_card_history_table']}
                       set od_id = '$ordr_idxx',
                           uq_id = '$tmp_uq_id',
                           cd_mall_id = '$g_conf_site_cd',
                           cd_amount = '$good_mny',
                           cd_app_no = '$tno',
                           cd_app_rt = '$res_cd',
                           cd_trade_ymd = '$trade_ymd',
                           cd_trade_hms = '$trade_hms',
                           cd_opt01 = '$buyr_name',
                           cd_time = NOW(),
                           cd_ip = '$cust_ip' ";
            $result = sql_query($sql, TRUE);
        }
        // 06-1-3. 가상계좌
        if ( $use_pay_method == "001000000000" )
        {
            $bankname = iconv("cp949", "utf8", $bankname);

            $trade_ymd = date("Y-m-d", time());
            $trade_hms = date("H:i:s", time());

            // 가상계좌내역 INSERT
            $sql = "insert {$g4['shop_card_history_table']}
                       set od_id = '$ordr_idxx',
                           uq_id = '$tmp_uq_id',
                           cd_mall_id = '$g_conf_site_cd',
                           cd_amount = '0',
                           cd_app_no = '$tno',
                           cd_app_rt = '$res_cd',
                           cd_trade_ymd = '$trade_ymd',
                           cd_trade_hms = '$trade_hms',
                           cd_opt01 = '$buyr_name',
                           cd_time = NOW(),
                           cd_ip = '$cust_ip' ";
            $result = sql_query($sql, TRUE);
        }
        // 06-1-4. 포인트
        if ( $use_pay_method == "000100000000" )
        {
        }
        // 06-1-5. 휴대폰
        if ( $use_pay_method == "000010000000" )
        {
            $trade_ymd = substr($app_time,0,8);
            $trade_hms = substr($app_time,8,6);

            // 휴대폰결제내역 INSERT
            $sql = "insert {$g4['shop_card_history_table']}
                       set od_id = '$ordr_idxx',
                           uq_id = '$tmp_uq_id',
                           cd_mall_id = '$g_conf_site_cd',
                           cd_amount = '$good_mny',
                           cd_app_no = '$tno',
                           cd_app_rt = '$res_cd',
                           cd_trade_ymd = '$trade_ymd',
                           cd_trade_hms = '$trade_hms',
                           cd_opt01 = '$buyr_name',
                           cd_opt02 = '$mobile_no $commid',
                           cd_time = NOW(),
                           cd_ip = '$cust_ip' ";
            $result = sql_query($sql, TRUE);
        }
        // 06-1-6. 상품권
         if ( $use_pay_method == "000000001000" )
        {
        }
    }

/* = -------------------------------------------------------------------------- = */
/* =   06. 승인 및 실패 결과 DB처리                                             = */
/* ============================================================================== */
    else if ( $req_cd != "0000" )
    {
    }
}
?>