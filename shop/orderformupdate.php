<?
include_once('./_common.php');

// 새로운 주문번호 체크
$od_id = get_session('ss_order_uniqid');
if(!$od_id || $od_id != $od_uq_id) {
    die("Order Number Error.");
}

if(get_magic_quotes_gpc())
{
    /*
    $_GET  = array_map("stripslashes", $_GET);
    $_POST = array_map("stripslashes", $_POST);
    */
    $_GET  = array_add_callback("stripslashes", $_GET);
    $_POST = array_add_callback("stripslashes", $_POST);
}
/*
$_GET  = array_map("mysql_real_escape_string", $_GET);
$_POST = array_map("mysql_real_escape_string", $_POST);
*/
$_GET  = array_add_callback("mysql_real_escape_string", $_GET);
$_POST = array_add_callback("mysql_real_escape_string", $_POST);

$uq_id = get_session('ss_uniqid');

// 장바구니가 비어있는가?
if(get_session('ss_direct')) {
    $sw_direct = 1;
} else {
    $sw_direct = 0;
}

$cart_count = get_cart_count($uq_id, $sw_direct, $member['mb_id']);

if ($cart_count == 0)
    alert("장바구니가 비어 있습니다.\\n\\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.", "./cart.php");

$error = "";
// 장바구니 상품 재고 검사
// 1.03.07 : and a.it_id = b.it_id : where 조건문에 이 부분 추가
$sql = " select a.it_id,
                a.it_name,
                a.ct_option,
                a.is_option,
                a.opt_id,
                a.it_amount,
                a.ct_amount,
                a.ct_qty,
                b.it_use,
                b.it_gallery,
                b.it_tel_inq
           from {$g4['yc4_cart_table']} a,
                {$g4['yc4_item_table']} b
          where a.uq_id = '$uq_id'
            and a.ct_direct = '$sw_direct'
            and a.it_id = b.it_id ";

if($w == "selectedbuy")
    $sql .= " and a.ct_selected = '1' ";

$result = sql_query($sql);

$$tot_sell_amount = 0; // 총 주문금액

for ($i=0; $row=sql_fetch_array($result); $i++)
{
    // 주문가능 상품인지
    if(!$row['it_use'] || $row['it_gallery'] || $row['it_tel_inq']) {
        alert($row['it_name'].'은(는) 주문이 불가능한 상품입니다.');
    }

    if($row['is_option']) {
        // 주문가능한 옵션인지
        if($row['is_option'] == 1) {
            $sql1 = " select opt_use as option_use from {$g4['yc4_option_table']}
                        where it_id = '{$row['it_id']}' and opt_id = '{$row['opt_id']}' ";
        } else {
            $sql1 = " select sp_use as option_use from {$g4['yc4_supplement_table']}
                        where it_id = '{$row['it_id']}' and sp_id = '{$row['opt_id']}' ";
        }
        $row1 = sql_fetch($sql1);

        if(!$row1['option_use']) {
            $ct_option = $_POST['ct_option'][$i];
            $msg = '선택하신 상품 : '.$row['it_name'].'('.$row['ct_option'].')은(는) 구매할 수 없습니다.';

            alert($msg);
        }

        // 이미 장바구니에 있는 같은 옵션의 수량합계를 구한다.
        $sql2 = " select SUM(ct_qty) as cnt from {$g4['yc4_cart_table']}
                    where it_id = '{$row['it_id']}' and opt_id = '{$row['opt_id']}' and uq_id <> '$uq_id' and is_option = '{$row['is_option']}' and ct_status = '쇼핑' ";
        $row2 = sql_fetch($sql2);
        $cart_qty = $row2['cnt'];
        $stock_qty = get_option_stock_qty($row['it_id'], $row['opt_id'], $row['is_option']);
    } else {
        // 이미 장바구니에 있는 같은 상품의 수량합계를 구한다.
        $sql2 = " select SUM(ct_qty) as cnt from {$g4['yc4_cart_table']}
                    where it_id = '{$row['it_id']}' and uq_id <> '$uq_id' and is_option = '{$row['is_option']}' and ct_status = '쇼핑' ";
        $row2 = sql_fetch($sql2);
        $cart_qty = $row2['cnt'];
        $stock_qty = get_it_stock_qty($row['it_id']);
    }

    if($stock_qty < $ct_qty + $cart_qty) {
        if($row['is_option']) {
            $msg = '선택하신 상품 : '.$row['it_name'].'('.$row['ct_option'].')은(는) 재고가 부족하여 구매할 수 없습니다.';
        } else {
            $msg = '선택하신 상품 : '.$row['it_name'].'은(는) 재고가 부족하여 구매할 수 없습니다.';
        }

        alert($msg);
    }

    // 총 주문금액 계산
    $sell_amount = ((int)$row['it_amount'] + (int)$row['ct_amount']) * (int)$row['ct_qty'];
    $tot_sell_amount += $sell_amount;
}

// 주문총금액
/*
$sql1 = " select SUM((it_amount + ct_amount) * ct_qty) as od_amount
            from {$g4['yc4_cart_table']}
            where uq_id = '$uq_id'
              and ct_direct = '$sw_direct' ";
$row1 = sql_fetch($sql1);
$tot_sell_amount = $row1['od_amount'];
*/

// 배송비 계산
if ($default['de_send_cost_case'] == "없음" || $default['de_send_cost_case'] == "착불")
    $send_cost = 0;
else if($default['de_send_cost_case'] == "상한") {
    // 배송비 상한 : 여러단계의 배송비 적용 가능
    $send_cost_limit = explode(";", $default['de_send_cost_limit']);
    $send_cost_list  = explode(";", $default['de_send_cost_list']);
    $send_cost = 0;
    for ($k=0; $k<count($send_cost_limit); $k++) {
        // 총판매금액이 배송비 상한가 보다 작다면
        if ($tot_sell_amount < $send_cost_limit[$k]) {
            $send_cost = $send_cost_list[$k];
            break;
        }
    }
} else if($default['de_send_cost_case'] == "개별배송") {
    $send_cost = 0;

    $sql = " select a.ct_id,
                    a.it_id,
                    a.ct_send_cost_pay,
                    b.it_sc_type,
                    b.it_sc_basic,
                    b.it_sc_condition
               from {$g4['yc4_cart_table']} as a left join {$g4['yc4_item_table']} as b on ( a.it_id = b.it_id )
               where a.uq_id = '$uq_id'
                and a.ct_direct = '$sw_direct'
                and a.ct_parent = '0' ";

    if($w == "selectedbuy")
        $sql .= " and a.ct_selected = '1' ";

    $sql .= " order by a.ct_id ";

    $result = sql_query($sql);

    for($i=0; $row=sql_fetch_array($result); $i++) {
        if($row['ct_send_cost_pay'] == "착불") {
            $send_cost += 0;
        } else {
            // 금액, 수량 계산
            $sql = " select SUM((ct_amount + it_amount) * ct_qty) as sum_amount,
                            SUM(ct_qty) as sum_qty
                        from {$g4['yc4_cart_table']}
                        where ct_id = '{$row['ct_id']}'
                          or ct_parent = '{$row['ct_id']}' ";
            $sum = sql_fetch($sql);

            if($row['it_sc_type'] == 1) { // 조건부무료
                if($sum['sum_amount'] >= $row['it_sc_condition']) {
                    $send_cost += 0;
                } else {
                    $send_cost += $row['it_sc_basic'];
                }
            } else if($row['it_sc_type'] == 2) { // 유료
                $send_cost += $row['it_sc_basic'];
            } else if($row['it_sc_type'] == 3) { // 수량별부과
                $qty = ceil($sum['sum_qty'] / $row['it_sc_condition']);
                $send_cost += ($row['it_sc_basic'] * $qty);
            } else {
                $send_cost += 0;
            }
        }
    }
}

// 쿠폰적용금액계산
$item_dc_amount = 0;
$sendcost_dc_amount = 0;
$order_dc_amount = 0;

if($is_member) {
    $arr_item_coupon = array();
    $arr_idx = 0;

    // 상품할인쿠폰
    $cp_id_count = count($_POST['od_cp_id']);
    for($i=0; $i<$cp_id_count; $i++) {
        $cp_id = $_POST['od_cp_id'][$i];
        $it_id = $_POST['od_it_id'][$i];

        if(!$cp_id) {
            continue;
        }

        // 쿠폰정보
        $sql = " select *
                    from {$g4['yc4_coupon_table']}
                    where cp_id = '$cp_id'
                      and cp_use = '1'
                      and cp_start <= '{$g4['time_ymd']}'
                      and cp_end >= '{$g4['time_ymd']}'
                      and cp_type = '0' ";
        $cp = sql_fetch($sql);
        if(!$cp['cp_id']) { // 쿠폰정보없음
            continue;
        }

        // 상품정보
        $sql = " select it_id, ca_id, ca_id2, ca_id3, it_nocoupon
                    from {$g4['yc4_item_table']}
                    where it_id = '$it_id' ";
        $it = sql_fetch($sql);
        if($it['it_nocoupon']) { // 쿠폰제외상품
            continue;
        }

        // 쿠폰제외 카테고리에 속해있는지..
        $no = '';
        $ca_nocoupon = false;
        for($k=0; $k<3; $k++) {
            if($k > 0) {
                $no = $k + 1;
            }

            $ca_id = $it["ca_id{$no}"];

            $sql = " select ca_nocoupon from {$g4['yc4_category_table']} where ca_id = '$ca_id' ";
            $temp = sql_fetch($sql);

            if($temp['ca_nocoupon']) {
                $ca_nocoupon = true;
                break;
            }
        }

        if($ca_nocoupon) { // 쿠폰제외 카테고리 상품이면 다음으로
            continue;
        }

        if($cp['cp_target'] == 0 && $cp['it_id'] != $it_id) { // 쿠폰적용 상품 아님
            continue;
        }

        if($cp['cp_target'] == 1 && $cp['ca_id'] != '전체카테고리') { // 적용범위가 카테고리 일 때
            $no = '';
            $ca_id_check = false;
            for($k=0; $k<3; $k++) {
                if($k > 0) {
                    $no = $k + 1;
                }

                $ca_id = $it["ca_id$no"];
                if($cp['ca_id'] == $ca_id) {
                    $ca_id_check = true;
                    break;
                }
            }

            if(!$ca_id_check) { // 쿠폰 적용 카테고리 아님
                continue;
            }
        }

        if($cp['mb_id'] != '전체회원' && $cp['mb_id'] != $member['mb_id']) { // 쿠폰 사용 회원 아님
            continue;
        }

        // 이미 사용한 쿠폰인지
        $sql = " select ch_no
                    from {$g4['yc4_coupon_history_table']}
                    where cp_id = '$cp_id'
                      and it_id = '$it_id'
                      and mb_id = '{$member['mb_id']}'
                      and uq_id <> '$uq_id' ";
        $ch = sql_fetch($sql);

        if($ch['ch_no']){
            continue;
        }

        // 쿠폰할인금액
        $dc_amount = 0;
        if($cp['cp_method']) {
            // 해당상품총금액
            $sql3 = " select SUM((ct_amount + it_amount) * ct_qty) as item_amount
                        from {$g4['yc4_cart_table']}
                        where it_id = '$it_id'
                          and uq_id = '$uq_id'
                          and ct_direct = '$sw_direct' ";
            $row3 = sql_fetch($sql3);
            $dc_amount = floor(($row3['item_amount'] * ($cp['cp_amount'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];

            if($dc_amount > $cp['cp_maximum']) { // 최대할인금액보다 크면
                $dc_amount = $cp['cp_maximum'];
            }
        } else {
            $dc_amount = $cp['cp_amount'];
        }

        $item_dc_amount += $dc_amount;

        // 쿠폰사용정보 $arr_item_coupon에 저장
        $arr_item_coupon[$arr_idx]['cp_id'] = $cp_id;
        $arr_item_coupon[$arr_idx]['cp_subject'] = $cp['cp_subject'];
        $arr_item_coupon[$arr_idx]['it_id'] = $it_id;
        $arr_item_coupon[$arr_idx]['ct_id'] = $_POST['od_ct_id'][$i];
        $arr_item_coupon[$arr_idx]['ch_amount'] = $dc_amount;

        $arr_idx++;
    }

    // 배송비할인
    $s_cp_id = $_POST['od_send_coupon'];
    if($s_cp_id) {
        $sql4 = " select cp_id, cp_amount, cp_minimum, cp_subject, mb_id
                    from {$g4['yc4_coupon_table']}
                    where cp_id = '$s_cp_id'
                      and cp_type = '2'
                      and cp_use = '1'
                      and cp_start <= '{$g4['time_ymd']}'
                      and cp_end >= '{$g4['time_ymd']}' ";
        $row4 = sql_fetch($sql4);

        if($row4['mb_id'] == '전체회원' || $row4['mb_id'] == $member['mb_id']) {
            // 주문금액이 최소주문금액보다 크다면
            if($tot_sell_amount >= $row4['cp_minimum']) {
                if($row4['cp_id']) {
                    // 사용쿠폰인지체크
                    $sql5 = " select ch_no
                                from {$g4['yc4_coupon_history_table']}
                                where cp_id = '$s_cp_id'
                                  and mb_id = '{$member['mb_id']}'
                                  and uq_id <> '$uq_id' ";
                    $row5 = sql_fetch($sql5);

                    if(!$row5['ch_no']) {
                        $sendcost_dc_amount = $row4['cp_amount'];

                        if($send_cost != 0 && $sendcost_dc_amount > $send_cost) {
                            $sendcost_dc_amount = $send_cost;
                        }

                        // 배송비쿠폰정보저장
                        $arr_send_coupon['cp_id'] = $s_cp_id;
                        $arr_send_coupon['cp_subject'] = $row4['cp_subject'];
                        $arr_send_coupon['ch_amount'] = $sendcost_dc_amount;
                    }
                }
            }
        }
    }

    // 주문금액할인
    $o_cp_id = $_POST['od_coupon'];
    if($o_cp_id) {
        $sql4 = " select cp_id, cp_method, cp_amount, cp_trunc, cp_minimum, cp_maximum, cp_subject, mb_id
                    from {$g4['yc4_coupon_table']}
                    where cp_id = '$o_cp_id'
                      and cp_use = '1'
                      and cp_type = '1'
                      and cp_start <= '{$g4['time_ymd']}'
                      and cp_end >= '{$g4['time_ymd']}' ";
        $row4 = sql_fetch($sql4);

        if($row4['mb_id'] == '전체회원' || $row4['mb_id'] == $member['mb_id']) {
            // 주문금액이 최소주문금액보다 크다면
            if($tot_sell_amount >= $row4['cp_minimum']) {
                if($row4['cp_id']) {
                    // 사용쿠폰인지체크
                    $sql5 = " select ch_no
                                from {$g4['yc4_coupon_history_table']}
                                where cp_id = '$o_cp_id'
                                  and mb_id = '{$member['mb_id']}'
                                  and uq_id <> '$uq_id' ";
                    $row5 = sql_fetch($sql5);

                    if(!$row5['ch_no']) {
                        if($row4['cp_method']) { // 정율(%)할인
                            $order_dc_amount = floor(($tot_sell_amount * ($row4['cp_amount'] / 100) / $row4['cp_trunc'])) * $row4['cp_trunc'];
                            if($row4['cp_maximum'] && $order_dc_amount > $row4['cp_maximum']) { // 최대할인금액보다 크다면
                                $order_dc_amount = $row4['cp_maximum'];
                            }
                        } else {
                            $order_dc_amount = $row4['cp_amount'];
                        }

                        // 결제할인쿠폰정보저장
                        $arr_order_coupon['cp_id'] = $o_cp_id;
                        $arr_order_coupon['cp_subject'] = $row4['cp_subject'];
                        $arr_order_coupon['ch_amount'] = $order_dc_amount;
                    }
                }
            }
        }
    }
}

// POST로 넘어온 값
$i_amount       = (int)$_POST['od_amount'];
$i_amount_dc    = (int)$_POST['od_coupon_amount'];
$i_send_cost    = (int)$_POST['od_send_cost'];
$i_send_cost_dc = (int)$_POST['od_send_coupon_amount'];
$i_send_cost_area = (int)$_POST['od_send_cost_area'];
$i_temp_point   = (int)$_POST['od_temp_point'];
$i_cp_amount = 0;
$cp_amount_count = count($_POST['od_ch_amount']);
for($i=0; $i<$cp_amount_count; $i++) {
    $i_cp_amount += (int)$_POST['od_ch_amount'][$i];
}

// 주문금액이 상이함
if (((int)$tot_sell_amount - (int)$item_dc_amount - (int)$order_dc_amount) !== ($i_amount - $i_amount_dc - $i_cp_amount)) {
    die("Error.");
}

// 배송비가 상이함
// 추가배송비
$zipcode = $od_b_zip1.$od_b_zip2;
$sql = " select sc_amount from {$g4['yc4_sendcost_table']} where sc_zip1 <= '$zipcode' and sc_zip2 >= '$zipcode' ";
$row = sql_fetch($sql);
$area_send_cost = (int)$row['sc_amount'];

if (((int)$send_cost - (int)$sendcost_dc_amount + $area_send_cost) !== ($i_send_cost - $i_send_cost_dc + $i_send_cost_area)) {
    die("Error..");
}

// 결제포인트가 상이함
$tot_amount = $tot_sell_amount - $tot_dc_amount + $send_cost;
// 회원이면서 포인트사용이면
$temp_point = 0;
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

if ($i_temp_point)
{
    if ($member['mb_point'] < $i_temp_point)
        alert("회원님의 포인트가 부족하여 포인트로 결제 할 수 없습니다.");
}

// 결제할 금액
$od_amount = $i_amount + $i_send_cost - $i_temp_point - $i_amount_dc - $i_cp_amount - $i_send_cost_dc + $i_send_cost_area;

$same_amount_check = false;

if ($od_settle_case == "무통장")
{
    $od_receipt_amount  = 0;
    $od_receipt_point   = $i_temp_point;
}
else if ($od_settle_case == "계좌이체")
{
    include "./kcp/pp_ax_hub.php";

    $od_receipt_amount  = $amount;
    $od_receipt_time    = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $tno                = $tno;
    $od_receipt_point   = $i_temp_point;
    if (strtolower($g4['charset']) == "utf-8") {
        $bank_name = iconv("cp949", "utf8", $bank_name);
    }
    $od_bank_account    = $bank_name;
    $od_deposit_name    = $od_name;
    $same_amount_check  = true;
}
else if($od_settle_case == "가상계좌")
{
    include "./kcp/pp_ax_hub.php";

    $od_receipt_amount  = 0;
    $od_receipt_point   = $i_temp_point;
    if (strtolower($g4['charset']) == "utf-8") {
        $bankname = iconv("cp949", "utf8", $bankname);
        $depositor = iconv("cp949", "utf8", $depositor);
    }
    $od_bank_account    = $bankname.' '.$account;
    $od_deposit_name    = $depositor;
}
else if ($od_settle_case == "휴대폰")
{
    include "./kcp/pp_ax_hub.php";

    $od_receipt_amount  = $amount;
    $od_receipt_time    = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $tno                = $tno;
    $od_receipt_point   = $i_temp_point;
    $od_bank_account    = $commid.' '.$mobile_no;
    $same_amount_check  = true;
}
else if ($od_settle_case == "신용카드")
{
    include "./kcp/pp_ax_hub.php";

    $od_receipt_amount  = $amount;
    $od_receipt_time    = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $tno                = $tno;
    $od_receipt_point   = $i_temp_point;
    if (strtolower($g4['charset']) == "utf-8") {
        $card_name = iconv("cp949", "utf8", $card_name);
    }
    $od_bank_account    = $card_name;
    $same_amount_check  = true;
}
else
{
    die("od_settle_case Error!!!");
}

// 주문금액과 결제금액이 일치하는지 체크
if($same_amount_check) {
    if((int)$od_amount !== (int)$od_receipt_amount) {
        include "./kcp/pp_ax_hub_cancel.php"; // 결제취소처리

        die("Order Receipt Amount Error");
    }
}

if ($is_member)
    $od_pwd = $member['mb_password'];
else
    $od_pwd = sql_password($_POST['od_pwd']);

// 주문서에 입력
$sql = " insert {$g4['yc4_order_table']}
            set od_id             = '$od_id',
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
                od_send_cost_area = '$od_send_cost_area',
                od_send_coupon    = '$od_send_coupon_amount',
                od_amount         = '$od_amount',
                od_receipt_amount = '$od_receipt_amount',
                od_receipt_time   = '$od_receipt_time',
                od_receipt_point  = '$od_receipt_point',
                od_bank_account   = '$od_bank_account',
                od_shop_memo      = '',
                tno               = '$tno',
                escw_yn           = '$escw_yn',
                od_coupon_amount  = '$od_coupon_amount',
                od_hope_date      = '$od_hope_date',
                od_time           = '{$g4['time_ymdhis']}',
                od_ip             = '$REMOTE_ADDR',
                od_settle_case    = '$od_settle_case',
                od_cash_yn        = '$cash_yn',
                od_cash_authno    = '$cash_authno',
                od_cash_tr_code   = '$cash_tr_code'
                ";
$result = sql_query($sql, FALSE);

// 주문정보 입력 때 오류가 발생했다면
if(!$result) {
    if($tno) { // KCP 결제 취소처리
        include "./kcp/pp_ax_hub_cancel.php";
    } else {
        alert("주문정보 입력 중 오류가 발생했습니다. 다시 주문해 주세요.", "./cart.php");
    }
}

// 장바구니 쇼핑에서 주문으로
// 신용카드 또는 휴대폰 결제로 주문하면서 신용카드 포인트 사용하지 않는다면 포인트 부여하지 않음
$sql_card_point = "";
if ($od_receipt_amount > 0 && ($od_settle_case == '신용카드' || $od_settle_case == '휴대폰') && $default['de_card_point'] == false) {
    $sql_card_point = " , ct_point = '0' ";
}

$ct_id_count = count($_POST['od_ct_id']);
for($j=0; $j<$ct_id_count; $j++) {
    $temp_ct_id = $_POST['od_ct_id'][$j];

    $sql = "update {$g4['yc4_cart_table']}
               set uq_id        = '$od_id',
                   ct_status    = '주문'
                   $sql_card_point
             where ct_id = '$temp_ct_id'
                or ct_parent = '$temp_ct_id' ";

    sql_query($sql);
}

// 재고조정
$sql = " select it_id, is_option, opt_id, ct_qty
           from {$g4['yc4_cart_table']}
          where uq_id = '$od_id'
            and ct_direct = '$sw_direct' ";
$result = sql_query($sql);

for($i=0; $it=sql_fetch_array($result); $i++) {
    if($it['is_option'] == 1) { // 선택옵션
        $sql = " update {$g4['yc4_option_table']}
                      set opt_qty = IF( (opt_qty - {$it['ct_qty']}) > 0, (opt_qty - {$it['ct_qty']}), 0 )
                    where it_id = '{$it['it_id']}'
                      and opt_id = '{$it['opt_id']}' ";
        sql_query($sql);
    } else if($it['is_option'] == 2) { // 추가옵션
        $sql = " update {$g4['yc4_supplement_table']}
                      set sp_qty = IF( (sp_qty - {$it['ct_qty']}) > 0, (sp_qty - {$it['ct_qty']}), 0 )
                    where it_id = '{$it['it_id']}'
                      and sp_id = '{$it['opt_id']}' ";
        sql_query($sql);
    } else { // No옵션상품
        $sql = " update {$g4['yc4_item_table']}
                      set it_stock_qty = IF( (it_stock_qty - {$it['ct_qty']}) > 0, (it_stock_qty - {$it['ct_qty']}), 0 )
                    where it_id = '{$it['it_id']}' ";
        sql_query($sql);
    }
}

// 쿠폰사용내역기록
if($is_member) {
    $cp_count = count($arr_item_coupon);
    for($i=0; $i<$cp_count; $i++) {
        // 쿠폰내역기록
        $sql = " insert into {$g4['yc4_coupon_history_table']}
                    set cp_id       = '{$arr_item_coupon[$i]['cp_id']}',
                        cp_subject  = '{$arr_item_coupon[$i]['cp_subject']}',
                        mb_id       = '{$member['mb_id']}',
                        it_id       = '{$arr_item_coupon[$i]['it_id']}',
                        ct_id       = '{$arr_item_coupon[$i]['ct_id']}',
                        uq_id       = '$od_id',
                        ch_amount   = '{$arr_item_coupon[$i]['ch_amount']}',
                        ch_datetime = '{$g4['time_ymdhis']}' ";
        sql_query($sql);

        // cart 테이블에 쿠폰금액기록
        $sql = " update {$g4['yc4_cart_table']}
                    set cp_amount   = '{$arr_item_coupon[$i]['ch_amount']}'
                    where ct_id = '{$arr_item_coupon[$i]['ct_id']}' ";
        sql_query($sql);
    }

    // 배송비쿠폰내역
    if($arr_send_coupon['cp_id']) {
        $sql = " insert into {$g4['yc4_coupon_history_table']}
                    set cp_id       = '{$arr_send_coupon['cp_id']}',
                        cp_subject  = '{$arr_send_coupon['cp_subject']}',
                        mb_id       = '{$member['mb_id']}',
                        it_id       = '',
                        ct_id       = '',
                        uq_id       = '$od_id',
                        ch_amount   = '{$arr_send_coupon['ch_amount']}',
                        ch_datetime = '{$g4['time_ymdhis']}' ";
         sql_query($sql);
    }

    // 결제할인쿠폰내역
    if($arr_order_coupon['cp_id']) {
        $sql = " insert into {$g4['yc4_coupon_history_table']}
                    set cp_id       = '{$arr_order_coupon['cp_id']}',
                        cp_subject  = '{$arr_order_coupon['cp_subject']}',
                        mb_id       = '{$member['mb_id']}',
                        it_id       = '',
                        ct_id       = '',
                        uq_id       = '$od_id',
                        ch_amount   = '{$arr_order_coupon['ch_amount']}',
                        ch_datetime = '{$g4['time_ymdhis']}' ";
         sql_query($sql);
    }
}

// 회원이면서 포인트를 사용했다면 포인트 테이블에 사용을 추가
if ($is_member && $od_receipt_point) {
    insert_point($member['mb_id'], (-1) * $od_receipt_point, "주문번호 $od_id 결제");
}

$od_memo = nl2br(htmlspecialchars2(stripslashes($od_memo))) . "&nbsp;";


//include_once("./ordermail1.inc.php");

if ($od_settle_case == "무통장")
//    include_once("./ordermail2.inc.php");

// SMS BEGIN --------------------------------------------------------
// 쇼핑몰 운영자가 수신자가 됨
$receive_number = preg_replace("/[^0-9]/", "", $default['de_sms_hp']); // 수신자번호
$send_number = preg_replace("/[^0-9]/", "", $od_hp); // 발신자번호

$sms_contents = $default['de_sms_cont2'];
$sms_contents = preg_replace("/{이름}/", $od_name, $sms_contents);
$sms_contents = preg_replace("/{보낸분}/", $od_name, $sms_contents);
$sms_contents = preg_replace("/{받는분}/", $od_b_name, $sms_contents);
$sms_contents = preg_replace("/{주문번호}/", $od_id, $sms_contents);
$sms_contents = preg_replace("/{주문금액}/", number_format($ttotal_amount), $sms_contents);
$sms_contents = preg_replace("/{회원아이디}/", $member['mb_id'], $sms_contents);
$sms_contents = preg_replace("/{회사명}/", $default['de_admin_company_name'], $sms_contents);

if ($default['de_sms_use2'] && $receive_number)
{
    include_once("$g4[path]/lib/icode.sms.lib.php");
    $SMS = new SMS; // SMS 연결
    $SMS->SMS_con($default['de_icode_server_ip'], $default['de_icode_id'], $default['de_icode_pw'], $default['de_icode_server_port']);
    $SMS->Add($receive_number, $send_number, $default['de_icode_id'], stripslashes($sms_contents), "");
    $SMS->Send();
}
// SMS END   --------------------------------------------------------


// 세션값 제거
if($w != "selectedbuy")
    set_session('ss_uniqid', '');
set_session('ss_order_uniqid', '');
set_session('ss_direct', '');

// 비회원 장바구니 uq_id 쿠키제거
if($default['de_guest_cart_use']) {
    if(get_cookie('ck_guest_cart_uqid')) {
        set_cookie('ck_guest_cart_uqid', '', 0);
    }
}

// inquiryview 에서 사용함
set_session('ss_inquiry_uniqid', $od_id);
set_session('ss_inquiry_direct', $sw_direct);

goto_url("{$g4['url']}/{$g4['shop']}/orderinquiryview.php?od_id=$od_id");
?>

<html>
    <head>
        <title>*** KCP [AX-HUB Version] ***</title>
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