<?php
$sub_menu = '600410';
include_once('./_common.php');

// print_r2($_POST);
// exit;

auth_check_menu($auth, $sub_menu, "w");

// check_admin_token();

$pb_chk_count = isset($_POST['pb_chk']) ? count($_POST['pb_chk']) : 0;
if (!$pb_chk_count)
    alert('처리할 자료를 하나 이상 선택해 주십시오.');

$status_normal = array('입금', '준비', '배송', '완료');
$status_cancel = array('취소', '반품', '품절');

if (in_array($_POST['pb_status'], $status_normal) || in_array($_POST['pb_status'], $status_cancel)) {; // 통과
} else {
    alert('변경할 상태가 올바르지 않습니다.');
}

$od_id = isset($_REQUEST['od_id']) ? clean_xss_tags($_REQUEST['od_id']) : '';
$pay_id = isset($_REQUEST['pay_id']) ? clean_xss_tags($_REQUEST['pay_id']) : '';
$search = isset($_REQUEST['search']) ? get_search_string($_REQUEST['search']) : '';
$sort1 = isset($_REQUEST['sort1']) ? clean_xss_tags($_REQUEST['sort1'], 1, 1) : '';
$sort2 = isset($_REQUEST['sort2']) ? clean_xss_tags($_REQUEST['sort2'], 1, 1) : '';
$sel_field = isset($_REQUEST['sel_field']) ? clean_xss_tags($_REQUEST['sel_field'], 1, 1) : '';

$mod_history = '';
$cnt = (isset($_POST['pb_id']) && is_array($_POST['pb_id'])) ? count($_POST['pb_id']) : 0;
$arr_it_id = array();

for ($i = 0; $i < $cnt; $i++) {
    $k = isset($_POST['pb_chk'][$i]) ? (int) $_POST['pb_chk'][$i] : '';

    if ($k === '') continue;

    $pb_id = isset($_POST['pb_id'][$k]) ? (int) $_POST['pb_id'][$k] : 0;

    if (!$pb_id)
        continue;

    $sql = "SELECT * 
            FROM {$g5['g5_subscription_pay_basket_table']} 
            WHERE od_id = '$od_id' 
            AND pay_id = '$pay_id'";

    $pb = sql_fetch($sql);

    if (!(isset($pb['pb_id']) && $pb['pb_id'])) {
        continue;
    }

    // 수량이 변경됐다면
    $pb_qty = isset($_POST['pb_qty'][$k]) ? (int) $_POST['pb_qty'][$k] : 0;
    if ($pb['pb_qty'] != $pb_qty) {
        $diff_qty = $pb['pb_qty'] - $pb_qty;
        
        // 재고에 차이 반영.
        if (isset($pb['pb_stock_status']) && in_array($pb['pb_stock_status'], array('used', 'reused'))) {
            if ($pb['io_id']) {
                $sql = " update {$g5['g5_shop_item_option_table']}
                            set io_stock_qty = io_stock_qty + '$diff_qty'
                            where it_id = '{$pb['it_id']}'
                              and io_id = '{$pb['io_id']}'
                              and io_type = '{$pb['io_type']}' ";
            } else {
                $sql = " update {$g5['g5_shop_item_table']}
                            set it_stock_qty = it_stock_qty + '$diff_qty'
                            where it_id = '{$pb['it_id']}' ";
            }

            sql_query($sql);
        }

        // 수량변경
        $sql = " update {$g5['g5_subscription_pay_basket_table']}
                    set pb_qty = '$pb_qty'
                    where pb_id = '$pb_id'
                      and od_id = '$od_id' ";
        sql_query($sql);
        $mod_history .= G5_TIME_YMDHIS . ' ' . $pb['pb_option'] . ' 수량변경 ' . $pb['pb_qty'] . ' -> ' . $pb_qty . "\n";
    }

    // 재고를 이미 사용했다면 (재고에서 이미 뺐다면)
    $stock_use = $pb['pb_stock_use'];
    if (in_array($pb_status, array('취소', '반품', '품절'))) {
        $stock_use = 0;
        // 재고에서 뺀것을 복원한다.
        subscription_pay_process_stock($pay_id, 'increase');
    } else if (in_array($pb_status, array('입금', '준비', '배송', '완료'))) {
        $stock_use = 1;
        // 재고에서 뺀다.
        subscription_pay_process_stock($pay_id);
    }

    $point_use = $pb['pb_point_use'];
    // 회원이면서 포인트가 0보다 크면
    // 이미 포인트를 부여했다면 뺀다.
    if ($mb_id && $pb['pb_point'] && $pb['pb_point_use']) {
        $point_use = 0;
        delete_point($mb_id, "@delivery", $mb_id, "$od_id,$pb_id");
    }

    // 히스토리에 남김
    // 히스토리에 남길때는 작업|아이디|시간|IP|그리고 나머지 자료
    $now = G5_TIME_YMDHIS;
    $remote_addr = $_SERVER['REMOTE_ADDR'];
    $pb_history = "\n$pb_status|{$member['mb_id']}|$now|$remote_addr";

    $sql = " update {$g5['g5_subscription_pay_basket_table']}
                set pb_point_use  = '$point_use',
                    pb_stock_use  = '$stock_use',
                    pb_status     = '$pb_status',
                    pb_history    = CONCAT(pb_history,'$pb_history')
                where od_id = '$od_id'
                and pb_id  = '$pb_id' ";
    sql_query($sql);

    // it_id를 배열에 저장
    if (in_array($pb_status, array('취소', '반품', '품절', '완료'))) {
        $arr_it_id[] = $pb['it_id'];
    }
}

// 상품 판매수량 반영
if (is_array($arr_it_id) && !empty($arr_it_id)) {
    $unq_it_id = array_unique($arr_it_id);

    foreach ($unq_it_id as $it_id) {
        $sql2 = " select sum(pb_qty) as sum_qty from {$g5['g5_subscription_pay_basket_table']} where it_id = '$it_id' and pb_status = '완료' ";
        $row2 = sql_fetch($sql2);

        $sql3 = " update {$g5['g5_shop_item_table']} set it_sum_qty = '{$row2['sum_qty']}' where it_id = '$it_id' ";
        sql_query($sql3);
    }
}

// 장바구니 상품 모두 취소일 경우 주문상태 변경
$cancel_change = false;
if (in_array($_POST['pb_status'], $status_cancel)) {
    $sql = " select count(*) as od_count1,
                    SUM(IF(pb_status = '취소' OR pb_status = '반품' OR pb_status = '품절', 1, 0)) as od_count2
                from {$g5['g5_subscription_pay_basket_table']}
                where od_id = '$od_id' and pay_id = '$pay_id'";

    $row = sql_fetch($sql);

    if ($row['od_count1'] == $row['od_count2']) {
        $cancel_change = true;

        $pg_res_cd = '';
        $pg_res_msg = '';
        $pg_cancel_log = '';

        // PG 신용카드 결제 취소일 때
        if ($pg_cancel == 1) {

            $pay = get_subscription_pay($pay_id, $od_id);

            if ($pay['py_tno'] && is_cancel_subscription_pg_order($pay)) {
                switch ($pay['py_pg']) {
                    case 'tosspayments':
                        include_once(G5_SUBSCRIPTION_PATH . '/settle_tosspayments.inc.php');

                        break;
                    case 'inicis':
                        include_once(G5_SUBSCRIPTION_PATH . '/settle_inicis.inc.php');
                        $cancel_msg = '쇼핑몰 운영자 승인 취소';

                        $args = array(
                            'tid' => $pay['py_tno'],
                            'msg' => $cancel_msg
                        );

                        $response = subscription_inicis_tid_cancel($args);
                        $result = json_decode($response, true);

                        if (isset($result['resultCode'])) {
                            if ((string) $result['resultCode'] !== '00') {
                                $pg_res_cd = $result['resultCode'];
                                $pg_res_msg = $result['resultMsg'];
                            }
                        } else {
                            $pg_res_cd = 'noresponse';
                            $pg_res_msg = 'curl 로 데이터를 받지 못했습니다.';
                        }

                        break;
                    case 'nicepay':
                        include_once(G5_SUBSCRIPTION_PATH . '/settle_nicepay.inc.php');
                        $cancel_msg = '쇼핑몰 운영자 승인 취소';

                        $tno = $pay['py_tno'];

                        $cancelAmt = (int) $pay['py_receipt_price'];

                        // 0:전체 취소, 1:부분 취소(별도 계약 필요)
                        $partialCancelCode = 0;

                        include G5_SUBSCRIPTION_PATH . '/nicepay/nicepay_cancel_process.php';

                        if (isset($result['ResultCode'])) {
                            // 실패했다면
                            if ($result['ResultCode'] !== '2001') {
                                $pg_res_cd = $result['ResultCode'];
                                $pg_res_msg = $result['ResultMsg'];
                            }
                        } else {
                            $pg_res_cd = 'noresponse';
                            $pg_res_msg = 'curl 로 데이터를 받지 못하거나 통신에 실패했습니다.';
                        }

                        break;

                    case 'kcp':
                    default:
                        include_once(G5_SUBSCRIPTION_PATH . '/settle_kcp.inc.php');
                        require_once(G5_SUBSCRIPTION_PATH . '/kcp/pp_ax_hub_lib.php');

                        // locale ko_KR.euc-kr 로 설정
                        setlocale(LC_CTYPE, 'ko_KR.euc-kr');

                        $c_PayPlus = new C_PP_CLI_T;

                        $c_PayPlus->mf_clear();

                        $ordr_idxx = $pay['subscription_pg_id'];
                        $tno = $pay['py_tno'];
                        $tran_cd = '00200000';
                        $cancel_msg = iconv_euckr('쇼핑몰 운영자 승인 취소');
                        $cust_ip = $_SERVER['REMOTE_ADDR'];
                        $bSucc_mod_type = "STSC";

                        $c_PayPlus->mf_set_modx_data("tno",      $tno);  // KCP 원거래 거래번호
                        $c_PayPlus->mf_set_modx_data("mod_type", $bSucc_mod_type);  // 원거래 변경 요청 종류
                        $c_PayPlus->mf_set_modx_data("mod_ip",   $cust_ip);  // 변경 요청자 IP
                        $c_PayPlus->mf_set_modx_data("mod_desc", $cancel_msg);  // 변경 사유

                        $c_PayPlus->mf_do_tx(
                            $tno,
                            $g_conf_home_dir,
                            $g_conf_site_cd,
                            $g_conf_site_key,
                            $tran_cd,
                            "",
                            $g_conf_gw_url,
                            $g_conf_gw_port,
                            "payplus_cli_slib",
                            $ordr_idxx,
                            $cust_ip,
                            "3",
                            0,
                            0,
                            $g_conf_key_dir,
                            $g_conf_log_dir
                        );

                        $res_cd  = $c_PayPlus->m_res_cd;
                        $res_msg = $c_PayPlus->m_res_msg;

                        if ($res_cd != '0000') {
                            $pg_res_cd = $res_cd;
                            $pg_res_msg = iconv_utf8($res_msg);
                        }

                        // locale 설정 초기화
                        setlocale(LC_CTYPE, '');
                        break;
                }

                // PG 취소요청 성공했으면
                if ($pg_res_cd == '') {
                    $pg_cancel_log = ' PG ' . $pay['py_settle_case'] . ' 승인취소 처리';
                    $sql = " update {$g5['g5_subscription_pay_table']}
                                set py_refund_price = '{$pay['py_receipt_price']}'
                                where pay_id = '$pay_id' ";
                    sql_query($sql);
                }
            }
        }

        // 관리자 주문취소 로그
        $mod_history .= G5_TIME_YMDHIS . ' ' . $member['mb_id'] . ' 주문' . $_POST['pb_status'] . ' 처리' . $pg_cancel_log . "\n";
    }
}

// 미수금 등의 정보
$info = get_subscription_pay_info($pay_id, $od_id);

if (!(isset($info['py_cart_price']) && $info)) {
    alert('주문자료가 존재하지 않습니다.');
}

$sql = "UPDATE {$g5['g5_subscription_pay_table']} 
        SET py_cart_price = '" . $info['py_cart_price'] . "', 
            py_cart_coupon = '" . $info['py_cart_coupon'] . "', 
            py_coupon = '" . $info['py_coupon'] . "', 
            py_send_coupon = '" . $info['py_send_coupon'] . "', 
            py_cancel_price = '" . $info['py_cancel_price'] . "', 
            py_send_cost = '" . $info['py_send_cost'] . "', 
            py_misu = '" . $info['py_misu'] . "', 
            py_tax_mny = '" . $info['py_tax_mny'] . "', 
            py_vat_mny = '" . $info['py_vat_mny'] . "', 
            py_free_mny = '" . $info['py_free_mny'] . "'";

if ($mod_history) {
    $sql .= ", py_mod_history = CONCAT(py_mod_history, '$mod_history')";
}

if ($cancel_change) {
    $sql .= ", py_status = '취소'";
} else {
    if (isset($_POST['pb_status']) && in_array($_POST['pb_status'], $status_normal)) {
        $sql .= ", py_status = '" . $_POST['pb_status'] . "'";
    }
}

$sql .= " WHERE od_id = '$od_id' 
          AND pay_id = '$pay_id'";

$result = sql_query($sql);

$qstr = "od_id=$od_id&amp;sort1=$sort1&amp;sort2=$sort2&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page";

$url = "./payform.php?pay_id=$pay_id&amp;$qstr";

// 신용카드 취소 때 오류가 있으면 알림
if ($pg_cancel == 1 && $pg_res_cd && $pg_res_msg) {
    alert('오류코드 : ' . $pg_res_cd . ' 오류내용 : ' . $pg_res_msg, $url);
} else {

    $sql = "SELECT py_receipt_point 
            FROM {$g5['g5_subscription_pay_table']} 
            WHERE od_id = '$od_id' 
            AND pay_id = '$pay_id'";
    $pay = sql_fetch($sql);

    if ($pay['py_receipt_point']) {
        alert("포인트로 결제한 주문은,\\n\\n주문상태 변경으로 인해 포인트의 가감이 발생하는 경우\\n\\n회원관리 > 포인트관리에서 수작업으로 포인트를 맞추어 주셔야 합니다.", $url);
    } else {
        goto_url($url);
    }
}
