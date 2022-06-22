<?php
$sub_menu = '400410';
include_once('./_common.php');

check_demo();

if($w == 'd')
    auth_check_menu($auth, $sub_menu, "d");
else
    auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$od_id = isset($_REQUEST['od_id']) ? safe_replace_regex($_REQUEST['od_id'], 'od_id') : '';

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g5['g5_shop_order_data_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (! (isset($od['od_id']) && $od['od_id'])) {
    alert("해당 주문번호로 미완료 주문서가 존재하지 않습니다.");
}

if($w == 'd') {
    $sql = " delete from {$g5['g5_shop_order_data_table']} where od_id = '$od_id' ";
    sql_query($sql, true);
    goto_url("./inorderlist.php?$qstr");
}

// 주문정보
$data = unserialize(base64_decode($od['dt_data']));

$sql_common = " from {$g5['g5_shop_cart_table']} where od_id = '{$od['cart_id']}' and ct_status = '쇼핑' ";

// 주문금액
$sql = " select SUM(IF(io_type = 1, io_price, (ct_price + io_price)) * ct_qty) as od_price, COUNT(distinct it_id) as cart_count $sql_common ";
$row = sql_fetch($sql);
$tot_ct_price  = $row['od_price'];
$cart_count    = $row['cart_count'];
$tot_od_price  = $tot_ct_price;
$i_price       = isset($data['od_price']) ? (int) $data['od_price'] : 0;
$i_send_cost   = isset($data['od_send_cost']) ? (int) $data['od_send_cost'] : 0;
$i_send_cost2  = isset($data['od_send_cost2']) ? (int) $data['od_send_cost2'] : 0;
$i_send_coupon = isset($data['od_send_coupon']) ? (int) $data['od_send_coupon'] : 0;
$i_temp_point  = isset($data['od_temp_point']) ? (int) $data['od_temp_point'] : 0;

// 쿠폰금액
$tot_cp_price = 0;
if($od['mb_id']) {
    // 상품쿠폰
    $tot_it_cp_price = $tot_od_cp_price = 0;
    $it_cp_cnt = (isset($data['cp_id']) && is_array($data['cp_id'])) ? count($data['cp_id']) : 0;
    $arr_it_cp_prc = array();
    for($i=0; $i<$it_cp_cnt; $i++) {
        $cid = $data['cp_id'][$i];
        $it_id = $data['it_id'][$i];
        $sql = " select cp_id, cp_method, cp_target, cp_type, cp_price, cp_trunc, cp_minimum, cp_maximum
                    from {$g5['g5_shop_coupon_table']}
                    where cp_id = '$cid'
                      and mb_id IN ( '{$od['mb_id']}', '전체회원' )
                      and cp_method IN ( 0, 1 ) ";
        $cp = sql_fetch($sql);
        if(! (isset($cp['cp_id']) && $cp['cp_id']))
            continue;

        // 사용한 쿠폰인지
        if(is_used_coupon($od['mb_id'], $cp['cp_id']))
            continue;

        // 분류할인인지
        if($cp['cp_method']) {
            $sql2 = " select it_id, ca_id, ca_id2, ca_id3
                        from {$g5['g5_shop_item_table']}
                        where it_id = '$it_id' ";
            $row2 = sql_fetch($sql2);

            if(!$row2['it_id'])
                continue;

            if($row2['ca_id'] != $cp['cp_target'] && $row2['ca_id2'] != $cp['cp_target'] && $row2['ca_id3'] != $cp['cp_target'])
                continue;
        } else {
            if($cp['cp_target'] != $it_id)
                continue;
        }

        // 상품금액
        $sql = " select SUM( IF(io_type = '1', io_price * ct_qty, (ct_price + io_price) * ct_qty)) as sum_price $sql_common and it_id = '$it_id' ";
        $ct = sql_fetch($sql);
        $item_price = $ct['sum_price'];

        if($cp['cp_minimum'] > $item_price)
            continue;

        $dc = 0;
        if($cp['cp_type']) {
            $dc = floor(($item_price * ($cp['cp_price'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];
        } else {
            $dc = $cp['cp_price'];
        }

        if($cp['cp_maximum'] && $dc > $cp['cp_maximum'])
            $dc = $cp['cp_maximum'];

        if($item_price < $dc)
            continue;

        $tot_it_cp_price += $dc;
        $arr_it_cp_prc[$it_id] = $dc;
    }

    $tot_od_price -= $tot_it_cp_price;

    // 주문쿠폰
    if(isset($data['od_cp_id']) && $data['od_cp_id']) {
        $sql = " select cp_id, cp_type, cp_price, cp_trunc, cp_minimum, cp_maximum
                    from {$g5['g5_shop_coupon_table']}
                    where cp_id = '{$data['od_cp_id']}'
                      and mb_id IN ( '{$od['mb_id']}', '전체회원' )
                      and cp_method = '2' ";
        $cp = sql_fetch($sql);

        // 사용한 쿠폰인지
        $cp_used = is_used_coupon($od['mb_id'], $cp['cp_id']);

        $dc = 0;
        if(!$cp_used && $cp['cp_id'] && ($cp['cp_minimum'] <= $tot_od_price)) {
            if($cp['cp_type']) {
                $dc = floor(($tot_od_price * ($cp['cp_price'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];
            } else {
                $dc = $cp['cp_price'];
            }

            if($cp['cp_maximum'] && $dc > $cp['cp_maximum'])
                $dc = $cp['cp_maximum'];

            $tot_od_cp_price = $dc;
            $tot_od_price -= $tot_od_cp_price;
        }
    }

    $tot_cp_price = $tot_it_cp_price + $tot_od_cp_price;
}

// 배송비
$od_send_cost = get_sendcost($od['cart_id']);

$tot_sc_cp_price = 0;
if($od['mb_id'] && $od_send_cost > 0) {
    // 배송쿠폰
    if($data['sc_cp_id']) {
        $sql = " select cp_id, cp_type, cp_price, cp_trunc, cp_minimum, cp_maximum
                    from {$g5['g5_shop_coupon_table']}
                    where cp_id = '{$data['sc_cp_id']}'
                      and mb_id IN ( '{$od['mb_id']}', '전체회원' )
                      and cp_method = '3' ";
        $cp = sql_fetch($sql);

        // 사용한 쿠폰인지
        $cp_used = is_used_coupon($od['mb_id'], $cp['cp_id']);

        $dc = 0;
        if(!$cp_used && $cp['cp_id'] && ($cp['cp_minimum'] <= $tot_od_price)) {
            if($cp['cp_type']) {
                $dc = floor(($send_cost * ($cp['cp_price'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];
            } else {
                $dc = $cp['cp_price'];
            }

            if($cp['cp_maximum'] && $dc > $cp['cp_maximum'])
                $dc = $cp['cp_maximum'];

            if($dc > $send_cost)
                $dc = $send_cost;

            $tot_sc_cp_price = $dc;
        }
    }
}

// 추가배송비
$od_send_cost2 = isset($data['od_send_cost2']) ? (int) $data['od_send_cost2'] : 0;

// 포인트
$od_temp_point = isset($data['od_temp_point']) ? (int) $data['od_temp_point'] : 0;

$i_price     = $i_price + $i_send_cost + $i_send_cost2 - $i_temp_point - $i_send_coupon;
$order_price = $tot_od_price + $od_send_cost + $od_send_cost2 - $tot_sc_cp_price - $od_temp_point;

if ($od['mb_id']) {
    $mb = get_member($od['mb_id']);
    $od_pwd = $mb['mb_password'];
} else {
    $od_pwd = get_encrypt_string($data['od_pwd']);
}

$od_escrow = 0;

// 복합과세 금액
$od_tax_mny = round($i_price / 1.1);
$od_vat_mny = $i_price - $od_tax_mny;
$od_free_mny = 0;
if($default['de_tax_flag_use']) {
    $od_tax_mny  = (int)$data['comm_tax_mny'];
    $od_vat_mny  = (int)$data['comm_vat_mny'];
    $od_free_mny = (int)$data['comm_free_mny'];
}

$od_pg = $default['de_pg_service'];
if($data['od_settle_case'] == 'KAKAOPAY')
    $od_pg = 'KAKAOPAY';

$od_email         = get_email_address($data['od_email']);
$od_name          = clean_xss_tags($data['od_name']);
$od_tel           = clean_xss_tags($data['od_tel']);
$od_hp            = clean_xss_tags($data['od_hp']);
$od_zip           = preg_replace('/[^0-9]/', '', $data['od_zip']);
$od_zip1          = substr($od_zip, 0, 3);
$od_zip2          = substr($od_zip, 3);
$od_addr1         = clean_xss_tags($data['od_addr1']);
$od_addr2         = clean_xss_tags($data['od_addr2']);
$od_addr3         = clean_xss_tags($data['od_addr3']);
$od_addr_jibeon   = preg_match("/^(N|R)$/", $data['od_addr_jibeon']) ? $data['od_addr_jibeon'] : '';
$od_b_name        = clean_xss_tags($data['od_b_name']);
$od_b_tel         = clean_xss_tags($data['od_b_tel']);
$od_b_hp          = clean_xss_tags($data['od_b_hp']);
$od_b_zip		  = preg_replace('/[^0-9]/', '', $data['od_b_zip']);
$od_b_zip1        = substr($od_b_zip, 0, 3);
$od_b_zip2        = substr($od_b_zip, 3);
$od_b_addr1       = clean_xss_tags($data['od_b_addr1']);
$od_b_addr2       = clean_xss_tags($data['od_b_addr2']);
$od_b_addr3       = clean_xss_tags($data['od_b_addr3']);
$od_b_addr_jibeon = preg_match("/^(N|R)$/", $data['od_b_addr_jibeon']) ? $data['od_b_addr_jibeon'] : '';
$od_memo          = clean_xss_tags($data['od_memo'], 0, 1, 0, 0);
$od_deposit_name  = clean_xss_tags($data['od_deposit_name']);
$od_tax_flag      = $default['de_tax_flag_use'];
$od_receipt_price = $tot_ct_price + $od_send_cost + $od_send_cost2 - ($od_temp_point + $tot_cp_price + $tot_sc_cp_price);
$od_receipt_point = $od_temp_point;
$od_receipt_time  = $od['dt_time'];
$od_misu          = 0;
$od_status        = '입금';
$od_bank_account  = isset($data['od_bank_account']) ? clean_xss_tags($data['od_bank_account'], 1, 1) : '';
$od_tno = '';
$od_app_no = '';
$od_hope_date = isset($data['od_hope_date']) ? clean_xss_tags($data['od_hope_date'], 1, 1) : '';

// 주문서에 입력
$sql = " insert {$g5['g5_shop_order_table']}
            set od_id             = '$od_id',
                mb_id             = '{$od['mb_id']}',
                od_pwd            = '$od_pwd',
                od_name           = '$od_name',
                od_email          = '$od_email',
                od_tel            = '$od_tel',
                od_hp             = '$od_hp',
                od_zip1           = '$od_zip1',
                od_zip2           = '$od_zip2',
                od_addr1          = '$od_addr1',
                od_addr2          = '$od_addr2',
                od_addr3          = '$od_addr3',
                od_addr_jibeon    = '$od_addr_jibeon',
                od_b_name         = '$od_b_name',
                od_b_tel          = '$od_b_tel',
                od_b_hp           = '$od_b_hp',
                od_b_zip1         = '$od_b_zip1',
                od_b_zip2         = '$od_b_zip2',
                od_b_addr1        = '$od_b_addr1',
                od_b_addr2        = '$od_b_addr2',
                od_b_addr3        = '$od_b_addr3',
                od_b_addr_jibeon  = '$od_b_addr_jibeon',
                od_deposit_name   = '$od_deposit_name',
                od_memo           = '$od_memo',
                od_cart_count     = '$cart_count',
                od_cart_price     = '$tot_ct_price',
                od_cart_coupon    = '$tot_it_cp_price',
                od_send_cost      = '$od_send_cost',
                od_send_coupon    = '$tot_sc_cp_price',
                od_send_cost2     = '$od_send_cost2',
                od_coupon         = '$tot_od_cp_price',
                od_receipt_price  = '$od_receipt_price',
                od_receipt_point  = '$od_receipt_point',
                od_bank_account   = '$od_bank_account',
                od_receipt_time   = '$od_receipt_time',
                od_misu           = '$od_misu',
                od_pg             = '$od_pg',
                od_tno            = '$od_tno',
                od_app_no         = '$od_app_no',
                od_escrow         = '$od_escrow',
                od_tax_flag       = '$od_tax_flag',
                od_tax_mny        = '$od_tax_mny',
                od_vat_mny        = '$od_vat_mny',
                od_free_mny       = '$od_free_mny',
                od_status         = '$od_status',
                od_shop_memo      = '',
                od_hope_date      = '{$od_hope_date}',
                od_time           = '{$od['dt_time']}',
                od_ip             = '{$data['od_ip']}',
                od_settle_case    = '{$data['od_settle_case']}',
                od_test           = '{$data['od_test']}'
                ";
$result = sql_query($sql, true);

$sql_card_point = "";
if ($od_receipt_price > 0 && !$default['de_card_point']) {
    $sql_card_point = " , ct_point = '0' ";
}
$sql = "update {$g5['g5_shop_cart_table']}
           set od_id = '$od_id',
               ct_status = '입금'
               $sql_card_point
         where od_id = '{$od['cart_id']}'
           and ct_select = '1' ";
$result = sql_query($sql, true);

// 회원이면서 포인트를 사용했다면 테이블에 사용을 추가
if ($od['mb_id'] && $od_receipt_point)
    insert_point($od['mb_id'], (-1) * $od_receipt_point, "주문번호 $od_id 결제");

// 쿠폰사용내역기록
if($od['mb_id']) {
    $it_cp_cnt = (isset($data['cp_id']) && is_array($data['cp_id'])) ? count($data['cp_id']) : 0;
    for($i=0; $i<$it_cp_cnt; $i++) {
        $cid = $data['cp_id'][$i];
        $cp_it_id = $data['it_id'][$i];
        $cp_prc = isset($arr_it_cp_prc[$cp_it_id]) ? (int) $arr_it_cp_prc[$cp_it_id] : 0;

        if(trim($cid)) {
            $sql = " insert into {$g5['g5_shop_coupon_log_table']}
                        set cp_id       = '$cid',
                            mb_id       = '{$od['mb_id']}',
                            od_id       = '$od_id',
                            cp_price    = '$cp_prc',
                            cl_datetime = '{$od['dt_time']}' ";
            sql_query($sql);
        }

        // 쿠폰사용금액 cart에 기록
        $sql = " update {$g5['g5_shop_cart_table']}
                    set cp_price = '$cp_prc'
                    where od_id = '$od_id'
                      and it_id = '$cp_it_id'
                      and ct_select = '1'
                    order by ct_id asc
                    limit 1 ";
        sql_query($sql);
    }

    if(isset($data['od_cp_id']) && $data['od_cp_id']) {
        $sql = " insert into {$g5['g5_shop_coupon_log_table']}
                    set cp_id       = '{$data['od_cp_id']}',
                        mb_id       = '{$od['mb_id']}',
                        od_id       = '$od_id',
                        cp_price    = '$tot_od_cp_price',
                        cl_datetime = '{$od['dt_time']}' ";
        sql_query($sql);
    }

    if(isset($data['sc_cp_id']) && $data['sc_cp_id']) {
        $sql = " insert into {$g5['g5_shop_coupon_log_table']}
                    set cp_id       = '{$data['sc_cp_id']}',
                        mb_id       = '{$od['mb_id']}',
                        od_id       = '$od_id',
                        cp_price    = '$tot_sc_cp_price',
                        cl_datetime = '{$od['dt_time']}' ";
        sql_query($sql);
    }
}

// 주문정보
$info = get_order_info($od_id);

// 미수금 정보 등 반영
$sql = " update {$g5['g5_shop_order_table']}
            set od_misu         = '{$info['od_misu']}',
                od_tax_mny      = '{$info['od_tax_mny']}',
                od_vat_mny      = '{$info['od_vat_mny']}',
                od_free_mny     = '{$info['od_free_mny']}',
                od_status       = '$od_status'
            where od_id = '$od_id' ";
sql_query($sql);

// 임시 주문정보 삭제
$sql = " delete from {$g5['g5_shop_order_data_table']} where od_id = '$od_id' and dt_pg = '$od_pg' ";
sql_query($sql, true);

echo '<meta http-equiv="content-type" content="text/html; charset=utf-8">'.PHP_EOL;
echo '<script>'.PHP_EOL;
echo 'if(confirm("복구하신 주문 상세페이지로 이동하시겠습니까?"))'.PHP_EOL;
echo 'document.location.href = "./orderform.php?od_id='.$od_id.'";'.PHP_EOL;
echo 'else'.PHP_EOL;
echo 'document.location.href = "./inorderlist.php?'.str_replace('&amp;', '&', $qstr).'";'.PHP_EOL;
echo '</script>'.PHP_EOL;