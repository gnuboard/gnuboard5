<?php
include_once './_common.php';
include_once G5_LIB_PATH.'/mailer.lib.php';

// print_r2($_POST);
// exit;

if (function_exists('add_order_post_log')) {
    add_order_post_log('init', 'init');
}

// 장바구니가 비어있는가?
if (get_session('subs_direct')) {
    $tmp_cart_id = get_session('subs_cart_direct');
} else {
    $tmp_cart_id = get_session('subs_cart_id');
}

if (get_subscription_cart_count($tmp_cart_id) == 0) {    // 장바구니에 담기
    if (function_exists('add_order_post_log')) {
        add_order_post_log('장바구니가 비어 있습니다.');
    }

    alert('장바구니가 비어 있습니다.\\n\\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.', G5_SUBSCRIPTION_URL.'/cart.php');
}

$sql = "select * from {$g5['g5_subscription_order_table']} limit 1";
$check_tmp = sql_fetch($sql);

if(!isset($check_tmp['od_subscription_date_format'])){
    $sql = "ALTER TABLE `{$g5['g5_subscription_order_table']}` 
            ADD COLUMN `od_subscription_date_format` CHAR(4) NOT NULL DEFAULT '',
            ADD COLUMN `od_subscription_number` tinyint(4) NOT NULL DEFAULT '0',
            ADD COLUMN `od_firstshipment_date` datetime DEFAULT NULL; ";
    sql_query($sql, false);
}

// 변수 초기화
$card_number = '';
$card_billkey = '';
$od_other_pay_type = '';

$od_hope_date = isset($_POST['od_hope_date']) ? clean_xss_tags($_POST['od_hope_date'], 1, 1) : '';
$ad_default = !empty($_POST['ad_default']) ? (int) $_POST['ad_default'] : 0;

$error = '';

// 정기결제 관련
$od_subscription_number = '';
$od_firstshipment_date = '';
$od_subscription_date_format = '';

// 장바구니 상품 재고 검사
$sql = " select it_id,
                ct_qty,
                it_name,
                io_id,
                io_type,
                ct_option,
                ct_subscription_number,
                ct_firstshipment_date,
                ct_date_format
           from {$g5['g5_subscription_cart_table']}
          where od_id = '$tmp_cart_id'
            and ct_select = '1' ";
$result = sql_query($sql);
for ($i = 0; $row = sql_fetch_array($result); ++$i) {
    // 상품에 대한 현재고수량
    if ($row['io_id']) {
        $it_stock_qty = (int) get_subscription_option_stock_qty($row['it_id'], $row['io_id'], $row['io_type']);
    } else {
        $it_stock_qty = (int) get_subscription_it_stock_qty($row['it_id']);
    }
    // 장바구니 수량이 재고수량보다 많다면 오류
    if ($row['ct_qty'] > $it_stock_qty) {
        $error .= "{$row['ct_option']} 의 재고수량이 부족합니다. 현재고수량 : $it_stock_qty 개\\n\\n";
    }
    
    // 정기결제 관련
    $od_subscription_number = $row['ct_subscription_number'];
    $od_firstshipment_date = $row['ct_firstshipment_date'];
    $od_subscription_date_format = $row['ct_date_format'];

}

if ($i == 0) {
    if (function_exists('add_order_post_log')) {
        add_order_post_log('장바구니가 비어 있습니다.');
    }

    alert('장바구니가 비어 있습니다.\\n\\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.', G5_SUBSCRIPTION_URL.'/cart.php');
}

if ($error != '') {
    $error .= "다른 고객님께서 {$od_name}님 보다 먼저 주문하신 경우입니다. 불편을 끼쳐 죄송합니다.";
    if (function_exists('add_order_post_log')) {
        add_order_post_log($error);
    }
    alert($error);
}

$i_price = isset($_POST['od_price']) ? (int) $_POST['od_price'] : 0;
$i_send_cost = isset($_POST['od_send_cost']) ? (int) $_POST['od_send_cost'] : 0;
$i_send_cost2 = isset($_POST['od_send_cost2']) ? (int) $_POST['od_send_cost2'] : 0;
$i_send_coupon = isset($_POST['od_send_coupon']) ? abs((int) $_POST['od_send_coupon']) : 0;
$i_temp_point = isset($_POST['od_temp_point']) ? (int) $_POST['od_temp_point'] : 0;

// 주문금액이 상이함
$sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as od_price,
              COUNT(distinct it_id) as cart_count
            from {$g5['g5_subscription_cart_table']} where od_id = '$tmp_cart_id' and ct_select = '1' ";
$row = sql_fetch($sql);
$tot_ct_price = $row['od_price'];
$cart_count = $row['cart_count'];
$tot_od_price = $tot_ct_price;

// 쿠폰과 배송비 변수
$tot_it_cp_price = 0;
$tot_od_cp_price = 0;
$tot_sc_cp_price = 0;

// 배송비가 상이함
$send_cost = get_sendcost($tmp_cart_id);

$tot_sc_cp_price = 0;
// 배송비 쿠폰 적용해야 해야함

$send_cost2 = 0;


// 추가배송비가 상이함
$od_b_zip   = preg_replace('/[^0-9]/', '', $od_b_zip);
$od_b_zip1  = substr($od_b_zip, 0, 3);
$od_b_zip2  = substr($od_b_zip, 3);
$zipcode = $od_b_zip;

$i_price = $i_price + $i_send_cost + $i_send_cost2 - $i_send_coupon;
$order_price = $tot_od_price + $send_cost + $send_cost2 - $tot_sc_cp_price;

$od_status = '주문';
$od_tno = '';

if (function_exists('check_payment_method')) {
    check_payment_method($od_settle_case);
}

// 변수 초기화
$od_card_name = '';

$pg_receipt_infos = array(
'od_cash'=>'',
'od_cash_no'=>'',
'od_cash_info'=>''
);

if ($od_settle_case == '무통장') {
    $od_receipt_point = $i_temp_point;
    $od_receipt_price = 0;
    $od_misu = $i_price - $od_receipt_price;
    if ($od_misu == 0) {
        $od_status = '입금';
    }
} elseif ($od_settle_case == '신용카드') {
    switch (get_subs_option('su_pg_service')) {
        case 'inicis':
            include G5_SUBSCRIPTION_PATH.'/inicis/inicis_bill_result.php';
            break;
        case 'nicepay':
            include G5_SUBSCRIPTION_PATH.'/nicepay/nicepay_subscription_result.php';
            break;
        case 'kcp':
        default:
            include G5_SUBSCRIPTION_PATH.'/kcp/kcp_api_batch_key_req.php';
            break;
    }

    $od_tno = $tno;
    $od_receipt_price = $amount;
    $od_receipt_point = $i_temp_point;
    // $od_receipt_time = preg_replace('/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/', '\\1-\\2-\\3 \\4:\\5:\\6', $app_time);
    $od_card_name = $card_name;
    $pg_price = $amount;
    $od_misu = $i_price - $od_receipt_price;
    if ($od_misu == 0) {
        $od_status = '입금';
    }
} else {
    exit('od_settle_case Error!!!');
}

$od_pg = get_subs_option('su_pg_service');

$tno = isset($tno) ? $tno : '';

// 주문금액과 결제금액이 일치하는지 체크
if ($tno) {
    if ((int) $order_price !== (int) $pg_price) {
        $cancel_msg = '결제금액 불일치';
        switch ($od_pg) {
            case 'inicis':
                include G5_SUBSCRIPTION_PATH.'/inicis/inipay_cancel.php';
                break;
            case 'nicepay':
                $cancelAmt = (int) $pg_price;
                include G5_SUBSCRIPTION_PATH.'/nicepay/cancel_process.php';
                break;
            case 'kcp':
            default:
                include G5_SUBSCRIPTION_PATH.'/kcp/pp_ax_hub_cancel.php';
                break;
        }

        if (function_exists('add_order_post_log')) {
            add_order_post_log($cancel_msg);
        }
        exit('Receipt Amount Error');
    }
}

// 복합과세 금액
$od_tax_mny = round($i_price / 1.1);
$od_vat_mny = $i_price - $od_tax_mny;
$od_free_mny = 0;

if (get_subs_option('su_tax_flag_use')) {
    $od_tax_mny = isset($_POST['comm_tax_mny']) ? (int) $_POST['comm_tax_mny'] : 0;
    $od_vat_mny = isset($_POST['comm_vat_mny']) ? (int) $_POST['comm_vat_mny'] : 0;
    $od_free_mny = isset($_POST['comm_free_mny']) ? (int) $_POST['comm_free_mny'] : 0;
}

// 주문번호를 얻는다.
$od_id = get_session('subs_order_id');

$od_email = get_email_address($od_email);
$od_name = clean_xss_tags($od_name);
$od_tel = clean_xss_tags($od_tel);
$od_hp = clean_xss_tags($od_hp);
$od_zip = preg_replace('/[^0-9]/', '', $od_zip);
$od_zip1 = substr($od_zip, 0, 3);
$od_zip2 = substr($od_zip, 3);
$od_addr1 = clean_xss_tags($od_addr1);
$od_addr2 = clean_xss_tags($od_addr2);
$od_addr3 = clean_xss_tags($od_addr3);
$od_addr_jibeon = preg_match('/^(N|R)$/', $od_addr_jibeon) ? $od_addr_jibeon : '';
$od_b_name = clean_xss_tags($od_b_name);
$od_b_tel = clean_xss_tags($od_b_tel);
$od_b_hp = clean_xss_tags($od_b_hp);
$od_b_addr1 = clean_xss_tags($od_b_addr1);
$od_b_addr2 = clean_xss_tags($od_b_addr2);
$od_b_addr3 = clean_xss_tags($od_b_addr3);
$od_b_addr_jibeon = preg_match('/^(N|R)$/', $od_b_addr_jibeon) ? $od_b_addr_jibeon : '';
$od_memo = clean_xss_tags($od_memo, 1, 1, 0, 0);
// $od_deposit_name = clean_xss_tags($od_deposit_name);     // 정기결제에서 사용안함
$od_tax_flag      = get_subs_option('su_tax_flag_use');

$inserts = array(
    'od_id' => $od_id,
    'mb_id' => $member['mb_id'],
    'od_name' => $od_name,
    'od_email' => $od_email,
    'od_tel' => $od_tel,
    'od_hp' => $od_hp,
    'od_zip1' => $od_zip1,
    'od_zip2' => $od_zip2,
    'od_addr1' => $od_addr1,
    'od_addr2' => $od_addr2,
    'od_addr3' => $od_addr3,
    'od_addr_jibeon' => $od_addr_jibeon,
    'od_b_name' => $od_b_name,
    'od_b_tel' => $od_b_tel,
    'od_b_hp' => $od_b_hp,
    'od_b_zip1' => $od_b_zip1,
    'od_b_zip2' => $od_b_zip2,
    'od_b_addr1' => $od_b_addr1,
    'od_b_addr2' => $od_b_addr2,
    'od_b_addr3' => $od_b_addr3,
    'od_b_addr_jibeon' => $od_b_addr_jibeon,
    // 'od_deposit_name' => $od_deposit_name,
    'od_memo' => $od_memo,
    'od_cart_count' => $cart_count,
    'od_cart_price' => $tot_ct_price,
    'od_cart_coupon' => $tot_it_cp_price,
    'od_send_cost' => $od_send_cost,
    'od_send_coupon' => $tot_sc_cp_price,
    'od_send_cost2' => $od_send_cost2,
    'od_coupon' => $tot_od_cp_price,
    'od_receipt_price' => $od_receipt_price,
    'od_receipt_point' => $od_receipt_point,
    'od_card_name' => $od_card_name,
    'od_pg' => $od_pg,
    'od_tno' => $od_tno,
    'od_tax_flag' => $od_tax_flag,
    'od_tax_mny' => $od_tax_mny,
    'od_vat_mny' => $od_vat_mny,
    'od_free_mny' => $od_free_mny,
    'od_subscription_memo' => '',
    'od_hope_date' => $od_hope_date,
    'od_time' => G5_TIME_YMDHIS,
    'od_ip' => $_SERVER['REMOTE_ADDR'],
    'od_settle_case' => $od_settle_case,
    'od_other_pay_type' => $od_other_pay_type,
    'od_test' => get_subs_option('su_card_use'),
    'card_number' => $card_number,
    'card_billkey' => $card_billkey,
    'od_subscription_number' => $od_subscription_number,
    'od_firstshipment_date' => $od_firstshipment_date,
    'od_subscription_date_format' => $od_subscription_date_format,
);

// https://stackoverflow.com/questions/10054633/insert-array-into-mysql-database-with-php
$columns = implode(', ', array_keys($inserts));
$values = implode("', '", array_values($inserts));

// 주문서에 입력
$sql = "INSERT INTO `{$g5['g5_subscription_order_table']}`($columns) VALUES ('$values')";

// echo $sql;
// exit;

// // 주문서에 입력
// $sql = " insert {$g5['g5_subscription_order_table']}
//             set od_id             = '$od_id',
//                 mb_id             = '{$member['mb_id']}',
//                 od_name           = '$od_name',
//                 od_email          = '$od_email',
//                 od_tel            = '$od_tel',
//                 od_hp             = '$od_hp',
//                 od_zip1           = '$od_zip1',
//                 od_zip2           = '$od_zip2',
//                 od_addr1          = '$od_addr1',
//                 od_addr2          = '$od_addr2',
//                 od_addr3          = '$od_addr3',
//                 od_addr_jibeon    = '$od_addr_jibeon',
//                 od_b_name         = '$od_b_name',
//                 od_b_tel          = '$od_b_tel',
//                 od_b_hp           = '$od_b_hp',
//                 od_b_zip1         = '$od_b_zip1',
//                 od_b_zip2         = '$od_b_zip2',
//                 od_b_addr1        = '$od_b_addr1',
//                 od_b_addr2        = '$od_b_addr2',
//                 od_b_addr3        = '$od_b_addr3',
//                 od_b_addr_jibeon  = '$od_b_addr_jibeon',
//                 od_deposit_name   = '$od_deposit_name',
//                 od_memo           = '$od_memo',
//                 od_cart_count     = '$cart_count',
//                 od_cart_price     = '$tot_ct_price',
//                 od_cart_coupon    = '$tot_it_cp_price',
//                 od_send_cost      = '$od_send_cost',
//                 od_send_coupon    = '$tot_sc_cp_price',
//                 od_send_cost2     = '$od_send_cost2',
//                 od_coupon         = '$tot_od_cp_price',
//                 od_receipt_price  = '$od_receipt_price',
//                 od_receipt_point  = '$od_receipt_point',
//                 od_card_name   = '$od_card_name',
//                 od_pg             = '$od_pg',
//                 od_tno            = '$od_tno',
//                 od_tax_flag       = '$od_tax_flag',
//                 od_tax_mny        = '$od_tax_mny',
//                 od_vat_mny        = '$od_vat_mny',
//                 od_free_mny       = '$od_free_mny',
//                 od_SUBSCRIPTION_memo      = '',
//                 od_hope_date      = '$od_hope_date',
//                 od_time           = '".G5_TIME_YMDHIS."',
//                 od_ip             = '$REMOTE_ADDR',
//                 od_settle_case    = '$od_settle_case',
//                 od_other_pay_type = '$od_other_pay_type',
//                 od_cash           = '{$pg_receipt_infos['od_cash']}',
//                 od_cash_no        = '{$pg_receipt_infos['od_cash_no']}',
//                 od_cash_info      = '{$pg_receipt_infos['od_cash_info']}',
//                 od_test           = '{$default['de_card_test']}'
//                 ";
$result = sql_query($sql, false);

// 정말로 insert 가 되었는지 한번더 체크한다.
$exists_sql = "select * from {$g5['g5_subscription_order_table']} where od_id = '$od_id'";
$exists_order = sql_fetch($exists_sql);

$pays = subscription_process_payment($exists_order);

// 정기결제가 성공이면
if (isset($pays['code']) && $pays['code'] === 'success') {
    
    //subscription_order_pay($pays);
    
} else {
    // 실패시 처리
}

// 주문정보 입력 오류시 결제 취소
// if (!$result || !(isset($exists_order['od_id']) && $od_id && $exists_order['od_id'] === $od_id)) {
//     if ($tno) {
//         $cancel_msg = '주문정보 입력 오류 : '.$sql;
//         switch ($od_pg) {
//             case 'lg':
//                 include G5_SUBSCRIPTION_PATH.'/lg/xpay_cancel.php';
//                 break;
//             case 'inicis':
//                 include G5_SUBSCRIPTION_PATH.'/inicis/inipay_cancel.php';
//                 break;
//             case 'KAKAOPAY':
//                 $_REQUEST['TID'] = $tno;
//                 $_REQUEST['Amt'] = $amount;
//                 $_REQUEST['CancelMsg'] = $cancel_msg;
//                 $_REQUEST['PartialCancelCode'] = 0;
//                 include G5_SUBSCRIPTION_PATH.'/kakaopay/kakaopay_cancel.php';
//                 break;
//             default:
//                 include G5_SUBSCRIPTION_PATH.'/kcp/pp_ax_hub_cancel.php';
//                 break;
//         }
//     }

//     // 관리자에게 오류 알림 메일발송
//     $error = 'order';
//     include G5_SUBSCRIPTION_PATH.'/ordererrormail.php';

//     if (function_exists('add_order_post_log')) {
//         add_order_post_log($cancel_msg);
//     }
//     exit('<p>고객님의 주문 정보를 처리하는 중 오류가 발생해서 주문이 완료되지 않았습니다.</p><p>'.strtoupper($od_pg).'를 이용한 전자결제(신용카드, 계좌이체, 가상계좌 등)은 자동 취소되었습니다.');
// }

// 장바구니 상태변경
// 신용카드로 주문하면서 신용카드 포인트 사용하지 않는다면 포인트 부여하지 않음
$cart_status = $od_status;
$sql_card_point = '';
if ($od_receipt_price > 0 && !$default['de_card_point']) {
    $sql_card_point = " , ct_point = '0' ";
}

// 회원 아이디 값 변경
$sql_mb_id = '';
if ($is_member) {
    $sql_mb_id = " , mb_id = '{$member['mb_id']}' ";
}

$sql = "update {$g5['g5_subscription_cart_table']}
           set od_id = '$od_id',
               ct_status = '$cart_status'
               $sql_card_point
               $sql_mb_id
         where od_id = '$tmp_cart_id'
           and ct_select = '1' ";
$result = sql_query($sql, false);

// 주문정보 입력 오류시 결제 취소
// if (!$result) {
//     if ($tno) {
//         $cancel_msg = '주문상태 변경 오류';
//         switch ($od_pg) {
//             case 'lg':
//                 include G5_SUBSCRIPTION_PATH.'/lg/xpay_cancel.php';
//                 break;
//             case 'inicis':
//                 include G5_SUBSCRIPTION_PATH.'/inicis/inipay_cancel.php';
//                 break;
//             case 'KAKAOPAY':
//                 $_REQUEST['TID'] = $tno;
//                 $_REQUEST['Amt'] = $amount;
//                 $_REQUEST['CancelMsg'] = $cancel_msg;
//                 $_REQUEST['PartialCancelCode'] = 0;
//                 include G5_SUBSCRIPTION_PATH.'/kakaopay/kakaopay_cancel.php';
//                 break;
//             default:
//                 include G5_SUBSCRIPTION_PATH.'/kcp/pp_ax_hub_cancel.php';
//                 break;
//         }
//     }

//     // 관리자에게 오류 알림 메일발송
//     $error = 'status';
//     include G5_SUBSCRIPTION_PATH.'/ordererrormail.php';

//     if (function_exists('add_order_post_log')) {
//         add_order_post_log($cancel_msg);
//     }
//     // 주문삭제
//     sql_query(" delete from {$g5['g5_SUBSCRIPTION_order_table']} where od_id = '$od_id' ");

//     exit('<p>고객님의 주문 정보를 처리하는 중 오류가 발생해서 주문이 완료되지 않았습니다.</p><p>'.strtoupper($od_pg).'를 이용한 전자결제(신용카드, 계좌이체, 가상계좌 등)은 자동 취소되었습니다.');
// }

// 회원이면서 포인트를 사용했다면 테이블에 사용을 추가
// if ($is_member && $od_receipt_point) {
//     insert_point($member['mb_id'], (-1) * $od_receipt_point, "주문번호 $od_id 결제");
// }

$od_memo = nl2br(htmlspecialchars2(stripslashes($od_memo))).'&nbsp;';

include_once G5_SUBSCRIPTION_PATH.'/ordermail1.inc.php';
include_once G5_SUBSCRIPTION_PATH.'/ordermail2.inc.php';

// SMS BEGIN --------------------------------------------------------
// 주문고객과 쇼핑몰관리자에게 SMS 전송
// if ($config['cf_sms_use'] && ($default['de_sms_use2'] || $default['de_sms_use3'])) {
//     $is_sms_send = (function_exists('is_sms_send')) ? is_sms_send('orderformupdate') : false;

//     if ($is_sms_send) {
//         $sms_contents = [$default['de_sms_cont2'], $default['de_sms_cont3']];
//         $recv_numbers = [$od_hp, $default['de_sms_hp']];
//         $send_numbers = [$default['de_admin_company_tel'], $default['de_admin_company_tel']];

//         $sms_count = 0;
//         $sms_messages = [];

//         for ($s = 0; $s < count($sms_contents); ++$s) {
//             $sms_content = $sms_contents[$s];
//             $recv_number = preg_replace('/[^0-9]/', '', $recv_numbers[$s]);
//             $send_number = preg_replace('/[^0-9]/', '', $send_numbers[$s]);

//             $sms_content = str_replace('{이름}', $od_name, $sms_content);
//             $sms_content = str_replace('{보낸분}', $od_name, $sms_content);
//             $sms_content = str_replace('{받는분}', $od_b_name, $sms_content);
//             $sms_content = str_replace('{주문번호}', $od_id, $sms_content);
//             $sms_content = str_replace('{주문금액}', number_format($tot_ct_price + $od_send_cost + (int) $od_send_cost2), $sms_content);
//             $sms_content = str_replace('{회원아이디}', $member['mb_id'], $sms_content);
//             $sms_content = str_replace('{회사명}', $default['de_admin_company_name'], $sms_content);

//             $idx = 'de_sms_use'.($s + 2);

//             if ($default[$idx] && $recv_number) {
//                 $sms_messages[] = ['recv' => $recv_number, 'send' => $send_number, 'cont' => $sms_content];
//                 ++$sms_count;
//             }
//         }

//         // 무통장 입금 때 고객에게 계좌정보 보냄
//         if ($od_settle_case == '무통장' && $default['de_sms_use2'] && $od_misu > 0) {
//             $sms_content = $od_name."님의 입금계좌입니다.\n금액:".number_format($od_misu)."원\n계좌:".$od_card_name."\n".$default['de_admin_company_name'];

//             $recv_number = preg_replace('/[^0-9]/', '', $od_hp);
//             $send_number = preg_replace('/[^0-9]/', '', $default['de_admin_company_tel']);

//             $sms_messages[] = ['recv' => $recv_number, 'send' => $send_number, 'cont' => $sms_content];
//             ++$sms_count;
//         }

//         // SMS 전송
//         if ($sms_count > 0) {
//             if ($config['cf_sms_type'] == 'LMS') {
//                 include_once G5_LIB_PATH.'/icode.lms.lib.php';

//                 $port_setting = get_icode_port_type($config['cf_icode_id'], $config['cf_icode_pw']);

//                 // SMS 모듈 클래스 생성
//                 if ($port_setting !== false) {
//                     $SMS = new LMS();
//                     $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $port_setting);

//                     for ($s = 0; $s < count($sms_messages); ++$s) {
//                         $strDest = [];
//                         $strDest[] = $sms_messages[$s]['recv'];
//                         $strCallBack = $sms_messages[$s]['send'];
//                         $strCaller = iconv_euckr(trim($default['de_admin_company_name']));
//                         $strSubject = '';
//                         $strURL = '';
//                         $strData = iconv_euckr($sms_messages[$s]['cont']);
//                         $strDate = '';
//                         $nCount = count($strDest);

//                         $res = $SMS->Add($strDest, $strCallBack, $strCaller, $strSubject, $strURL, $strData, $strDate, $nCount);

//                         $SMS->Send();
//                         $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
//                     }
//                 }
//             } else {
//                 include_once G5_LIB_PATH.'/icode.sms.lib.php';

//                 $SMS = new SMS(); // SMS 연결
//                 $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);

//                 for ($s = 0; $s < count($sms_messages); ++$s) {
//                     $recv_number = $sms_messages[$s]['recv'];
//                     $send_number = $sms_messages[$s]['send'];
//                     $sms_content = iconv_euckr($sms_messages[$s]['cont']);

//                     $SMS->Add($recv_number, $send_number, $config['cf_icode_id'], $sms_content, '');
//                 }

//                 $SMS->Send();
//                 $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
//             }
//         }
//     }
// }
// SMS END   --------------------------------------------------------

// orderview 에서 사용하기 위해 session에 넣고
$uid = md5($od_id.G5_TIME_YMDHIS.$_SERVER['REMOTE_ADDR']);
set_session('subs_orderview_uid', $uid);

// // 주문 정보 임시 데이터 삭제
// if ($od_pg == 'inicis') {
//     $sql = " delete from {$g5['g5_SUBSCRIPTION_order_data_table']} where od_id = '$od_id' and dt_pg = '$od_pg' ";
//     sql_query($sql);
// }

if (function_exists('add_order_post_log')) {
    add_order_post_log('', 'delete');
}

// 주문번호제거
set_session('subs_order_id', '');

// 기존자료 세션에서 제거
if (get_session('subs_direct')) {
    set_session('subs_cart_direct', '');
}

// 배송지처리
if ($is_member) {
    $sql = " select * from {$g5['g5_shop_order_address_table']}
                where mb_id = '{$member['mb_id']}'
                  and ad_name = '$od_b_name'
                  and ad_tel = '$od_b_tel'
                  and ad_hp = '$od_b_hp'
                  and ad_zip1 = '$od_b_zip1'
                  and ad_zip2 = '$od_b_zip2'
                  and ad_addr1 = '$od_b_addr1'
                  and ad_addr2 = '$od_b_addr2'
                  and ad_addr3 = '$od_b_addr3' ";
    $row = sql_fetch($sql);

    // 기본배송지 체크
    if ($ad_default) {
        $sql = " update {$g5['g5_shop_order_address_table']}
                    set ad_default = '0'
                    where mb_id = '{$member['mb_id']}' ";
        sql_query($sql);
    }

    $ad_subject = isset($_POST['ad_subject']) ? clean_xss_tags($_POST['ad_subject']) : '';

    if (isset($row['ad_id']) && $row['ad_id']) {
        $sql = " update {$g5['g5_shop_order_address_table']}
                      set ad_default = '$ad_default',
                          ad_subject = '$ad_subject',
                          ad_jibeon  = '$od_b_addr_jibeon'
                    where mb_id = '{$member['mb_id']}'
                      and ad_id = '{$row['ad_id']}' ";
    } else {
        $sql = " insert into {$g5['g5_shop_order_address_table']}
                    set mb_id       = '{$member['mb_id']}',
                        ad_subject  = '$ad_subject',
                        ad_default  = '$ad_default',
                        ad_name     = '$od_b_name',
                        ad_tel      = '$od_b_tel',
                        ad_hp       = '$od_b_hp',
                        ad_zip1     = '$od_b_zip1',
                        ad_zip2     = '$od_b_zip2',
                        ad_addr1    = '$od_b_addr1',
                        ad_addr2    = '$od_b_addr2',
                        ad_addr3    = '$od_b_addr3',
                        ad_jibeon   = '$od_b_addr_jibeon' ";
    }

    sql_query($sql);
}

goto_url(G5_SUBSCRIPTION_URL.'/orderinquiryview.php?od_id='.$od_id.'&amp;uid='.$uid);
?>
<html>
    <head>
        <title>주문정보 기록</title>
        <script>
            // 결제 중 새로고침 방지 샘플 스크립트 (중복결제 방지)
            function noRefresh()
            {
                /* CTRL + N키 막음. */
                if ((event.keyCode == 78) && (event.ctrlKey == true))
                {
                    event.keyCode = 0;
                    return false;
                }
                /* F5 번키 막음. */
                if(event.keyCode == 116)
                {
                    event.keyCode = 0;
                    return false;
                }
            }

            document.onkeydown = noRefresh ;
        </script>
    </head>
</html>