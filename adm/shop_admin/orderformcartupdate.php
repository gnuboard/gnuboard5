<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$od_id = isset($_POST['od_id']) ? safe_replace_regex($_POST['od_id'], 'od_id') : '';
$ct_status = isset($_POST['ct_status']) ? clean_xss_tags($_POST['ct_status'], 1, 1) : '';
if ($ct_status === '' && isset($_POST['submit_ct_status'])) {
    $ct_status = clean_xss_tags($_POST['submit_ct_status'], 1, 1);
}
$pg_cancel = isset($_POST['pg_cancel']) ? (int) $_POST['pg_cancel'] : 0;
$nicepay_refund_acct_no = isset($_POST['RefundAcctNo']) ? preg_replace('/[^0-9]/', '', $_POST['RefundAcctNo']) : '';
$nicepay_refund_bank_cd = isset($_POST['RefundBankCd']) ? preg_replace('/[^0-9]/', '', $_POST['RefundBankCd']) : '';
$nicepay_refund_acct_nm = isset($_POST['RefundAcctNm']) ? trim(clean_xss_tags($_POST['RefundAcctNm'], 1, 1)) : '';

$ct_chk_count = isset($_POST['ct_chk']) ? count($_POST['ct_chk']) : 0;
if(!$ct_chk_count)
    alert('처리할 자료를 하나 이상 선택해 주십시오.');

$status_normal = array('주문','입금','준비','배송','완료');
$status_cancel = array('취소','반품','품절');

if (in_array($ct_status, $status_normal) || in_array($ct_status, $status_cancel)) {
    ; // 통과
} else {
    alert('변경할 상태가 올바르지 않습니다.');
}

// INIpay PRO 전체취소는 로컬 상품상태를 바꾸기 전에 PG 취소를 먼저 확정한다.
// PG 취소 실패 후에도 주문만 취소되어 가상계좌 입금이 남는 상태를 방지한다.
$inicis_pro_cancel_preprocessed = false;
$inicis_pro_order_lock = '';
if (in_array($_POST['ct_status'], $status_cancel)) {
    $selected_ct_ids = array();
    $posted_ct_count = isset($_POST['ct_id']) && is_array($_POST['ct_id']) ? count($_POST['ct_id']) : 0;
    for ($pre_i = 0; $pre_i < $posted_ct_count; $pre_i++) {
        $pre_k = isset($_POST['ct_chk'][$pre_i]) ? (int) $_POST['ct_chk'][$pre_i] : -1;
        if ($pre_k < 0 || !isset($_POST['ct_id'][$pre_k]))
            continue;
        $pre_ct_id = (int) $_POST['ct_id'][$pre_k];
        if ($pre_ct_id > 0)
            $selected_ct_ids[$pre_ct_id] = $pre_ct_id;
    }

    if (count($selected_ct_ids)) {
        $selected_ct_sql = implode(',', array_values($selected_ct_ids));
        $future = sql_fetch(" select count(*) as total_count,
                                    sum(if(ct_status in ('취소','반품','품절') or ct_id in ($selected_ct_sql), 1, 0)) as cancel_count
                               from {$g5['g5_shop_cart_table']}
                              where od_id = '".sql_escape_string($od_id)."' ");
        if ((int) $future['total_count'] > 0 && (int) $future['total_count'] === (int) $future['cancel_count']) {
            $pre_od = sql_fetch(" select * from {$g5['g5_shop_order_table']} where od_id = '".sql_escape_string($od_id)."' ");
            if (!empty($pre_od['od_tno']) && $pre_od['od_pg'] === 'inicis') {
                include_once(G5_SHOP_PATH.'/inicis/pro/inicis_pro.lib.php');
                $pro_tables = inicis_pro_audit_tables();
                $pro_summary = sql_fetch(" select * from `{$pro_tables['summary']}`
                                            where ip_oid = '".sql_escape_string($pre_od['od_id'])."'
                                              and ip_tid = '".sql_escape_string($pre_od['od_tno'])."' ", false);
                if (!empty($pro_summary['ip_id'])) {
                    $inicis_pro_order_lock = inicis_pro_lock($pre_od['od_id']);
                    if ($inicis_pro_order_lock === '')
                        alert('동일 주문의 결제 또는 통보 처리가 진행 중입니다. 잠시 후 다시 취소해 주십시오.');

                    // KG이니시스 상점관리자에서 이미 취소한 거래이면 PG 취소 없이 주문 취소만 진행한다.
                    $pre_inquiry = inicis_pro_inquiry($pre_od['od_tno'], $pre_od['od_id'], $pro_summary);
                    $pre_inquiry_data = isset($pre_inquiry['data']) && is_array($pre_inquiry['data']) ? $pre_inquiry['data'] : array();
                    $pre_pg_status = isset($pre_inquiry_data['status']) ? strtoupper(preg_replace('/[^A-Za-z0-9_]/', '', (string) $pre_inquiry_data['status'])) : '';

                    if (!empty($pre_inquiry['success']) && in_array($pre_pg_status, array('1', 'C', 'CANCEL', 'DEPOSIT_CANCELED', 'REFUND_COMPLETED'))) {
                        inicis_pro_save_inquiry($pre_od['od_id'], $pre_inquiry, 'admin');
                        inicis_pro_audit_write($pre_od['od_id'], 'cancel', 'canceled', array(
                            'tid' => $pre_od['od_tno'],
                            'mid' => $pro_summary['ip_mid'],
                            'amount' => isset($pro_summary['ip_amount']) ? (int) $pro_summary['ip_amount'] : 0,
                            'pay_type' => isset($pro_summary['ip_pay_type']) ? $pro_summary['ip_pay_type'] : '',
                            'source' => 'admin',
                            'code' => $pre_pg_status,
                            'message' => 'KG이니시스에서 이미 취소된 거래로 확인되어 주문 취소만 진행합니다.'
                        ));
                        $inicis_pro_cancel_preprocessed = true;
                    } else {
                        $pre_refunded = (int) $pre_od['od_refund_price'];
                        $pre_remaining = (int) $pre_od['od_receipt_price'] - $pre_refunded;

                        if ($pre_refunded > 0 && $pre_remaining <= 0) {
                            // 이전 부분취소로 결제금액이 모두 환불된 주문은 PG 취소 없이 주문 취소만 진행한다.
                            inicis_pro_audit_write($pre_od['od_id'], 'cancel', 'canceled', array(
                                'tid' => $pre_od['od_tno'],
                                'mid' => $pro_summary['ip_mid'],
                                'amount' => isset($pro_summary['ip_amount']) ? (int) $pro_summary['ip_amount'] : 0,
                                'pay_type' => isset($pro_summary['ip_pay_type']) ? $pro_summary['ip_pay_type'] : '',
                                'source' => 'admin',
                                'message' => '이전 부분취소로 결제금액이 모두 환불되어 주문 취소만 진행합니다.'
                            ));
                            $inicis_pro_cancel_preprocessed = true;
                        } elseif (!isset($_POST['pg_cancel']) || (int) $_POST['pg_cancel'] !== 1) {
                            // PG 승인취소 없이 주문만 취소하는 선택을 이력에 남긴다. 결제는 KG이니시스에 승인 상태로 남는다.
                            inicis_pro_audit_write($pre_od['od_id'], 'cancel', 'canceled', array(
                                'tid' => $pre_od['od_tno'],
                                'mid' => $pro_summary['ip_mid'],
                                'amount' => isset($pro_summary['ip_amount']) ? (int) $pro_summary['ip_amount'] : 0,
                                'pay_type' => isset($pro_summary['ip_pay_type']) ? $pro_summary['ip_pay_type'] : '',
                                'source' => 'admin',
                                'message' => 'PG 승인취소 없이 주문만 취소했습니다. 결제는 KG이니시스에 남아 있습니다.',
                                'event_only' => '1'
                            ));
                        } else {
                            $pro_environment = !empty($pro_summary['ip_environment']) ? $pro_summary['ip_environment'] : inicis_pro_environment();
                            if ($pro_summary['ip_mid'] !== inicis_pro_get_mid(!empty($pro_summary['ip_pay_type']) ? $pro_summary['ip_pay_type'] : null) || $pro_environment !== inicis_pro_environment())
                                alert('거래 당시 MID 또는 결제환경과 현재 설정이 달라 PG 취소를 실행할 수 없습니다. KG이니시스 상점관리자에서 원거래를 확인해 주십시오.');

                            include_once(G5_SHOP_PATH.'/settle_inicis.inc.php');
                            $pre_cancel_args = array(
                                'paymethod' => get_type_inicis_paymethod($pre_od['od_settle_case']),
                                'tid' => $pre_od['od_tno'],
                                'mid' => $pro_summary['ip_mid'],
                                'audit_oid' => $pre_od['od_id'],
                                'audit_source' => 'admin',
                                'msg' => '쇼핑몰 운영자 승인 취소',
                                'url' => $pro_environment === 'test' ? 'https://stginiapi.inicis.com/api/v1/refund' : 'https://iniapi.inicis.com/api/v1/refund'
                            );
                            // 부분취소 이력이 있는 거래는 전체취소 요청이 거부되므로 잔여 금액을 부분취소로 처리한다.
                            $pre_cancel_is_part = $pre_refunded > 0 && $pre_remaining > 0;
                            if ($pre_cancel_is_part) {
                                $pre_cancel_args['msg'] = '쇼핑몰 운영자 승인 취소(잔여금액)';
                                $pre_cancel_args['price'] = $pre_remaining;
                                $pre_cancel_args['confirmPrice'] = 0;
                            }
                            $pre_cancel_response = inicis_tid_cancel($pre_cancel_args, $pre_cancel_is_part);
                            $pre_cancel_result = json_decode($pre_cancel_response, true);
                            if (!isset($pre_cancel_result['resultCode']) || $pre_cancel_result['resultCode'] !== '00') {
                                $pre_cancel_code = !empty($pre_cancel_result['resultCode']) ? $pre_cancel_result['resultCode'] : 'COMMUNICATION_FAILED';
                                $pre_cancel_message = !empty($pre_cancel_result['resultMsg']) ? $pre_cancel_result['resultMsg'] : 'KG이니시스 취소 응답을 확인하지 못했습니다.';
                                alert($pre_cancel_message.' 코드 : '.$pre_cancel_code);
                            }
                            if ($pre_cancel_is_part) {
                                inicis_pro_audit_write($pre_od['od_id'], 'cancel', 'canceled', array(
                                    'tid' => $pre_od['od_tno'],
                                    'mid' => $pro_summary['ip_mid'],
                                    'amount' => isset($pro_summary['ip_amount']) ? (int) $pro_summary['ip_amount'] : 0,
                                    'pay_type' => isset($pro_summary['ip_pay_type']) ? $pro_summary['ip_pay_type'] : '',
                                    'source' => 'admin',
                                    'code' => '00',
                                    'message' => '잔여 금액 부분취소로 전체취소를 완료했습니다.'
                                ));
                            }
                            $inicis_pro_cancel_preprocessed = true;
                        }
                    }
                }
            }
        }
    }
}

if ($pg_cancel === 1 && in_array($ct_status, $status_cancel)) {
    $nicepay_refund_order = sql_fetch(" select od_id, od_pg, od_settle_case, od_status, od_receipt_price from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");

    if (isset($nicepay_refund_order['od_id']) && $nicepay_refund_order['od_pg'] === 'nicepay' && $nicepay_refund_order['od_settle_case'] === '가상계좌' && is_cancel_shop_pg_order($nicepay_refund_order)) {
        $nicepay_refund_required = ((int) $nicepay_refund_order['od_receipt_price'] > 0);
        $nicepay_refund_inputted = ($nicepay_refund_acct_no !== '' || $nicepay_refund_bank_cd !== '' || $nicepay_refund_acct_nm !== '');

        if (!$nicepay_refund_required && !$nicepay_refund_inputted) {
            $nicepay_refund_acct_no = '';
            $nicepay_refund_bank_cd = '';
            $nicepay_refund_acct_nm = '';
        } else {
            if ($nicepay_refund_acct_no === '' || strlen($nicepay_refund_acct_no) > 16) {
                alert('나이스페이 가상계좌 취소를 위해 환불계좌번호를 숫자 16자리 이하로 입력해 주십시오.');
            }

            if (!preg_match('/^[0-9]{3}$/', $nicepay_refund_bank_cd)) {
                alert('나이스페이 가상계좌 취소를 위해 환불계좌코드를 숫자 3자리로 입력해 주십시오.');
            }

            if ($nicepay_refund_acct_nm === '') {
                alert('나이스페이 가상계좌 취소를 위해 환불계좌주명을 입력해 주십시오.');
            }

            $nicepay_refund_acct_nm_euckr = function_exists('iconv') ? @iconv('UTF-8', 'EUC-KR//IGNORE', $nicepay_refund_acct_nm) : $nicepay_refund_acct_nm;
            if (strlen($nicepay_refund_acct_nm_euckr) > 10) {
                alert('나이스페이 환불계좌주명은 10 byte 이하로 입력해 주십시오.');
            }
        }
    }
}

$search = isset($_REQUEST['search']) ? get_search_string($_REQUEST['search']) : '';
$sort1 = isset($_REQUEST['sort1']) ? clean_xss_tags($_REQUEST['sort1'], 1, 1) : '';
$sort2 = isset($_REQUEST['sort2']) ? clean_xss_tags($_REQUEST['sort2'], 1, 1) : '';
$sel_field = isset($_REQUEST['sel_field']) ? clean_xss_tags($_REQUEST['sel_field'], 1, 1) : '';

$mod_history = '';
$cnt = (isset($_POST['ct_id']) && is_array($_POST['ct_id'])) ? count($_POST['ct_id']) : 0;
$arr_it_id = array();

for ($i=0; $i<$cnt; $i++)
{
    $k = isset($_POST['ct_chk'][$i]) ? (int) $_POST['ct_chk'][$i] : '';

    if($k === '') continue;

    $ct_id = isset($_POST['ct_id'][$k]) ? (int) $_POST['ct_id'][$k] : 0;

    if(!$ct_id)
        continue;

    $sql = " select * from {$g5['g5_shop_cart_table']} where od_id = '$od_id' and ct_id  = '$ct_id' ";
    $ct = sql_fetch($sql);
    if(! (isset($ct['ct_id']) && $ct['ct_id']))
        continue;

    // 수량이 변경됐다면
    $ct_qty = isset($_POST['ct_qty'][$k]) ? (int) $_POST['ct_qty'][$k] : 0;
    if($ct['ct_qty'] != $ct_qty) {
        $diff_qty = $ct['ct_qty'] - $ct_qty;

        // 재고에 차이 반영.
        if($ct['ct_stock_use']) {
            if($ct['io_id']) {
                $sql = " update {$g5['g5_shop_item_option_table']}
                            set io_stock_qty = io_stock_qty + '$diff_qty'
                            where it_id = '{$ct['it_id']}'
                              and io_id = '{$ct['io_id']}'
                              and io_type = '{$ct['io_type']}' ";
            } else {
                $sql = " update {$g5['g5_shop_item_table']}
                            set it_stock_qty = it_stock_qty + '$diff_qty'
                            where it_id = '{$ct['it_id']}' ";
            }

            sql_query($sql);
        }

        // 수량변경
        $sql = " update {$g5['g5_shop_cart_table']}
                    set ct_qty = '$ct_qty'
                    where ct_id = '$ct_id'
                      and od_id = '$od_id' ";
        sql_query($sql);
        $mod_history .= G5_TIME_YMDHIS.' '.$ct['ct_option'].' 수량변경 '.$ct['ct_qty'].' -> '.$ct_qty."\n";
    }

    // 재고를 이미 사용했다면 (재고에서 이미 뺐다면)
    $stock_use = $ct['ct_stock_use'];
    if ($ct['ct_stock_use'])
    {
        if ($ct_status == '주문' || $ct_status == '취소' || $ct_status == '반품' || $ct_status == '품절')
        {
            $stock_use = 0;
            // 재고에 다시 더한다.
            if($ct['io_id']) {
                $sql = " update {$g5['g5_shop_item_option_table']}
                            set io_stock_qty = io_stock_qty + '{$ct['ct_qty']}'
                            where it_id = '{$ct['it_id']}'
                              and io_id = '{$ct['io_id']}'
                              and io_type = '{$ct['io_type']}' ";
            } else {
                $sql = " update {$g5['g5_shop_item_table']}
                            set it_stock_qty = it_stock_qty + '{$ct['ct_qty']}'
                            where it_id = '{$ct['it_id']}' ";
            }

            sql_query($sql);
        }
    }
    else
    {
        // 재고 오류로 인한 수정
        if ($ct_status == '배송' || $ct_status == '완료')
        {
            $stock_use = 1;
            // 재고에서 뺀다.
            if($ct['io_id']) {
                $sql = " update {$g5['g5_shop_item_option_table']}
                            set io_stock_qty = io_stock_qty - '{$ct['ct_qty']}'
                            where it_id = '{$ct['it_id']}'
                              and io_id = '{$ct['io_id']}'
                              and io_type = '{$ct['io_type']}' ";
            } else {
                $sql = " update {$g5['g5_shop_item_table']}
                            set it_stock_qty = it_stock_qty - '{$ct['ct_qty']}'
                            where it_id = '{$ct['it_id']}' ";
            }

            sql_query($sql);
        }
        /* 주문 수정에서 "품절" 선택시 해당 상품 자동 품절 처리하기
        else if ($ct_status == '품절') {
            $stock_use = 1;
            // 재고에서 뺀다.
            $sql =" update {$g5['g5_shop_item_table']} set it_stock_qty = 0 where it_id = '{$ct['it_id']}' ";
            sql_query($sql);
        } */
    }

    $point_use = $ct['ct_point_use'];
    // 회원이면서 포인트가 0보다 크면
    // 이미 포인트를 부여했다면 뺀다.
    if ($mb_id && $ct['ct_point'] && $ct['ct_point_use'])
    {
        $point_use = 0;
        //insert_point($mb_id, (-1) * ($ct[ct_point] * $ct[ct_qty]), "주문번호 $od_id ($ct_id) 취소");
        delete_point($mb_id, "@delivery", $mb_id, "$od_id,$ct_id");
    }

    // 히스토리에 남김
    // 히스토리에 남길때는 작업|아이디|시간|IP|그리고 나머지 자료
    $now = G5_TIME_YMDHIS;
    $ct_history="\n$ct_status|{$member['mb_id']}|$now|$REMOTE_ADDR";

    $sql = " update {$g5['g5_shop_cart_table']}
                set ct_point_use  = '$point_use',
                    ct_stock_use  = '$stock_use',
                    ct_status     = '$ct_status',
                    ct_history    = CONCAT(ct_history,'$ct_history')
                where od_id = '$od_id'
                and ct_id  = '$ct_id' ";
    sql_query($sql);

    // it_id를 배열에 저장
    if($ct_status == '주문' || $ct_status == '취소' || $ct_status == '반품' || $ct_status == '품절' || $ct_status == '완료')
        $arr_it_id[] = $ct['it_id'];
}

// 상품 판매수량 반영
if(is_array($arr_it_id) && !empty($arr_it_id)) {
    $unq_it_id = array_unique($arr_it_id);

    foreach($unq_it_id as $it_id) {
        $sql2 = " select sum(ct_qty) as sum_qty from {$g5['g5_shop_cart_table']} where it_id = '$it_id' and ct_status = '완료' ";
        $row2 = sql_fetch($sql2);

        $sql3 = " update {$g5['g5_shop_item_table']} set it_sum_qty = '{$row2['sum_qty']}' where it_id = '$it_id' ";
        sql_query($sql3);
    }
}

// 장바구니 상품 모두 취소일 경우 주문상태 변경
$cancel_change = false;
if (in_array($ct_status, $status_cancel)) {
    $sql = " select count(*) as od_count1,
                    SUM(IF(ct_status = '취소' OR ct_status = '반품' OR ct_status = '품절', 1, 0)) as od_count2
                from {$g5['g5_shop_cart_table']}
                where od_id = '$od_id' ";
    $row = sql_fetch($sql);

    if($row['od_count1'] == $row['od_count2']) {
        $cancel_change = true;

        $pg_res_cd = '';
        $pg_res_msg = '';
        $pg_cancel_log = '';

        // PG 신용카드 결제 취소일 때
        if($pg_cancel == 1) {
            $sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
            $od = sql_fetch($sql);

            if ($od['od_tno'] && is_cancel_shop_pg_order($od)) {
                switch($od['od_pg']) {
                    case 'lg':
                        include_once(G5_SHOP_PATH.'/settle_lg.inc.php');

                        $LGD_TID = $od['od_tno'];

                        $xpay = new XPay($configPath, $CST_PLATFORM);

                        // Mert Key 설정
                        $xpay->set_config_value('t'.$LGD_MID, $config['cf_lg_mert_key']);
                        $xpay->set_config_value($LGD_MID, $config['cf_lg_mert_key']);

                        $xpay->Init_TX($LGD_MID);

                        $xpay->Set('LGD_TXNAME', 'Cancel');
                        $xpay->Set('LGD_TID', $LGD_TID);

                        if ($xpay->TX()) {
                            $res_cd = $xpay->Response_Code();
                            if($res_cd != '0000' && $res_cd != 'AV11') {
                                $pg_res_cd = $res_cd;
                                $pg_res_msg = $xpay->Response_Msg();
                            }
                        } else {
                            $pg_res_cd = $xpay->Response_Code();
                            $pg_res_msg = $xpay->Response_Msg();
                        }
                        break;
                    case 'toss':
                        $cancel_msg = '쇼핑몰 운영자 승인 취소';
                        include_once(G5_SHOP_PATH.'/toss/toss_cancel.php');
                        break;
                    case 'inicis':
                        include_once(G5_SHOP_PATH.'/settle_inicis.inc.php');
                        $cancel_msg = '쇼핑몰 운영자 승인 취소';

                        if ($inicis_pro_cancel_preprocessed) {
                            $result = array('resultCode' => '00', 'resultMsg' => 'INIpay PRO PG 취소 선처리 완료');
                        } else {
                            $args = array(
                                'paymethod' => get_type_inicis_paymethod($od['od_settle_case']),
                                'tid' => $od['od_tno'],
                                'msg' => $cancel_msg
                            );

                            $response = inicis_tid_cancel($args);
                            $result = json_decode($response, true);
                        }

                        if (isset($result['resultCode'])) {
                            if ($result['resultCode'] != '00') {
                                $pg_res_cd = $result['resultCode'];
                                $pg_res_msg = $result['resultMsg'];
                            }
                        } else {
                            $pg_res_cd = '';
                            $pg_res_msg = 'curl 로 데이터를 받지 못했습니다.';
                        }

                        break;
                    case 'nicepay':
                        include_once(G5_SHOP_PATH.'/settle_nicepay.inc.php');
                        $cancel_msg = '쇼핑몰 운영자 승인 취소';
                        
                        $tno = $od['od_tno'];
                        
                        $cancelAmt = (int)$od['od_receipt_price'];
                        if($od['od_settle_case'] == '가상계좌' && $od['od_status'] == '주문' && $cancelAmt == 0)
                            $cancelAmt = (int)$od['od_misu'];

                        // 0:전체 취소, 1:부분 취소(별도 계약 필요)
                        $partialCancelCode = 0;

                        if($cancelAmt <= 0) {
                            $pg_res_cd = 'NO_CANCEL_AMT';
                            $pg_res_msg = '취소 요청금액이 없습니다.';
                            break;
                        }

                        include G5_SHOP_PATH.'/nicepay/cancel_process.php';

                        if (isset($result['ResultCode'])) {
                            // 실패했다면
                            if (! in_array($result['ResultCode'], array('2001', '2211'), true)) {
                                $pg_res_cd = $result['ResultCode'];
                                $pg_res_msg = $result['ResultMsg'];
                            }
                        } else {
                            $pg_res_cd = '';
                            $pg_res_msg = 'curl 로 데이터를 받지 못하거나 통신에 실패했습니다.';
                        }

                        break;
                    case 'KAKAOPAY':
                        include_once(G5_SHOP_PATH.'/settle_kakaopay.inc.php');
                        $_REQUEST['TID']               = $od['od_tno'];
                        $_REQUEST['Amt']               = $od['od_receipt_price'];
                        $_REQUEST['CancelMsg']         = '쇼핑몰 운영자 승인 취소';
                        $_REQUEST['PartialCancelCode'] = 0;
                        include G5_SHOP_PATH.'/kakaopay/kakaopay_cancel.php';
                        break;
                    default:
                        include_once(G5_SHOP_PATH.'/settle_kcp.inc.php');
                        require_once(G5_SHOP_PATH.'/kcp/pp_ax_hub_lib.php');

                        // locale ko_KR.euc-kr 로 설정
                        setlocale(LC_CTYPE, 'ko_KR.euc-kr');

                        $c_PayPlus = new C_PP_CLI_T;

                        $c_PayPlus->mf_clear();
                        
                        $ordr_idxx = $od['od_id'];
                        $tno = $od['od_tno'];
                        $tran_cd = '00200000';
                        $cancel_msg = iconv_euckr('쇼핑몰 운영자 승인 취소');
                        $cust_ip = $_SERVER['REMOTE_ADDR'];
                        $bSucc_mod_type = "STSC";

                        $c_PayPlus->mf_set_modx_data( "tno",      $tno                         );  // KCP 원거래 거래번호
                        $c_PayPlus->mf_set_modx_data( "mod_type", $bSucc_mod_type              );  // 원거래 변경 요청 종류
                        $c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip                     );  // 변경 요청자 IP
                        $c_PayPlus->mf_set_modx_data( "mod_desc", $cancel_msg );  // 변경 사유

                        $c_PayPlus->mf_do_tx( $tno,  $g_conf_home_dir, $g_conf_site_cd,
                                              $g_conf_site_key,  $tran_cd,    "",
                                              $g_conf_gw_url,  $g_conf_gw_port,  "payplus_cli_slib",
                                              $ordr_idxx, $cust_ip, "3" ,
                                              0, 0, $g_conf_key_dir, $g_conf_log_dir);

                        $res_cd  = $c_PayPlus->m_res_cd;
                        $res_msg = $c_PayPlus->m_res_msg;

                        if($res_cd != '0000') {
                            $pg_res_cd = $res_cd;
                            $pg_res_msg = iconv_utf8($res_msg);
                        }

                        // locale 설정 초기화
                        setlocale(LC_CTYPE, '');
                        break;
                }

                // PG 취소요청 성공했으면
                if($pg_res_cd == '') {
                    $pg_cancel_log = ' PG '.$od['od_settle_case'].' 승인취소 처리';
                    $sql = " update {$g5['g5_shop_order_table']}
                                set od_refund_price = '{$od['od_receipt_price']}'
                                where od_id = '$od_id' ";
                    sql_query($sql);
                }
            }
        }

        // 관리자 주문취소 로그
        $mod_history .= G5_TIME_YMDHIS.' '.$member['mb_id'].' 주문'.$ct_status.' 처리'.$pg_cancel_log."\n";
    }
}

// 미수금 등의 정보
$info = get_order_info($od_id);

if(!$info)
    alert('주문자료가 존재하지 않습니다.');

$sql = " update {$g5['g5_shop_order_table']}
            set od_cart_price   = '{$info['od_cart_price']}',
                od_cart_coupon  = '{$info['od_cart_coupon']}',
                od_coupon       = '{$info['od_coupon']}',
                od_send_coupon  = '{$info['od_send_coupon']}',
                od_cancel_price = '{$info['od_cancel_price']}',
                od_send_cost    = '{$info['od_send_cost']}',
                od_misu         = '{$info['od_misu']}',
                od_tax_mny      = '{$info['od_tax_mny']}',
                od_vat_mny      = '{$info['od_vat_mny']}',
                od_free_mny     = '{$info['od_free_mny']}' ";
if ($mod_history) { // 주문변경 히스토리 기록
    $sql .= " , od_mod_history = CONCAT(od_mod_history,'$mod_history') ";
}

if($cancel_change) {
    $sql .= " , od_status = '취소' "; // 주문상품 모두 취소, 반품, 품절이면 주문 취소
} else {
    if ($ct_status && in_array($ct_status, $status_normal)) { // 정상인 주문상태만 기록
        $sql .= " , od_status = '$ct_status' ";
    }
}

$sql .= " where od_id = '$od_id' ";
sql_query($sql);

$qstr = "sort1=$sort1&amp;sort2=$sort2&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page";

$url = "./orderform.php?od_id=$od_id&amp;$qstr";

if ($inicis_pro_order_lock !== '')
    inicis_pro_unlock($inicis_pro_order_lock);

// 신용카드 취소 때 오류가 있으면 알림
if($pg_cancel == 1 && $pg_res_cd && $pg_res_msg) {
    alert('오류코드 : '.$pg_res_cd.' 오류내용 : '.$pg_res_msg, $url);
} else {
    // 1.06.06
    $od = sql_fetch(" select od_receipt_point from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");
    if ($od['od_receipt_point'])
        alert("포인트로 결제한 주문은,\\n\\n주문상태 변경으로 인해 포인트의 가감이 발생하는 경우\\n\\n회원관리 > 포인트관리에서 수작업으로 포인트를 맞추어 주셔야 합니다.", $url);
    else
        goto_url($url);
}
