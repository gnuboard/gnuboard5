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

    $pro_oid = '';
    $pro_audit_values = array();
    $pro_cancel_needs_fallback = false;

    // INIpay PRO 승인 직후 주문 DB 저장에 실패한 경우에는 인증 TID로 망취소한다.
    // 망취소가 실패하면 아래의 기존 INIAPI 취소를 보조 경로로 계속 시도한다.
    if (!empty($_POST['inicis_pro'])) {
        include_once(G5_SHOP_PATH.'/inicis/pro/inicis_pro.lib.php');

        $pro_oid = isset($_POST['inicis_pro_oid']) ? inicis_pro_clean_oid($_POST['inicis_pro_oid']) : inicis_pro_clean_oid(get_session('ss_order_id'));
        $pro_log = inicis_pro_get_log($pro_oid);
        $pro_data = isset($pro_log['pro_data']) && is_array($pro_log['pro_data']) ? $pro_log['pro_data'] : array();
        $pro_auth_tid = isset($pro_data['P_AUTH_TID']) ? inicis_pro_clean_tid($pro_data['P_AUTH_TID']) : '';
        $pro_idc = isset($pro_data['P_IDCNAME']) ? strtolower($pro_data['P_IDCNAME']) : '';
        $pro_mid = isset($pro_data['P_MID']) ? $pro_data['P_MID'] : '';
        $pro_amount = isset($pro_data['P_AMT']) ? (int) $pro_data['P_AMT'] : 0;
        $pro_tid = isset($pro_data['P_APPL_TID']) ? inicis_pro_clean_tid($pro_data['P_APPL_TID']) : '';
        $pro_audit_values = array(
            'tid' => $pro_tid,
            'auth_tid' => $pro_auth_tid,
            'mid' => $pro_mid,
            'amount' => $pro_amount,
            'pay_type' => isset($pro_data['P_TYPE']) ? $pro_data['P_TYPE'] : '',
            'device' => isset($pro_data['__device']) ? $pro_data['__device'] : '',
            'source' => 'system',
            'message' => '주문 저장 이후 DB 처리 오류로 자동 취소를 요청했습니다.'
        );

        $pro_pay_type = isset($pro_data['P_TYPE']) ? $pro_data['P_TYPE'] : null;
        if ($pro_oid !== '' && $pro_auth_tid !== '' && $pro_mid === inicis_pro_get_mid($pro_pay_type) && $pro_amount > 0) {
            inicis_pro_audit_write($pro_oid, 'cancel', 'cancel_requested', $pro_audit_values);
            $pro_cancel = inicis_pro_net_cancel($pro_idc, $pro_mid, $pro_auth_tid, $pro_amount, $pro_oid, isset($cancel_msg) ? $cancel_msg : 'DB FAIL', $pro_pay_type);
            if (!empty($pro_cancel['success'])) {
                sql_query(" update {$g5['g5_shop_inicis_log_table']}
                               set P_STATUS = 'cancel',
                                   P_AUTH_DT = '".preg_replace('/[^0-9]/', '', G5_TIME_YMDHIS)."',
                                   P_RMESG1 = 'INIpay PRO net-cancel success'
                             where oid = '".sql_escape_string($pro_oid)."' ", false);
                $pro_audit_values['code'] = isset($pro_cancel['data']['P_STATUS']) ? $pro_cancel['data']['P_STATUS'] : '00';
                $pro_audit_values['message'] = '주문 DB 처리 실패 후 망취소 완료';
                inicis_pro_audit_write($pro_oid, 'cancel', 'canceled', $pro_audit_values);
                return;
            }

            $pro_cancel_needs_fallback = true;
            $pro_audit_values['message'] = '망취소 결과를 확인하지 못해 INIAPI 취소를 시도합니다.';
            inicis_pro_audit_write($pro_oid, 'cancel', 'cancel_requested', $pro_audit_values);
        }
    }

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
            'msg' => 'DB FAIL',         // 취소사유
            'audit' => false
        );

        $response = inicis_tid_cancel($args); 

        if ($pro_oid !== '' && $pro_cancel_needs_fallback) {
            $result_code = '';
            if (is_string($response) && preg_match('/["\']resultCode["\']\s*:\s*["\']([^"\']+)["\']/', $response, $matches))
                $result_code = inicis_pro_cut($matches[1], 30);

            $pro_audit_values['code'] = $result_code;
            if ($result_code === '00') {
                $pro_audit_values['message'] = 'INIAPI 보조 취소 완료';
                inicis_pro_audit_write($pro_oid, 'cancel', 'canceled', $pro_audit_values);
            } else {
                $pro_audit_values['message'] = '자동 취소 결과를 확인하지 못했습니다. 상점관리자 확인이 필요합니다.';
                inicis_pro_audit_write($pro_oid, 'cancel', 'cancel_failed', $pro_audit_values);
            }
        }
    } elseif ($pro_oid !== '' && $pro_cancel_needs_fallback) {
        $pro_audit_values['message'] = '자동 취소수단을 확인하지 못했습니다. 상점관리자 확인이 필요합니다.';
        inicis_pro_audit_write($pro_oid, 'cancel', 'cancel_failed', $pro_audit_values);
    }
}
