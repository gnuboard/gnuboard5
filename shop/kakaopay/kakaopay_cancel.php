<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

/*******************************************************************
 * 7. DB연동 실패 시 강제취소                                      *
 *                                                                 *
 * 지불 결과를 DB 등에 저장하거나 기타 작업을 수행하다가 실패하는  *
 * 경우, 아래의 코드를 참조하여 이미 지불된 거래를 취소하는 코드를 *
 * 작성합니다.                                                     *
 *******************************************************************/

$cancelFlag = "true";

// $cancelFlag를 "true"로 변경하는 condition 판단은 개별적으로
// 수행하여 주십시오.

if($cancelFlag == "true")
{

    if( isset($is_noti_pay) && $is_noti_pay ){
        return;
    }

    include_once(G5_SHOP_PATH.'/settle_kakaopay.inc.php');

    if( get_session('ss_order_id') && $tno ){

        $ini_oid = preg_replace('/[^a-z0-9_\-]/i', '', get_session('ss_order_id'));
        $tno = preg_replace('/[^a-z0-9_\-]/i', '', $tno);

        $sql = "select oid from {$g5['g5_shop_inicis_log_table']} where oid = '$ini_oid' and P_TID = '$tno' ";

        $exists_log = sql_fetch($sql);
        
        if( $exists_log['oid'] ){
            $sql = " update {$g5['g5_shop_inicis_log_table']}
                        set P_STATUS  = 'cancel',
                        P_AUTH_DT = '".preg_replace('/[^0-9]/', '', G5_TIME_YMDHIS)."' where oid = '$ini_oid' and P_TID = '$tno' ";
        } else {
            $sql = " insert into {$g5['g5_shop_inicis_log_table']}
                        set oid = '$ini_oid',
                            P_TID     = '$tno',
                            P_STATUS  = 'cancel',
                            P_AUTH_DT = '".preg_replace('/[^0-9]/', '', G5_TIME_YMDHIS)."' ";
        }

        sql_query($sql, false);
    }

    $db_check = 1;
    $cancel_msg = "DB FAIL";

    if( $is_admin ){
        $tmp = sql_fetch("select * from `{$g5['g5_shop_order_table']}` where od_tno = '".trim($_REQUEST['TID'])."' ");

        if( $tmp['od_pg'] === 'KAKAOPAY' ){
            $tno = trim($_REQUEST['TID']);

            $db_check = 0;
            $cancel_msg = isset($_REQUEST['CancelMsg']) ? iconv_euckr($_REQUEST['CancelMsg']) : iconv_euckr('관리자 승인 취소');
        }

    }

    $TID = $tno;
    $inipay->SetField("type", "cancel"); // 고정
    if( $default['de_kakaopay_cancelpwd'] ){
        $inipay->SetField("mid", $default['de_kakaopay_mid']);
        $inipay->SetField("admin", $default['de_kakaopay_cancelpwd']);
    }
    $inipay->SetField("tid", $TID); // 고정
    $inipay->SetField("cancelmsg", $cancel_msg); // 취소사유
    $inipay->startAction();
    if($inipay->GetResult('ResultCode') == "00" && $db_check)
    {
        $inipay->MakeTXErrMsg(MERCHANT_DB_ERR,"Merchant DB FAIL");
    }

    $res_cd  = $inipay->getResult('ResultCode');
    $res_msg = $inipay->getResult('ResultMsg');

    if($res_cd != '00') {
        $pg_res_cd = $res_cd;
        $pg_res_msg = iconv_utf8($res_msg);
    }
}