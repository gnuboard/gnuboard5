<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

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

    include_once(G5_SHOP_PATH.'/settle_inicis.inc.php');

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
    
    $ini_paymethod = get_type_inicis_paymethod($od_settle_case);

    if ($ini_paymethod){
        $args = array(
            'paymethod' => $ini_paymethod,
            'tid' => $tno,
            'msg' => 'DB FAIL'          // 취소사유
        );

        $response = inicis_tid_cancel($args); 
    }
}