<?php
include_once('./_common.php');

if(get_magic_quotes_gpc())
{
    //$_GET  = array_map("stripslashes", $_GET);
    //$_POST = array_map("stripslashes", $_POST);
    $_GET  = array_add_callback("stripslashes", $_GET);
    $_POST = array_add_callback("stripslashes", $_POST);
}
//$_GET  = array_map("mysql_real_escape_string", $_GET);
//$_POST = array_map("mysql_real_escape_string", $_POST);
$_GET  = array_add_callback("mysql_real_escape_string", $_GET);
$_POST = array_add_callback("mysql_real_escape_string", $_POST);

// 장바구니가 비어있는가?
if (get_session("ss_direct"))
    $tmp_uq_id = get_session('ss_uq_direct');
else
    $tmp_uq_id = get_session('ss_uq_id');

if (get_cart_count($tmp_uq_id) == 0)// 장바구니에 담기
    alert('장바구니가 비어 있습니다.\\n\\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.', G4_SHOP_URL.'/cart.php');

$error = "";
// 장바구니 상품 재고 검사
$sql = " select it_id,
                ct_qty,
                it_name,
                io_id,
                io_type,
                ct_option
           from {$g4['shop_cart_table']}
          where uq_id = '$tmp_uq_id'
            and ct_select = '1' ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    // 상품에 대한 현재고수량
    if($row['io_id']) {
        $it_stock_qty = (int)get_option_stock_qty($row['it_id'], $row['io_id'], $row['io_type']);
    } else {
        $it_stock_qty = (int)get_it_stock_qty($row['it_id']);
    }
    // 장바구니 수량이 재고수량보다 많다면 오류
    if ($row['ct_qty'] > $it_stock_qty)
        $error .= "{$row['ct_option']} 의 재고수량이 부족합니다. 현재고수량 : $it_stock_qty 개\\n\\n";
}

if($i == 0)
    alert('장바구니가 비어 있습니다.\\n\\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.', G4_SHOP_URL.'/cart.php');

if ($error != "")
{
    $error .= "다른 고객님께서 {$od_name}님 보다 먼저 주문하신 경우입니다. 불편을 끼쳐 죄송합니다.";
    alert($error);
}

$i_amount     = (int)$_POST['od_amount'];
$i_send_cost  = (int)$_POST['od_send_cost'];
$i_temp_point = (int)$_POST['od_temp_point'];


// 주문금액이 상이함
$sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as od_amount
            from {$g4['shop_cart_table']} where uq_id = '$tmp_uq_id' and ct_select = '1' ";
$row = sql_fetch($sql);
$tot_ct_amount = $row['od_amount'];

// 쿠폰금액계산
$tot_cp_amount = 0;
if($is_member) {
    // 상품쿠폰
    $tot_it_cp_amount = $tot_od_cp_amount = 0;
    $it_cp_cnt = count($_POST['cp_id']);
    $arr_it_cp_amt = array();
    for($i=0; $i<$it_cp_cnt; $i++) {
        $cid = $_POST['cp_id'][$i];
        $it_id = $_POST['it_id'][$i];
        $sql = " select cp_id, cp_method, cp_target, cp_type, cp_amount, cp_trunc, cp_minimum, cp_maximum
                    from {$g4['shop_coupon_table']}
                    where cp_id = '$cid'
                      and mb_id = '{$member['mb_id']}'
                      and cp_start <= '".G4_TIME_YMD."'
                      and cp_end >= '".G4_TIME_YMD."'
                      and cp_used = '0'
                      and cp_method IN ( 0, 1 ) ";
        $cp = sql_fetch($sql);
        if(!$cp['cp_id'])
            continue;

        // 분류할인인지
        if($cp['cp_method']) {
            $sql2 = " select it_id, ca_id, ca_id2, ca_id3
                        from {$g4['shop_item_table']}
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
        $sql = " select SUM( IF(io_type = '1', io_price * ct_qty, (ct_price + io_price) * ct_qty)) as sum_price
                    from {$g4['shop_cart_table']}
                    where uq_id = '$tmp_uq_id'
                      and it_id = '$it_id'
                      and ct_select = '1' ";
        $ct = sql_fetch($sql);
        $item_price = $ct['sum_price'];

        if($cp['cp_minimum'] > $item_price)
            continue;

        $dc = 0;
        if($cp['cp_type']) {
            $dc = floor(($item_price * ($cp['cp_amount'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];
        } else {
            $dc = $cp['cp_amount'];
        }

        if($cp['cp_maximum'] && $dc > $cp['cp_maximum'])
            $dc = $cp['cp_maximum'];

        $tot_it_cp_amount += $dc;
        $arr_it_cp_amt[$it_id] = $dc;
    }

    $tot_od_amount = $tot_ct_amount - $tot_it_cp_amount;

    // 주문쿠폰
    if($_POST['od_cp_id']) {
        $sql = " select cp_id, cp_type, cp_amount, cp_trunc, cp_minimum, cp_maximum
                    from {$g4['shop_coupon_table']}
                    where cp_id = '{$_POST['od_cp_id']}'
                      and mb_id = '{$member['mb_id']}'
                      and cp_start <= '".G4_TIME_YMD."'
                      and cp_end >= '".G4_TIME_YMD."'
                      and cp_used = '0'
                      and cp_method = '2' ";
        $cp = sql_fetch($sql);

        $dc = 0;
        if($cp['cp_id'] && ($cp['cp_minimum'] <= $tot_od_amount)) {
            if($cp['cp_type']) {
                $dc = floor(($tot_od_amount * ($cp['cp_amount'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];
            } else {
                $dc = $cp['cp_amount'];
            }

            if($cp['cp_maximum'] && $dc > $cp['cp_maximum'])
                $dc = $cp['cp_maximum'];

            $tot_od_cp_amount = $dc;
            $tot_od_amount -= $tot_od_cp_amount;
        }
    }

    $tot_cp_amount = $tot_it_cp_amount + $tot_od_cp_amount;
}

if ((int)($row['od_amount'] - $tot_cp_amount) !== $i_amount) {
    die("Error.");
}

// 배송비가 상이함
$tot_sell_amount = $row['od_amount'];
// 배송비 계산
if ($default['de_send_cost_case'] == '없음') {
    $send_cost = 0;
} else if($default['de_send_cost_case'] == '상한') {
    // 배송비 상한 : 여러단계의 배송비 적용 가능
    $send_cost_limit = explode(";", $default['de_send_cost_limit']);
    $send_cost_list  = explode(";", $default['de_send_cost_list']);
    $send_cost = 0;
    for ($k=0; $k<count($send_cost_limit); $k++) {
        // 총판매금액이 배송비 상한가 보다 작다면
        if ($tot_sell_amount < preg_replace('/[^0-9]/', '', $send_cost_limit[$k])) {
            $send_cost = preg_replace('/[^0-9]/', '', $send_cost_list[$k]);
            break;
        }
    }
} else { // 개별배송비
    $send_cost = 0;
    $sql = " select it_id
                from {$g4['shop_cart_table']}
                where uq_id = '$tmp_uq_id'
                  and ct_select = '1'
                  and ct_num = '0'
                  and ct_send_cost = '0' ";
    $result = sql_query($sql);
    for($i=0; $sc=sql_fetch_array($result); $i++) {
        // 합계
        $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
                        SUM(ct_qty) as qty
                    from {$g4['shop_cart_table']}
                    where it_id = '{$sc['it_id']}'
                      and uq_id = '$tmp_uq_id' ";
        $sum = sql_fetch($sql);

        $send_cost += get_item_sendcost($sc['it_id'], $sum['price'], $sum['qty']);
    }
}

$tot_sc_cp_amount = 0;
if($is_member && $send_cost > 0) {
    // 배송쿠폰
    if($_POST['sc_cp_id']) {
        $sql = " select cp_id, cp_type, cp_amount, cp_trunc, cp_minimum, cp_maximum
                    from {$g4['shop_coupon_table']}
                    where cp_id = '{$_POST['sc_cp_id']}'
                      and mb_id = '{$member['mb_id']}'
                      and cp_start <= '".G4_TIME_YMD."'
                      and cp_end >= '".G4_TIME_YMD."'
                      and cp_used = '0'
                      and cp_method = '3' ";
        $cp = sql_fetch($sql);

        $dc = 0;
        if($cp['cp_id'] && ($cp['cp_minimum'] <= $tot_od_amount)) {
            if($cp['cp_type']) {
                $dc = floor(($send_cost * ($cp['cp_amount'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];
            } else {
                $dc = $cp['cp_amount'];
            }

            if($cp['cp_maximum'] && $dc > $cp['cp_maximum'])
                $dc = $cp['cp_maximum'];

            if($dc > $send_cost)
                $dc = $send_cost;

            $tot_sc_cp_amount = $dc;
        }
    }
}

if ((int)($send_cost - $tot_sc_cp_amount) !== $i_send_cost) {
    die("Error..");
}

// 결제포인트가 상이함
$tot_amount = $tot_od_amount + ($send_cost - $tot_sc_cp_amount);
// 회원이면서 포인트사용이면
$temp_point = 0;
if($default['de_mileage_use']) {
    if ($is_member)
    {
        // 포인트 결제 사용 포인트보다 회원의 마일리지가 크다면
        if ($member['mb_mileage'] >= $default['de_point_settle'])
        {
            $temp_point = $tot_amount * ($default['de_point_per'] / 100); // 포인트 결제 % 적용
            $temp_point = (int)((int)($temp_point / 100) * 100); // 100점 단위

            $member_mileage = (int)((int)($member['mb_mileage'] / 100) * 100); // 100점 단위
            if ($temp_point > $member_mileage)
                $temp_point = $member_mileage;
        }
    }

    if (($i_temp_point > (int)$temp_point || $i_temp_point < 0))
        die("Error...");

    if ($od_temp_point)
    {
        if ($member['mb_mileage'] < $od_temp_point)
            alert('회원님의 마일리지가 부족하여 마일리지로 결제 할 수 없습니다.');
    }
} else {
    if ($is_member && $config['cf_use_point'])
    {
        // 포인트 결제 사용 포인트보다 회원의 포인트가 크다면
        if ($member['mb_point'] >= $default['de_point_settle'])
        {
            $temp_point = $tot_amount * ($default['de_point_per'] / 100); // 포인트 결제 % 적용
            $temp_point = (int)((int)($temp_point / 100) * 100); // 100점 단위

            $member_point = (int)((int)($member['mb_point'] / 100) * 100); // 100점 단위
            if ($temp_point > $member_point)
                $temp_point = $member_point;
        }
    }

    if (($i_temp_point > (int)$temp_point || $i_temp_point < 0) && $config['cf_use_point'])
        die("Error...");

    if ($od_temp_point)
    {
        if ($member['mb_point'] < $od_temp_point)
            alert('회원님의 포인트가 부족하여 포인트로 결제 할 수 없습니다.');
    }
}

$i_amount = $i_amount + $i_send_cost - $i_temp_point;

if ($od_settle_case == "무통장")
{
    $od_temp_bank       = $i_amount;
    $od_temp_point      = $i_temp_point;
    $od_receipt_point   = $i_temp_point;
    $od_receipt_bank    = 0;
}
else if ($od_settle_case == "계좌이체")
{
    include G4_SHOP_PATH.'/kcp/pp_ax_hub.php';

    $od_temp_bank       = $i_amount;
    $od_temp_point      = $i_temp_point;

    $od_escrow1         = $tno;
    $od_receipt_bank    = $amount;
    $od_receipt_point   = $i_temp_point;
    $od_bank_time       = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $od_bank_account    = $od_settle_case;
    $od_deposit_name    = $od_name;
    $bank_name          = iconv("cp949", "utf8", $bank_name);
    $od_bank_account    = $bank_name;
    $pg_amount          = $amount;
}
else if ($od_settle_case == "가상계좌")
{
    include G4_SHOP_PATH.'/kcp/pp_ax_hub.php';

    $od_temp_bank       = $i_amount;
    $od_temp_point      = $i_temp_point;
    $od_receipt_point   = 0;

    $od_escrow1         = $tno;
    $od_receipt_amount  = 0;
    $bankname           = iconv("cp949", "utf8", $bankname);
    $depositor          = iconv("cp949", "utf8", $depositor);
    $od_bank_account    = $bankname.' '.$account.' '.$depositor;
    $od_deposit_name    = $od_name;
    $pg_amount          = $amount;
}
else if ($od_settle_case == "휴대폰")
{
    include G4_SHOP_PATH.'/kcp/pp_ax_hub.php';

    $od_temp_bank       = $i_amount;
    $od_temp_point      = $i_temp_point;

    $od_escrow1         = $tno;
    $od_receipt_hp      = $amount;
    $od_receipt_point   = $i_temp_point;
    $od_hp_time         = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $od_bank_account    = $commid.' '.$mobile_no;
    $pg_amount          = $amount;
}
else if ($od_settle_case == "신용카드")
{
    include G4_SHOP_PATH.'/kcp/pp_ax_hub.php';

    $od_temp_card       = $i_amount;
    $od_temp_point      = $i_temp_point;

    $od_escrow1         = $tno;
    $od_receipt_card    = $amount;
    $od_receipt_point   = $i_temp_point;
    $od_card_time       = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $card_name          = iconv("cp949", "utf8", $card_name);
    $od_bank_account    = $card_name;
    $pg_amount          = $amount;
}
else
{
    die("od_settle_case Error!!!");
}

// 주문금액과 결제금액이 일치하는지 체크
if($tno) {
    if((int)$i_amount !== (int)$pg_amount) {
        $cancel_msg = '결제금액 불일치';
        include G4_SHOP_PATH.'/kcp/pp_ax_hub_cancel.php'; // 결제취소처리

        die("Receipt Amount Error");
    }
}

if ($is_member)
    $od_pwd = $member['mb_password'];
else
    $od_pwd = sql_password($_POST['od_pwd']);

// 주문번호를 얻는다.
$od_id = get_session('ss_order_uniqid');

// 주문상품의 uq_id 변경을 위한 uq_id를 얻는다.
$uq_id = get_uniqid();

// 주문서에 입력
$sql = " insert {$g4['shop_order_table']}
            set od_id             = '$od_id',
                uq_id             = '$uq_id',
                mb_id             = '{$member['mb_id']}',
                od_pwd            = '$od_pwd',
                od_name           = '$od_name',
                od_email          = '$od_email',
                od_tel            = '$od_tel',
                od_hp             = '$od_hp',
                od_zip1           = '$od_zip1',
                od_zip2           = '$od_zip2',
                od_addr1          = '$od_addr1',
                od_addr2          = '$od_addr2',
                od_b_name         = '$od_b_name',
                od_b_tel          = '$od_b_tel',
                od_b_hp           = '$od_b_hp',
                od_b_zip1         = '$od_b_zip1',
                od_b_zip2         = '$od_b_zip2',
                od_b_addr1        = '$od_b_addr1',
                od_b_addr2        = '$od_b_addr2',
                od_deposit_name   = '$od_deposit_name',
                od_memo           = '$od_memo',
                od_send_cost      = '$od_send_cost',
                od_send_coupon    = '$tot_sc_cp_amount',
                od_coupon         = '$tot_od_cp_amount',
                od_temp_bank      = '$od_temp_bank',
                od_temp_card      = '$od_receipt_card',
                od_temp_hp        = '$od_receipt_hp',
                od_temp_point     = '$od_temp_point',
                od_receipt_bank   = '$od_receipt_bank',
                od_receipt_card   = '$od_receipt_card',
                od_receipt_hp     = '$od_receipt_hp',
                od_receipt_point  = '$od_receipt_point',
                od_bank_account   = '$od_bank_account',
                od_bank_time      = '$od_bank_time',
                od_card_time      = '$od_card_time',
                od_hp_time        = '$od_hp_time',
                od_escrow1        = '$od_escrow1',
                od_shop_memo      = '',
                od_hope_date      = '$od_hope_date',
                od_time           = '".G4_TIME_YMDHIS."',
                od_ip             = '$REMOTE_ADDR',
                od_settle_case    = '$od_settle_case'
                ";
$result = sql_query($sql, false);

// 주문정보 입력 오류시 kcp 결제 취소
if(!$result) {
    if($tno) {
        $cancel_msg = '주문정보 입력 오류';
        include G4_SHOP_PATH.'/kcp/pp_ax_hub_cancel.php'; // 결제취소처리
    }

    die("<p>$sql<p>" . mysql_errno() . " : " .  mysql_error() . "<p>error file : {$_SERVER['PHP_SELF']}");
}

// 장바구니 쇼핑에서 주문으로
// 신용카드로 주문하면서 신용카드 포인트 사용하지 않는다면 포인트 부여하지 않음
$sql_card_point = "";
//if ($od_receipt_card > 0 && $default[de_card_point] == false) {
if (($od_receipt_card > 0 || $od_receipt_hp > 0) && $default['de_card_point'] == false) {
    $sql_card_point = " , ct_point = '0' ";
}
$sql = "update {$g4['shop_cart_table']}
           set uq_id = '$uq_id',
               ct_status = '주문'
               $sql_card_point
         where uq_id = '$tmp_uq_id'
           and ct_select = '1' ";
$result = sql_query($sql, false);

// 주문정보 입력 오류시 kcp 결제 취소
if(!$result) {
    if($tno) {
        $cancel_msg = '주문상태 변경 오류';
        include G4_SHOP_PATH.'/kcp/pp_ax_hub_cancel.php'; // 결제취소처리
    }

    echo "<p>$sql<p>" . mysql_errno() . " : " .  mysql_error() . "<p>error file : {$_SERVER['PHP_SELF']}";

    // 주문삭제
    sql_query(" delete from {$g4['shop_order_table']} where od_id = '$od_id' and uq_id = '$uq_id' ");
    exit;
}

// 회원이면서 포인트를 사용했다면 테이블에 사용을 추가
if ($is_member && $od_receipt_point) {
    if(!$default['de_mileage_use'])
        insert_point($member['mb_id'], (-1) * $od_receipt_point, "주문번호 $od_id 결제");
    insert_mileage($member['mb_id'], (-1) * $od_receipt_point, "주문번호 $od_id 결제", $od_id);
}

// PG 결제내역기록
include_once(G4_SHOP_PATH.'/kcp/pp_ax_hub_result.php');

$od_memo = nl2br(htmlspecialchars2(stripslashes($od_memo))) . "&nbsp;";


// 쿠폰사용내역기록
if($is_member) {
    $it_cp_cnt = count($_POST['cp_id']);
    for($i=0; $i<$it_cp_cnt; $i++) {
        $cid = $_POST['cp_id'][$i];
        $cp_it_id = $_POST['it_id'][$i];
        $sql = " update {$g4['shop_coupon_table']}
                    set od_id = '$od_id',
                        cp_used = '1',
                        cp_used_time = '".G4_TIME_YMDHIS."'
                    where cp_id = '$cid'
                      and mb_id = '{$member['mb_id']}'
                      and cp_method IN ( 0, 1 ) ";
        sql_query($sql);

        // 쿠폰사용금액 cart에 기록
        $cp_amt = (int)$arr_it_cp_amt[$cp_it_id];
        $sql = " update {$g4['shop_cart_table']}
                    set cp_amount = '$cp_amt'
                    where uq_id = '$uq_id'
                      and it_id = '$cp_it_id'
                      and ct_select = '1'
                      and ct_num = '0' ";
        sql_query($sql);
    }

    if($_POST['od_cp_id']) {
        $sql = " update {$g4['shop_coupon_table']}
                    set od_id = '$od_id',
                        cp_used = '1',
                        cp_used_time = '".G4_TIME_YMDHIS."'
                    where cp_id = '{$_POST['od_cp_id']}'
                      and mb_id = '{$member['mb_id']}'
                      and cp_method = '2' ";
        sql_query($sql);
    }

    if($_POST['sc_cp_id']) {
        $sql = " update {$g4['shop_coupon_table']}
                    set od_id = '$od_id',
                        cp_used = '1',
                        cp_used_time = '".G4_TIME_YMDHIS."'
                    where cp_id = '{$_POST['sc_cp_id']}'
                      and mb_id = '{$member['mb_id']}'
                      and cp_method = '3' ";
        sql_query($sql);
    }
}


include_once(G4_SHOP_PATH.'/ordermail1.inc.php');
include_once(G4_SHOP_PATH.'/ordermail2.inc.php');

// SMS BEGIN --------------------------------------------------------
// 주문고객과 쇼핑몰관리자에게 SMS 전송
if($default['de_sms_use'] && ($default['de_sms_use2'] || $default['de_sms_use3'])) {
    $sms_contents = array($default['de_sms_cont2'], $default['de_sms_cont3']);
    $recv_numbers = array($od_hp, $default['de_sms_hp']);
    $send_numbers = array($default['de_admin_company_tel'], $od_hp);

    include_once(G4_LIB_PATH.'/icode.sms.lib.php');

    $SMS = new SMS; // SMS 연결
    $SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);
    $sms_count = 0;

    for($s=0; $s<count($sms_contents); $s++) {
        $sms_content = $sms_contents[$s];
        $recv_number = preg_replace("/[^0-9]/", "", $recv_numbers[$s]);
        $send_number = preg_replace("/[^0-9]/", "", $send_numbers[$s]);

        $sms_content = preg_replace("/{이름}/", $od_name, $sms_content);
        $sms_content = preg_replace("/{보낸분}/", $od_name, $sms_content);
        $sms_content = preg_replace("/{받는분}/", $od_b_name, $sms_content);
        $sms_content = preg_replace("/{주문번호}/", $od_id, $sms_content);
        $sms_content = preg_replace("/{주문금액}/", number_format($ttotal_amount), $sms_content);
        $sms_content = preg_replace("/{회원아이디}/", $member['mb_id'], $sms_content);
        $sms_content = preg_replace("/{회사명}/", $default['de_admin_company_name'], $sms_content);

        $idx = 'de_sms_use'.($s + 2);

        if($default[$idx] && $recv_number) {
            $SMS->Add($recv_number, $send_number, $default['de_icode_id'], iconv("utf-8", "euc-kr", stripslashes($sms_content)), "");
            $sms_count++;
        }
    }

    if($sms_count > 0)
        $SMS->Send();
}
// SMS END   --------------------------------------------------------


// orderview 에서 사용하기 위해 tmp에 넣고
set_session('ss_temp_uq_id', $uq_id);

// 주문번호제거
set_session('ss_order_uniqid', '');

// 기존자료 세션에서 제거
if (get_session('ss_direct'))
    set_session('ss_uq_direct', '');

goto_url(G4_SHOP_URL.'/orderinquiryview.php?od_id='.$od_id.'&amp;uq_id='.$uq_id);
?>
