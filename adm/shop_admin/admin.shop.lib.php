<?php
if (!defined('_GNUBOARD_')) exit;

// 상품옵션별재고 또는 상품재고에 더하기
function add_io_stock($it_id, $ct_qty, $io_id="", $io_type=0)
{
    global $g5;

    if($io_id) {
        $sql = " update {$g5['g5_shop_item_option_table']}
                    set io_stock_qty = io_stock_qty + '{$ct_qty}'
                    where it_id = '{$it_id}'
                      and io_id = '{$io_id}'
                      and io_type = '{$io_type}' ";
    } else {
        $sql = " update {$g5['g5_shop_item_table']}
                    set it_stock_qty = it_stock_qty + '{$ct_qty}'
                    where it_id = '{$it_id}' ";
    }
    return sql_query($sql);
}


// 상품옵션별재고 또는 상품재고에서 빼기
function subtract_io_stock($it_id, $ct_qty, $io_id="", $io_type=0)
{
    global $g5;

    if($io_id) {
        $sql = " update {$g5['g5_shop_item_option_table']}
                    set io_stock_qty = io_stock_qty - '{$ct_qty}'
                    where it_id = '{$it_id}'
                      and io_id = '{$io_id}'
                      and io_type = '{$io_type}' ";
    } else {
        $sql = " update {$g5['g5_shop_item_table']}
                    set it_stock_qty = it_stock_qty - '{$ct_qty}'
                    where it_id = '{$it_id}' ";
    }
    return sql_query($sql);
}


// 주문과 장바구니의 상태를 변경한다.
function change_status($od_id, $current_status, $change_status)
{
    global $g5;

    $sql = " update {$g5['g5_shop_order_table']} set od_status = '{$change_status}' where od_id = '{$od_id}' and od_status = '{$current_status}' ";
    sql_query($sql, true);

    $sql = " update {$g5['g5_shop_cart_table']} set ct_status = '{$change_status}' where od_id = '{$od_id}' and ct_status = '{$current_status}' ";
    sql_query($sql, true);
}


// 주문서에 입금시 update
function order_update_receipt($od_id)
{
    global $g5;

    $sql = " update {$g5['g5_shop_order_table']} set od_receipt_price = od_misu, od_misu = 0, od_receipt_time = '".G5_TIME_YMDHIS."' where od_id = '$od_id' and od_status = '입금' ";
    return sql_query($sql);
}


// 주문서에 배송시 update
function order_update_delivery($od_id, $mb_id, $change_status, $delivery)
{
    global $g5;

    if($change_status != '배송')
        return;

    $sql = " update {$g5['g5_shop_order_table']} set od_delivery_company = '{$delivery['delivery_company']}', od_invoice = '{$delivery['invoice']}', od_invoice_time = '{$delivery['invoice_time']}' where od_id = '$od_id' and od_status = '준비' ";
    sql_query($sql);

    $sql = " select * from {$g5['g5_shop_cart_table']} where od_id = '$od_id' ";
    $result = sql_query($sql);

    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        // 재고를 사용하지 않았다면
        $stock_use = $row['ct_stock_use'];

        if(!$row['ct_stock_use'])
        {
            // 재고에서 뺀다.
            subtract_io_stock($row['it_id'], $row['ct_qty'], $row['io_id'], $row['io_type']);
            $stock_use = 1;

            $sql = " update {$g5['g5_shop_cart_table']} set ct_stock_use  = '$stock_use' where ct_id = '{$row['ct_id']}' ";
            sql_query($sql);
        }
    }
}

// 처리내용 SMS
function conv_sms_contents($od_id, $contents)
{
    global $g5, $config, $default;

    $sms_contents = '';

    if ($od_id && $config['cf_sms_use'] == 'icode')
    {
        $sql = " select od_id, od_name, od_invoice, od_receipt_price, od_delivery_company
                    from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
        $od = sql_fetch($sql);

        $sms_contents = $contents;
        $sms_contents = str_replace("{이름}", $od['od_name'], $sms_contents);
        $sms_contents = str_replace("{입금액}", number_format($od['od_receipt_price']), $sms_contents);
        $sms_contents = str_replace("{택배회사}", $od['od_delivery_company'], $sms_contents);
        $sms_contents = str_replace("{운송장번호}", $od['od_invoice'], $sms_contents);
        $sms_contents = str_replace("{주문번호}", $od['od_id'], $sms_contents);
        $sms_contents = str_replace("{회사명}", $default['de_admin_company_name'], $sms_contents);
    }

    return stripslashes($sms_contents);
}

function check_order_inicis_tmps(){
    global $g5, $config, $default, $member;

    $admin_cookie_time = get_cookie('admin_visit_time');

    if( ! $admin_cookie_time ){

        if( $default['de_pg_service'] === 'inicis' && empty($default['de_card_test']) ){
            $sql = " select * from {$g5['g5_shop_inicis_log_table']} where P_TID <> '' and P_TYPE in ('CARD', 'ISP', 'BANK') and P_MID <> '' and P_STATUS = '00' and is_mail_send = 0 and substr(P_AUTH_DT, 1, 14) < '".date('YmdHis', strtotime('-3 minutes', G5_SERVER_TIME))."' ";

            $result = sql_query($sql, false);
            
            if( !$result ){
                return;
            }

            $mail_msg = '';

            for($i=0;$row=sql_fetch_array($result);$i++){
                
                $oid = $row['oid'];
                $p_tid = $row['P_TID'];
                $p_mid = strtolower($tmps['P_MID']);

                if( in_array($p_mid, array('iniescrow0', 'inipaytest')) ) continue;

                $sql = "update {$g5['g5_shop_inicis_log_table']} set is_mail_send = 1 where oid = '".$oid."' and P_TID = '".$p_tid."' ";
                sql_query($sql);

                $sql = " select od_id from {$g5['g5_shop_order_table']} where od_id = '$oid' and od_tno = '$p_tid' ";
                $tmp = sql_fetch($sql);

                if( $tmp['od_id'] ) continue;

                $sql = " select pp_id from {$g5['g5_shop_personalpay_table']} where pp_id = '$oid' and pp_tno = '$p_tid' ";
                $tmp = sql_fetch($sql);

                if( $tmp['pp_id'] ) continue;

                $mail_msg .= '<a href="'.G5_ADMIN_URL.'/shop_admin/inorderform.php?od_id='.$oid.'" target="_blank" >미완료 발생 주문번호 : '.$oid.'</a><br><br>';
                
            }
            
            if( $mail_msg ){
                include_once(G5_LIB_PATH.'/mailer.lib.php');

                $mails = array_unique(array($member['mb_email'], $config['cf_admin_email']));

                foreach($mails as $mail_address){
                    if (!preg_match("/([0-9a-zA-Z_-]+)@([0-9a-zA-Z_-]+)\.([0-9a-zA-Z_-]+)/", $mail_address)) continue;

                    mailer($member['mb_nick'], $member['mb_email'], $mail_address, $config['cf_title'].' 사이트 미완료 주문 알림', '이니시스를 통해 결제한 주문건 중에서 미완료 주문이 발생했습니다.<br><br>발생된 원인으로는 장바구니 금액와 실결제 금액이 맞지 않는 경우, 네트워크 오류, 프로그램 오류, 알수 없는 오류 등이 있습니다.<br><br>아래 내용과 실제 주문내역, 이니시스 상점 관리자 에서 결제된 내용을 확인하여 조치를 취해 주세요.<br><br>'.$mail_msg, 0);
                }
            }
        }

        if( $default['de_pg_service'] == 'lg' && function_exists('check_log_folder') ){
            check_log_folder(G5_LGXPAY_PATH.'/lgdacom/log');
        }

        set_cookie('admin_visit_time', G5_SERVER_TIME, 3600);   //1시간 간격으로 체크
    }
}   //end function check_order_inicis_tmps
?>