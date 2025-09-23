<?php
if (!defined('_GNUBOARD_')) exit;

include_once(dirname(__FILE__) .'/form.nonce.lib.php');

function get_dir_path($pathComponents, $is_end = 0)
{

    $dir_separator = '/';       // DIRECTORY_SEPARATOR
    $filteredComponents = array_filter($pathComponents);

    return implode($dir_separator, $filteredComponents) . ($is_end ? $dir_separator : '');
}

function subscription_category_url($ca_id, $add_param = '')
{
    global $config;

    if ($config['cf_bbs_rewrite']) {
        // return get_pretty_url('shop', 'list-'.$ca_id, $add_param);
    }

    $add_params = $add_param ? '&' . $add_param : '';
    return G5_SUBSCRIPTION_URL . '/list.php?ca_id=' . urlencode($ca_id) . $add_params;
}

function subscription_item_url($it_id, $add_param = '')
{
    global $config;

    if ($config['cf_bbs_rewrite']) {
        // return get_pretty_url('shop', $it_id, $add_param);
    }

    $add_params = $add_param ? '&' . $add_param : '';
    return G5_SUBSCRIPTION_URL . '/item.php?it_id=' . urlencode($it_id) . $add_params;
}

// cart id 설정
function set_subscription_cart_id($direct)
{
    global $g5, $default, $member;

    if ($direct) {
        $tmp_cart_id = get_session('subs_cart_direct');
        if (!$tmp_cart_id) {
            $tmp_cart_id = get_uniqid();
            set_session('subs_cart_direct', $tmp_cart_id);
        }
    } else {
        // 비회원장바구니 cart id 쿠키설정
        if ($default['de_guest_cart_use']) {
            $tmp_cart_id = preg_replace('/[^a-z0-9_\-]/i', '', get_cookie('ck_guest_cart_id'));
            if ($tmp_cart_id) {
                set_session('subs_cart_id', $tmp_cart_id);
                //set_cookie('ck_guest_cart_id', $tmp_cart_id, ($default['de_cart_keep_term'] * 86400));
            } else {
                $tmp_cart_id = get_uniqid();
                set_session('subs_cart_id', $tmp_cart_id);
                set_cookie('ck_guest_cart_id', $tmp_cart_id, ($default['de_cart_keep_term'] * 86400));
            }
        } else {
            $tmp_cart_id = get_session('subs_cart_id');
            if (!$tmp_cart_id) {
                $tmp_cart_id = get_uniqid();
                set_session('subs_cart_id', $tmp_cart_id);
            }
        }

        // 보관된 회원장바구니 자료 cart id 변경
        if ($member['mb_id'] && $tmp_cart_id) {
            $sql = " update {$g5['g5_subscription_cart_table']}
                        set od_id = '$tmp_cart_id'
                        where mb_id = '{$member['mb_id']}'
                          and ct_direct = '0'
                          and ct_status = '쇼핑' ";
            sql_query($sql);
        }
    }
}

function isValidDate($dateString)
{
    $timestamp = strtotime($dateString);

    // strtotime이 false가 아니고, 원본 문자열과 변환된 날짜가 일치하는지 확인
    return $timestamp && date('Y-m-d', $timestamp) === $dateString;
}

function isValidDate_ymd($dateStr) {
    // 2자리 또는 4자리 연도 + '-' 구분자 검사
    if (preg_match('/^(\d{2}|\d{4})-(\d{2})-(\d{2})/', $dateStr, $matches)) {
        $year = (int) $matches[1];
        $month = (int) $matches[2];
        $day = (int) $matches[3];

        // 2자리 연도일 경우 보정
        if ($year < 100) {
            $year += 2000;
        }

        // checkdate는 달, 일, 년 순서
        return checkdate($month, $day, $year);
    }

    return false;
}

function get_subscriptionDayOfWeek($opt_etc)
{

    if (!$opt_etc) {
        return '';
    }

    $days = array(
        'sun' => '일요일',
        'mon' => '월요일',
        'tue' => '화요일',
        'wed' => '수요일',
        'thu' => '목요일',
        'fri' => '금요일',
        'sat' => '토요일'
    );

    // $opt_etc이 제공되고 유효한 키일 경우
    if (!empty($opt_etc) && array_key_exists(strtolower($opt_etc), $days)) {
        return $days[strtolower($opt_etc)];
    }
}

function get_subscriptionMonthDay($int_day, $is_month = 0)
{

    $timestamp = strtotime($date);

    if ($int_day > 0) {
        // 다음 달의 $int_day일로 이동
        $timestamp = strtotime("+$int_day days", strtotime("first day of next month", $timestamp));
    }

    if ($is_month) {
        return date('m월 d일', $timestamp);
    }

    return date('d일', $timestamp);
}

// 정기결제 장바구니 건수 검사
function get_subscription_cart_count($cart_id)
{
    global $g5, $default;

    $sql = "SELECT count(ct_id) as cnt FROM {$g5['g5_subscription_cart_table']} 
            WHERE od_id = '" . sql_real_escape_string($cart_id) . "'";
    $row = sql_fetch($sql);
    /*
    $sql = " select count(ct_id) as cnt from {$g5['g5_subscription_cart_table']} where od_id = '$cart_id' ";
    $row = sql_fetch($sql);
    */

    return isset($row['cnt']) ? (int) $row['cnt'] : 0;
}

function subscription_print_item_options($it_id, $cart_id, $is_get = 0)
{
    global $g5;

    $sql = "SELECT ct_price, ct_option, ct_qty, io_price, io_type 
            FROM {$g5['g5_subscription_cart_table']} 
            WHERE it_id = '" . sql_real_escape_string($it_id) . "' 
            AND od_id = '" . sql_real_escape_string($cart_id) . "' 
            ORDER BY io_type ASC, ct_id ASC";
    $results = sql_query($sql);
    $subscription_carts = sql_result_array($results);

    $str = '';
    $i = 0;

    $datas =  array();

    foreach ($subscription_carts as $row) {
        if ($i == 0) {
            $str .= '<ul>' . PHP_EOL;
        }

        $price_plus = '';

        if ($row['io_price'] >= 0) {
            $price_plus = '+';
        }

        $datas[] = array(
            'ct_option' => $row['ct_option'],
            'ct_qty' => $row['ct_qty'],
            'price_plus' => $price_plus,
            'ct_price' => $row['ct_price'],
            'io_price' => $row['io_price'],
            'opt_price' => $row['io_type'] ? $row['io_price'] : (int) $row['ct_price'] + (int) $row['io_price']  // io_type 이 1이면 추가옵션이며, 0이면 선택옵션이다
        );

        $str .= '<li>' . get_text($row['ct_option']) . ' ' . $row['ct_qty'] . '개 (' . $price_plus . display_price($row['io_price']) . ')</li>' . PHP_EOL;

        $i++;
    }

    if ($is_get) {
        return $datas;
    }

    if ($i > 0)
        $str .= '</ul>';

    return $str;
}

// 장바구니 금액 체크 $is_price_update 가 true 이면 장바구니 가격 업데이트한다. 
function before_check_subscription_cart_price($s_cart_id, $is_ct_select_condition=false, $is_price_update=false, $is_item_cache=false, $is_stock_check=false)
{
    global $g5, $default, $config;

    if (!$s_cart_id) return;

    $sql = "SELECT * FROM {$g5['g5_subscription_cart_table']} 
            WHERE od_id = '" . sql_real_escape_string($s_cart_id) . "'";
            
    if ($is_ct_select_condition) {
        $sql .= " AND ct_select = '0'";
    }
    
    $results = sql_query($sql);
    $rows = sql_result_array($results);

    $check_need_update = false;

    foreach ($rows as $row) {
        if (!$row['it_id']) continue;

        $it_id = $row['it_id'];
        $it = get_shop_item($it_id, $is_item_cache);
        
        $update_querys = array();

        if (!(isset($it['it_id']) && $it['it_id'])) continue;
        
        // 재고를 체크한다면
        if ($row['ct_status'] !== '쇼핑' && $is_stock_check) {
            $ct_qty = (int) $row['ct_qty'];
            $stock_msgs = array();
            $item_option = $row['it_name'] . ($row['io_id'] ? "({$row['ct_option']})" : '');

            // 재고 수량 확인
            $it_stock_qty = !$row['io_id']
                ? get_it_stock_qty($row['it_id'])
                : get_option_stock_qty($row['it_id'], $row['io_id'], $row['io_type']);

            $is_sold_out = $it['it_soldout'] || !$it['it_use'];
            $has_enough_stock = $ct_qty <= $it_stock_qty;

            if ($row['ct_select']) {
                if ($is_sold_out) {
                    $update_querys['ct_select'] = 0;
                    $stock_msgs[] = "{$row['it_name']} 상품이 품절 또는 판매중지 되어 장바구니에서 제외됩니다.";
                } elseif (!$has_enough_stock) {
                    $update_querys['ct_select'] = 0;
                    $stock_msgs[] = "{$item_option} 의 재고수량이 부족합니다. 현재 재고수량 : " . number_format($it_stock_qty) . " 개";
                }

                if ($stock_msgs) {
                    add_subscription_order_history(implode("\n", $stock_msgs), array(
                        'hs_type' => 'subscription_no_stock',
                        'od_id'   => $s_cart_id,
                        'mb_id'   => $row['mb_id']
                    ));
                }

            } else {
                // 주문한 내역에서 재고 있으면 
                if (!$is_sold_out && $has_enough_stock) {
                    $update_querys['ct_select'] = 1;
                    // 필요한 경우 메시지 기록 가능
                }
            }
        }
        
        if ((int) $it['it_price'] !== (int) $row['ct_price']) {
            // 장바구니 테이블 상품 가격과 상품 테이블의 상품 가격이 다를경우
            $update_querys['ct_price'] = $it['it_price'];
        }

        if ($row['io_id']) {
            $io_sql = " select * from {$g5['g5_shop_item_option_table']} where it_id = '{$it['it_id']}' and io_id = '{$row['io_id']}' ";
            $io_infos = sql_fetch($io_sql);

            if ($io_infos['io_type']) {
                $this_io_type = $io_infos['io_type'];
            }

            if ($io_infos['io_id'] && (int) $io_infos['io_price'] !== (int) $row['io_price']) {
                // 장바구니 테이블 옵션 가격과 상품 옵션테이블의 옵션 가격이 다를경우
                $update_querys['io_price'] = $io_infos['io_price'];
            }
        }

        // 포인트
        $compare_point = 0;
        if ($config['cf_use_point']) {
            
            // DB 에 io_type 이 1이면 상품추가옵션이며, 0이면 상품선택옵션이다
            if ($row['io_type'] == 0) {
                $compare_point = get_item_point($it, $row['io_id']);
            } else {
                $compare_point = $it['it_supply_point'];
            }

            if ($compare_point < 0)
                $compare_point = 0;
        }

        if ((int) $row['ct_point'] !== (int) $compare_point) {

            // 장바구니 테이블 적립 포인트와 상품 테이블의 적립 포인트가 다를경우
            $update_querys['ct_point'] = $compare_point;
        }

        if ($update_querys) {
            $check_need_update = true;
        }

        // 장바구니에 담긴 금액과 실제 상품 금액에 차이가 있고, $is_price_update 가 true 인 경우 장바구니 금액을 업데이트 합니다. 
        if ($is_price_update && $update_querys) {
            $conditions = array();

            foreach ($update_querys as $column => $value) {
                $conditions[] = "`{$column}` = '{$value}'";
            }

            if ($col_querys = implode(',', $conditions)) {
                
                $sql_query = "update `{$g5['g5_subscription_cart_table']}` set {$col_querys} where it_id = '{$it['it_id']}' and od_id = '$s_cart_id' and ct_id =  '{$row['ct_id']}' ";

                sql_query($sql_query, false);
            }
        }
    }

    // 장바구니에 담긴 금액과 실제 상품 금액에 차이가 있다면
    if ($check_need_update) {
        return false;
    }

    return true;
}

function subscription_order_pay_price($od_id)
{
    global $g5;
    
    $pay_infos = get_subscription_cart_data($od_id);

    $total_price = (int)$pay_infos['tot_sell_price'] + (int)$pay_infos['send_cost'];
    
    // 정기결제할인 쿠폰이 있는지 체크
    $od = get_subscription_order($od_id);
    
    $couponprice = $od['od_cart_coupon'] + $od['od_coupon'] + $od['od_send_coupon'];
    
    if ($couponprice > 0) {
        $sql = " select cp_id, cp_price from {$g5['g5_subscription_coupon_log_table']} where mb_id = '{$od['mb_id']}' and od_id = '$od_id' ";
        $result = sql_query($sql);
        
        $cp_ids = array();
        $cp_ids_price = array();
        
        $total_cp_price = 0;
        
        for($i=0; $row=sql_fetch_array($result); $i++) {
            $cp_ids[] = $row['cp_id'];
            $cp_ids_price[] = $row['cp_price'];
            
            $total_cp_price += $row['cp_price'];
        }
        
        run_event('event_subscription_order_pay_price', $od_id, $od, $cp_ids, $total_cp_price);
        
        $total_price = $total_price - $total_cp_price;
    }
    
    return $total_price;
}

function get_subscription_cart_data($s_cart_id, $is_pay = 0)
{
    global $g5;

    // $s_cart_id 로 현재 장바구니 자료 쿼리
    $sql = "SELECT a.ct_id, a.it_id, a.it_name, a.ct_price, a.ct_point, a.ct_qty, a.ct_status, a.ct_send_cost, a.it_sc_type, 
                b.ca_id, b.ca_id2, b.ca_id3, b.it_notax 
            FROM {$g5['g5_subscription_cart_table']} a 
            LEFT JOIN {$g5['g5_shop_item_table']} b ON (a.it_id = b.it_id) 
            WHERE a.od_id = '" . sql_real_escape_string($s_cart_id) . "' 
            AND a.ct_select = '1' 
            GROUP BY a.it_id 
            ORDER BY a.ct_id";
    $results = sql_query($sql);
    $rows = sql_result_array($results);

    $goods = array();
    $images = array();
    $it_options = array();

    $tot_point = 0;
    $tot_sell_price = 0;

    $good_info = '';
    $it_send_cost = 0;
    $it_cp_count = 0;

    $comm_tax_mny = 0; // 과세금액
    $comm_vat_mny = 0; // 부가세
    $comm_free_mny = 0; // 면세금액
    $tot_tax_mny = 0;

    foreach ($rows as $row) {
        $sql = "SELECT SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price, 
                    SUM(ct_point * ct_qty) as point, 
                    SUM(ct_qty) as qty 
                FROM {$g5['g5_subscription_cart_table']} 
                WHERE it_id = '" . sql_real_escape_string($row['it_id']) . "' 
                AND od_id = '" . sql_real_escape_string($s_cart_id) . "'";
        $sum = sql_fetch($sql);
        $item_name = preg_replace("/\'|\"|\||\,|\&|\;/", '', $row['it_name']);
        $image_url = get_subscription_it_image($row['it_id'], 80, 80, false, '', '', false, 1);

        // if (!in_array($item_name, $goods)) {
        //    $goods[] = $item_name;
        // }

        $image_urls[] = $image_url;
        // $link_urls[] = // 상품 url
        $goods[] = $item_name;

        $it_options[] = subscription_print_item_options($row['it_id'], $s_cart_id, 1);

        $point = $sum['point'];
        $sell_price = $sum['price'];

        // 배송비
        switch ($row['ct_send_cost']) {
            case 1:
                $ct_send_cost = '착불';
                break;
            case 2:
                $ct_send_cost = '무료';
                break;
            default:
                $ct_send_cost = '선불';
                break;
        }

        // 조건부무료
        if ($row['it_sc_type'] == 2) {
            $sendcost = get_subscription_item_sendcost($row['it_id'], $sum['price'], $sum['qty'], $s_cart_id);

            if ($sendcost == 0) {
                $ct_send_cost = '무료';
            }
        }

        $tot_point += $point;
        $tot_sell_price += $sell_price;
    }

    // 배송비 계산
    $send_cost = get_subscription_sendcost($s_cart_id);

    return array('goods' => $goods, 'image_urls' => $image_urls, 'it_options' => $it_options, 'tot_point' => $tot_point, 'tot_sell_price' => $tot_sell_price, 'send_cost' => $send_cost);

}

// 정기결제 상품의 재고 (창고재고수량 - 주문대기수량)
function get_subscription_it_stock_qty($it_id)
{

    return get_it_stock_qty($it_id);
}

// 배송비 구함
function get_subscription_sendcost($cart_id, $selected = 1)
{
    global $default, $g5;

    $send_cost = 0;
    $total_price = 0;
    $total_send_cost = 0;
    $diff = 0;

    $sql = "SELECT DISTINCT it_id 
            FROM {$g5['g5_subscription_cart_table']} 
            WHERE od_id = '" . $cart_id . "' 
            AND ct_send_cost = '0' 
            AND ct_select = '" . $selected . "'";
    $result = sql_query($sql);

    for ($i = 0; $sc = sql_fetch_array($result); $i++) {
        // 합계
        $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
                        SUM(ct_qty) as qty
                    from {$g5['g5_subscription_cart_table']}
                    where it_id = '{$sc['it_id']}'
                      and od_id = '$cart_id'
                      and ct_status IN ( '쇼핑', '주문', '입금', '준비', '배송', '완료' )
                      and ct_select = '$selected'";
        $sum = sql_fetch($sql);

        $send_cost = get_subscription_item_sendcost($sc['it_id'], $sum['price'], $sum['qty'], $cart_id);

        if ($send_cost > 0)
            $total_send_cost += $send_cost;

        if ($default['de_send_cost_case'] == '차등' && $send_cost == -1) {
            $total_price += $sum['price'];
            $diff++;
        }
    }

    $send_cost = 0;
    if ($default['de_send_cost_case'] == '차등' && $total_price >= 0 && $diff > 0) {
        // 금액별차등 : 여러단계의 배송비 적용 가능
        $send_cost_limit = explode(";", $default['de_send_cost_limit']);
        $send_cost_list  = explode(";", $default['de_send_cost_list']);
        $send_cost = 0;
        for ($k = 0; $k < count($send_cost_limit); $k++) {
            // 총판매금액이 배송비 상한가 보다 작다면
            if ($total_price < preg_replace('/[^0-9]/', '', $send_cost_limit[$k])) {
                $send_cost = preg_replace('/[^0-9]/', '', $send_cost_list[$k]);
                break;
            }
        }
    }

    return ($total_send_cost + $send_cost);
}

// 상품별 배송비
function get_subscription_item_sendcost($it_id, $price, $qty, $cart_id)
{
    global $g5, $default;

    $sql = " select it_id, it_sc_type, it_sc_method, it_sc_price, it_sc_minimum, it_sc_qty
                from {$g5['g5_subscription_cart_table']}
                where it_id = '$it_id'
                  and od_id = '$cart_id'
                order by ct_id
                limit 1 ";
    $ct = sql_fetch($sql);
    if (!$ct['it_id'])
        return 0;

    if ($ct['it_sc_type'] > 1) {
        if ($ct['it_sc_type'] == 2) { // 조건부무료
            if ($price >= $ct['it_sc_minimum'])
                $sendcost = 0;
            else
                $sendcost = $ct['it_sc_price'];
        } else if ($ct['it_sc_type'] == 3) { // 유료배송
            $sendcost = $ct['it_sc_price'];
        } else { // 수량별 부과
            if (!$ct['it_sc_qty'])
                $ct['it_sc_qty'] = 1;

            $q = ceil((int)$qty / (int)$ct['it_sc_qty']);
            $sendcost = (int)$ct['it_sc_price'] * $q;
        }
    } else if ($ct['it_sc_type'] == 1) { // 무료배송
        $sendcost = 0;
    } else {
        $sendcost = -1;
    }

    return $sendcost;
}

// 정기결제 옵션의 재고 (창고재고수량 - 주문대기수량)
function get_subscription_option_stock_qty($it_id, $io_id, $type)
{

    return get_option_stock_qty($it_id, $io_id, $type);
}

// 정기결제 장바구니 상품삭제
function subscription_cart_item_clean()
{
    global $g5, $default;

    // 장바구니 보관일
    $keep_term = $default['de_cart_keep_term'];

    if (!$keep_term)
        $keep_term = 15; // 기본값 15일

    // ct_select_time이 기준시간 이상 경과된 경우 변경
    if (defined('G5_CART_STOCK_LIMIT'))
        $cart_stock_limit = G5_CART_STOCK_LIMIT;
    else
        $cart_stock_limit = 3;

    $stocktime = 0;
    if ($cart_stock_limit > 0) {
        if ($cart_stock_limit > $keep_term * 24)
            $cart_stock_limit = $keep_term * 24;

        $stocktime = G5_SERVER_TIME - (3600 * $cart_stock_limit);
        $sql = " update {$g5['g5_subscription_cart_table']}
                    set ct_select = '0'
                    where ct_select = '1'
                      and ct_status = '쇼핑'
                      and UNIX_TIMESTAMP(ct_select_time) < '$stocktime' ";
        sql_query($sql);
    }

    // 설정 시간이상 경과된 상품 삭제
    $statustime = G5_SERVER_TIME - (86400 * $keep_term);

    $sql = " delete from {$g5['g5_subscription_cart_table']}
                where ct_status = '쇼핑'
                  and UNIX_TIMESTAMP(ct_time) < '$statustime' ";

    sql_query($sql);
}

function subscription_is_soldout($it_id, $is_cache = false)
{
    return is_soldout($it_id, $is_cache);
}

function subscription_member_cert_check($id, $type)
{
    global $g5, $member;

    $msg = '';

    return $msg;
}

function get_subscription_category($ca_id)
{
    global $g5, $g5_object;

    $add_query = '';

    $sql = " select * from {$g5['g5_shop_category_table']} where ca_id = '{$ca_id}' $add_query ";
    return sql_fetch($sql);
}

function get_subscription_order($od_id, $is_select_memeber = 0)
{
    global $g5, $is_member, $member;

    $sql = "SELECT * FROM {$g5['g5_subscription_order_table']} 
            WHERE od_id = '" . sql_real_escape_string($od_id) . "'";

    if ($is_select_memeber) {
        if (!$is_member) {
            return null;
        }
        $sql .= " AND mb_id = '" . sql_real_escape_string($member['mb_id']) . "'";
    }

    return sql_fetch($sql);
}

function get_subscription_item($it_id, $is_cache = false, $add_query = '')
{
    global $g5, $g5_object;

    $add_query_key = $add_query ? 'subscription_' . md5($add_query) : '';

    $item = $is_cache ? $g5_object->get('subscription', $it_id, $add_query_key) : null;

    if (!$item) {
        $sql = " select * from {$g5['g5_shop_item_table']} where it_id = '{$it_id}' and it_class_num IN (1, 2) $add_query ";
        $item = sql_fetch($sql);

        $g5_object->set('subscription', $it_id, $item, $add_query_key);
    }

    if (isset($item['it_basic'])) {
        $item['it_basic'] = conv_content($item['it_basic'], 1);
    }

    if (! isset($item['it_id'])) {
        $item['it_id'] = '';
    }

    return $item;
}

function get_subscription_item_with_category($it_id, $seo_title = '', $add_query = '')
{

    global $g5, $default;

    if ($seo_title) {
        $sql = " select a.*, b.ca_name, b.ca_use from {$g5['g5_shop_item_table']} a, {$g5['g5_shop_category_table']} b where a.it_seo_title = '" . sql_real_escape_string(generate_seo_title($seo_title)) . "' and a.ca_id = b.ca_id $add_query";
    } else {
        $sql = " select a.*, b.ca_name, b.ca_use from {$g5['g5_shop_item_table']} a, {$g5['g5_shop_category_table']} b where a.it_id = '$it_id' and a.ca_id = b.ca_id $add_query";
    }

    $item = sql_fetch($sql);

    if (isset($item['it_basic'])) {
        $item['it_basic'] = conv_content($item['it_basic'], 1);
    }

    return $item;
}

function subscription_seo_title_update($it_id, $is_edit = false)
{
    global $g5;

    $subscription_item_cache = $is_edit ? false : true;
    $item = get_subscription_item($it_id, $subscription_item_cache);

    if ((! $item['it_seo_title'] || $is_edit) && $item['it_name']) {
        $it_seo_title = exist_seo_title_recursive('subscription', generate_seo_title($item['it_name']), $g5['g5_shop_item_table'], $item['it_id']);

        if (isset($item['it_seo_title']) && $it_seo_title !== $item['it_seo_title']) {
            $sql = " update `{$g5['g5_shop_item_table']}` set it_seo_title = '{$it_seo_title}' where it_id = '{$item['it_id']}' ";
            sql_query($sql);
        }
    }
}

class SubscriptionList extends item_list
{

    // $type        : 상품유형 (기본으로 1~5까지 사용)
    // $list_skin   : 상품리스트를 노출할 스킨을 설정합니다. 스킨위치는 skin/shop/쇼핑몰설정스킨/type??.skin.php
    // $list_mod    : 1줄에 몇개의 상품을 노출할지를 설정합니다.
    // $list_row    : 상품을 몇줄에 노출할지를 설정합니다.
    // $img_width   : 상품이미지의 폭을 설정합니다.
    // $img_height  : 상품이미지의 높이을 설정합니다. 0 으로 설정하는 경우 썸네일 이미지의 높이는 폭에 비례하여 생성합니다.
    //function __construct($type=0, $list_skin='', $list_mod='', $list_row='', $img_width='', $img_height=0, $ca_id='') {
    function __construct($list_skin = '', $list_mod = '', $list_row = '', $img_width = '', $img_height = 0)
    {
        parent::__construct($list_skin, $list_mod, $list_row, $img_width, $img_height);
        $this->set_href(G5_SUBSCRIPTION_URL . '/item.php?it_id=');
        $this->count++;
    }

    // 리스트 스킨을 바꾸고자 하는 경우에 사용합니다.
    // 리스트 스킨의 위치는 skin/shop/쇼핑몰설정스킨/type??.skin.php 입니다.
    // 특별히 설정하지 않는 경우 상품유형을 사용하는 경우는 쇼핑몰설정 값을 그대로 따릅니다.
    function set_list_skin($list_skin)
    {
        global $default;
        if ($this->is_mobile) {
            $this->list_skin = $list_skin ? $list_skin : G5_MSUBSCRIPTION_SKIN_PATH . '/' . preg_replace('/[^A-Za-z0-9 _ .-]/', '', $default['de_mobile_type' . $this->type . '_list_skin']);
        } else {
            $this->list_skin = $list_skin ? $list_skin : G5_SUBSCRIPTION_SKIN_PATH . '/' . preg_replace('/[^A-Za-z0-9 _ .-]/', '', $default['de_type' . $this->type . '_list_skin']);
        }
    }

    // 외부에서 쿼리문을 넘겨줄 경우에 담아둡니다.
    function set_query($query = '')
    {

        global $g5, $config, $member, $default;

        if ($query) {
            $this->query = $query;
        } else {

            $where = array();
            if ($this->use) {
                $where[] = " it_use = '1' ";
            }

            if ($this->type) {
                $where[] = " it_type{$this->type} = '1' ";
            }

            $where[] = " it_class_num IN (1, 2) ";

            if ($this->ca_id || $this->ca_id2 || $this->ca_id3) {
                $where_ca_id = array();
                if ($this->ca_id) {
                    $where_ca_id[] = " ca_id like '{$this->ca_id}%' ";
                }
                if ($this->ca_id2) {
                    $where_ca_id[] = " ca_id2 like '{$this->ca_id2}%' ";
                }
                if ($this->ca_id3) {
                    $where_ca_id[] = " ca_id3 like '{$this->ca_id3}%' ";
                }
                $where[] = " ( " . implode(" or ", $where_ca_id) . " ) ";
            }

            if ($this->order_by) {
                $sql_order = " order by {$this->order_by} ";
            }

            if ($this->event) {
                $sql_select = " select {$this->fields} ";
                $sql_common = " from `{$g5['g5_shop_event_item_table']}` a left join `{$g5['g5_shop_item_table']}` b on (a.it_id = b.it_id) ";
                $where[] = " a.ev_id = '{$this->event}' ";
            } else {
                $sql_select = " select {$this->fields} ";
                $sql_common = " from `{$g5['g5_shop_item_table']}` ";
            }
            $sql_where = " where " . implode(" and ", $where);
            $sql_limit = " limit " . $this->from_record . " , " . ($this->list_mod * $this->list_row);

            $this->query = $sql_select . $sql_common . $sql_where . $sql_order . $sql_limit;
        }
    }
}

// 상품이미지에 유형 아이콘 출력
function subscription_item_icon($it)
{
    global $g5;

    $icon = '<span class="sit_icon">';

    if ($it['it_type1'])
        $icon .= '<span class="shop_icon shop_icon_1">히트</span>';

    if ($it['it_type2'])
        $icon .= '<span class="shop_icon shop_icon_2">추천</span>';

    if ($it['it_type3'])
        $icon .= '<span class="shop_icon shop_icon_3">최신</span>';

    if ($it['it_type4'])
        $icon .= '<span class="shop_icon shop_icon_4">인기</span>';

    if ($it['it_type5'])
        $icon .= '<span class="shop_icon shop_icon_5">할인</span>';


    // 쿠폰상품
    $sql = " select count(*) as cnt
                from {$g5['g5_shop_coupon_table']}
                where cp_start <= '" . G5_TIME_YMD . "'
                  and cp_end >= '" . G5_TIME_YMD . "'
                  and (
                        ( cp_method = '0' and cp_target = '{$it['it_id']}' )
                        OR
                        ( cp_method = '1' and ( cp_target IN ( '{$it['ca_id']}', '{$it['ca_id2']}', '{$it['ca_id3']}' ) ) )
                      ) ";
    $row = sql_fetch($sql);
    if ($row['cnt'])
        $icon .= '<span class="shop_icon shop_icon_coupon">쿠폰</span>';

    $icon .= '</span>';

    return $icon;
}

// 상품 이미지를 얻는다
function get_subscription_it_image($it_id, $width, $height = 0, $anchor = false, $img_id = '', $img_alt = '', $is_crop = false, $is_array = 0)
{
    global $g5;

    if (!$it_id || !$width)
        return '';

    $row = get_subscription_item($it_id, true);

    if (!$row['it_id'])
        return '';

    $filename = $thumb = $img = '';

    $img_width = 0;
    for ($i = 1; $i <= 10; $i++) {
        $file = G5_DATA_PATH . '/item/' . $row['it_img' . $i];
        if (is_file($file) && $row['it_img' . $i]) {
            $size = @getimagesize($file);
            if (! isset($size[2]) || $size[2] < 1 || $size[2] > 3)
                continue;

            $filename = basename($file);
            $filepath = dirname($file);
            $img_width = $size[0];
            $img_height = $size[1];

            break;
        }
    }

    if ($img_width && !$height) {
        $height = round(($width * $img_height) / $img_width);
    }

    if ($filename) {
        $thumb = thumbnail($filename, $filepath, $filepath, $width, $height, false, $is_crop, 'center', false, $um_value = '80/0.5/3');
    }

    $file_url = '';

    if ($thumb) {
        $file_url = str_replace(G5_PATH, G5_URL, $filepath . '/' . $thumb);
        $img = '<img src="' . $file_url . '" width="' . $width . '" height="' . $height . '" alt="' . $img_alt . '"';
    } else {
        $img = '<img src="' . G5_SHOP_URL . '/img/no_image.gif" width="' . $width . '"';
        if ($height)
            $img .= ' height="' . $height . '"';
        $img .= ' alt="' . $img_alt . '"';
    }

    if ($img_id)
        $img .= ' id="' . $img_id . '"';
    $img .= '>';

    if ($anchor)
        $img = $img = '<a href="' . subscription_item_url($it_id) . '">' . $img . '</a>';

    if ($is_array) {
        return run_replace('get_subscription_it_image_tag_array', array(
            'img' => $img,
            'file_url' => $file_url,
            'width' => $width,
            'height' => $height
        ), $thumb, $it_id, $width, $height, $anchor, $img_id, $img_alt, $is_crop, $file_url);
    }

    return run_replace('get_subscription_it_image_tag', $img, $thumb, $it_id, $width, $height, $anchor, $img_id, $img_alt, $is_crop, $file_url);
}

function get_subscription_navigation_data($is_cache, $ca_id, $ca_id2 = '', $ca_id3 = '')
{

    $all_categories = get_subscription_category_array($is_cache);

    $datas = array();

    if (strlen($ca_id) >= 2 && $all_categories) {
        foreach ((array) $all_categories as $category1) {
            $datas[0][] = $category1['text'];
        }
    }

    $select_ca_id = $ca_id2 ? $ca_id2 : $ca_id;
    $item_categories2 = $select_ca_id ? get_subscription_category_by($is_cache, 'ca_id', $select_ca_id) : array();

    if (strlen($select_ca_id) >= 4 && $item_categories2) {
        foreach ((array) $item_categories2 as $key => $category2) {
            if ($key === 'text') continue;

            $datas[1][] = $category2['text'];
        }
    }

    $select_ca_id = $ca_id3 ? $ca_id3 : $ca_id;
    $item_categories3 = $select_ca_id ? get_subscription_category_by($is_cache, 'ca_id', $select_ca_id) : array();

    if (strlen($select_ca_id) >= 6 && $item_categories3 && isset($item_categories3[substr($select_ca_id, 0, 4)])) {
        $sub_categories = $item_categories3[substr($select_ca_id, 0, 4)];

        foreach ((array) $sub_categories as $key => $category3) {
            if ($key === 'text') continue;

            $datas[2][] = $category3['text'];
        }
    }

    return $datas;
}

function get_subscription_category_by($is_cache, $case, $value)
{

    if ($case === 'ca_id') {
        $categories = get_subscription_category_array($is_cache);

        $key = substr(preg_replace('/[^0-9a-z]/i', '', $value), 0, 2);

        if (isset($categories[$key])) {
            return $categories[$key];
        }
    }

    return array();
}

function get_subscription_category_array($is_cache = false)
{

    static $categories = array();

    $categories = run_replace('get_subscription_category_array', $categories, $is_cache);

    if ($is_cache && !empty($categories)) {
        return $categories;
    }

    $result = sql_query(get_subscription_category_sql('', 2));

    for ($i = 0; $row = sql_fetch_array($result); $i++) {

        $row['url'] = subscription_category_url($row['ca_id']);
        $categories[$row['ca_id']]['text'] = $row;

        if ($row['ca_id']) {
            $result2 = sql_query(get_subscription_category_sql($row['ca_id'], 4));

            for ($j = 0; $row2 = sql_fetch_array($result2); $j++) {

                $row2['url'] = subscription_category_url($row2['ca_id']);
                $categories[$row['ca_id']][$row2['ca_id']]['text'] = $row2;

                if ($row2['ca_id']) {
                    $result3 = sql_query(get_subscription_category_sql($row2['ca_id'], 6));
                    for ($k = 0; $row3 = sql_fetch_array($result3); $k++) {

                        $row3['url'] = subscription_category_url($row3['ca_id']);
                        $categories[$row['ca_id']][$row2['ca_id']][$row3['ca_id']]['text'] = $row3;
                    }
                }   //end if
            }   //end for
        }   //end if
    }   //end for

    return $categories;
}

function get_subscription_category_sql($ca_id, $len)
{
    global $g5;

    $sql = " select * from {$g5['g5_shop_category_table']}
                where ca_use = '1' ";
    if ($ca_id)
        $sql .= " and ca_id like '$ca_id%' ";
    $sql .= " and length(ca_id) = '$len' order by ca_order, ca_id ";

    return $sql;
}

function get_weekend_yoil($date, $full = 0)
{

    return get_yoil($date, $full);
}

// 상품명과 건수를 반환
function get_subscription_goods($cart_id)
{
    global $g5;

    // 상품명만들기
    $sql = "SELECT a.it_id, b.it_name 
            FROM {$g5['g5_subscription_cart_table']} as a 
            JOIN {$g5['g5_shop_item_table']} as b ON a.it_id = b.it_id 
            WHERE a.od_id = '" . sql_real_escape_string($cart_id) . "' 
            ORDER BY a.ct_id 
            LIMIT 1";
    $row = sql_fetch($sql);

    // 상품명에 "(쌍따옴표)가 들어가면 오류 발생함
    $goods['it_id'] = $row['it_id'];
    $goods['full_name'] = addslashes($row['it_name']);
    // 특수문자제거
    $goods['full_name'] = preg_replace("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", "",  $goods['full_name']);

    // 상품건수
    $sql = "SELECT COUNT(*) AS cnt 
            FROM {$g5['g5_subscription_cart_table']} 
            WHERE od_id = '" . sql_real_escape_string($cart_id) . "'";
    $row = sql_fetch($sql);

    $cnt = ($row['cnt']) ? (int) $row['cnt'] - 1 : 0;

    if ($cnt) {
        $goods['full_name'] .= ' 외 ' . $cnt . '건';
    }

    $goods['count'] = $row['cnt'];

    return $goods;
}

function subscription_order_pay($od, $pg_data, $pay_round_no)
{
    global $g5;

    // $od['py_receipt_price'] ?
    // inicis : $pg_data['price']

    // $py_receipt_time = date('Y년m월d일', strtotime($pg_data['payDate'].$pg_data['payTime']));

    $subscription_pg_id = $pg_data['orderId'];
    $paymethod = $pg_data['payMethod'];
    $od_receipt_price = $pg_data['amount'];
    $receipt_url = $pg_data['receiptUrl'];
    $py_cardname = isset($pg_data['card']['cardName']) ? $pg_data['card']['cardName'] : '';
    $py_cardnumber = isset($pg_data['card']['cardNum']) ? $pg_data['card']['cardNum'] : '';
    $py_app_no = $pg_data['py_app_no'];

    if (!$py_cardname) {
        $py_cardname = $pg_data['cardname'];
    }

    if (!$py_cardnumber) {
        $py_cardnumber = $pg_data['cardnumber'];
    }

    // 나이스페이인 경우 메뉴얼 : https://github.com/nicepayments/nicepay-manual/blob/main/api/payment-subscribe.md#%EB%B9%8C%ED%82%A4%EC%8A%B9%EC%9D%B8
    // issuedCashReceipt 현금영수증 발급여부 true:발행 / false:미발행
    // useEscrow 에스크로 거래 여부 false:일반거래 / true:에스크로 거래
    // approveNo 제휴사 승인 번호 신용카드, 계좌이체, 휴대폰
    // balanceAmt 취소 가능 잔액, 부분취소 거래인경우, 전체금액에서 현재까지 취소된 금액을 차감한 금액.
    
    $next_delivery_date = get_subscription_delivery_date($od);
    
    $inserts = array(
        'od_id' => $od['od_id'],
        'mb_id' => $od['mb_id'],
        'ci_id' => $od['ci_id'],
        'subscription_pg_id' => $subscription_pg_id,
        'py_name' => $od['od_name'],
        'py_email' => $od['od_email'],
        'py_tel' => $od['od_tel'],
        'py_hp' => $od['od_hp'],
        'py_b_name' => $od['od_b_name'],
        'py_b_tel' => $od['od_b_tel'],
        'py_b_hp' => $od['od_b_hp'],
        'py_b_zip' => $od['od_b_zip'],
        'py_b_addr1' => $od['od_b_addr1'],
        'py_b_addr2' => $od['od_b_addr2'],
        'py_b_addr3' => $od['od_b_addr3'],
        'py_b_addr_jibeon' => $od['od_b_addr_jibeon'],
        'py_cart_coupon' => $od['od_cart_coupon'],
        'py_send_cost' => $od['od_send_cost'],
        'py_send_cost2' => $od['od_send_cost2'],
        'py_send_coupon' => $od['od_send_coupon'],
        'py_coupon' => $od['od_coupon'],
        // 'py_receipt_price' => $od['od_receipt_price'],
        'py_receipt_price' => $od_receipt_price,
        'py_receipt_time' => G5_TIME_YMDHIS,
        // 'py_settle_case' => 'card',
        'py_settle_case' => $paymethod,
        'py_receipt_url' => $receipt_url,
        'py_test' => $od['od_test'],
        'py_pg' => $od['od_pg'],
        'py_tno' => $pg_data['tid'],
        'py_time' => G5_TIME_YMDHIS,
        'py_app_no' => $py_app_no,
        'py_round_no' => $pay_round_no,
        'py_cart_count' => $od['od_cart_count'],
        'py_cart_price' => $od['od_cart_price'],
        'py_status' => '입금',
        'next_delivery_date' => $next_delivery_date
    );

    $columns = implode(', ', array_keys($inserts));
    $values = implode("', '", array_values($inserts));

    // 주문서에 입력
    $sql = "INSERT INTO `{$g5['g5_subscription_pay_table']}`($columns) VALUES ('$values')";

    $result = sql_query($sql);

    $insert_id = sql_insert_id();

    if ($insert_id) {

        // 상품명만들기
        $sql = "SELECT * 
                FROM {$g5['g5_subscription_cart_table']} 
                WHERE od_id = '" . $od['od_id'] . "' 
                ORDER BY ct_id ASC";
        $result = sql_query($sql);
        
        // 결제된다면 정기결제 상태가 입금이 되어야 함
        $pb_status = '입금';
        
        // 결제 될때 당시 결제된 장바구니 정보를 따로 저장한다. (pay_basket 테이블에)
        for ($i = 0; $row = sql_fetch_array($result); ++$i) {
            $sql = "INSERT INTO {$g5['g5_subscription_pay_basket_table']} 
                    (od_id, pay_id, mb_id, it_id, it_name, it_sc_type, it_sc_method, it_sc_price, it_sc_minimum, it_sc_qty, 
                    pb_status, pb_history, pb_price, pb_point, cp_price, pb_point_use, pb_stock_use, pb_option, pb_qty, 
                    pb_notax, io_id, io_type, io_price, pb_time, pb_ip, pb_send_cost, pb_direct, pb_select, pb_select_time, 
                    pb_subscription_number, pb_firstshipment_date, pb_date_format) 
                    VALUES (
                        '" . $row['od_id'] . "',
                        '" . $insert_id . "',
                        '" . $row['mb_id'] . "',
                        '" . $row['it_id'] . "',
                        '" . $row['it_name'] . "',
                        '" . $row['it_sc_type'] . "',
                        '" . $row['it_sc_method'] . "',
                        '" . $row['it_sc_price'] . "',
                        '" . $row['it_sc_minimum'] . "',
                        '" . $row['it_sc_qty'] . "',
                        '" . $pb_status . "',
                        '" . $row['ct_history'] . "',
                        '" . $row['ct_price'] . "',
                        '" . $row['ct_point'] . "',
                        '" . $row['cp_price'] . "',
                        '" . $row['ct_point_use'] . "',
                        '" . $row['ct_stock_use'] . "',
                        '" . $row['ct_option'] . "',
                        '" . $row['ct_qty'] . "',
                        '" . $row['ct_notax'] . "',
                        '" . $row['io_id'] . "',
                        '" . $row['io_type'] . "',
                        '" . $row['io_price'] . "',
                        '" . $row['ct_time'] . "',
                        '" . $row['ct_ip'] . "',
                        '" . $row['ct_send_cost'] . "',
                        '" . $row['ct_direct'] . "',
                        '" . $row['ct_select'] . "',
                        '" . $row['ct_select_time'] . "',
                        '" . $row['ct_subscription_number'] . "',
                        '" . $row['ct_firstshipment_date'] . "',
                        '" . $row['ct_date_format'] . "'
                    )";
            sql_query($sql);
        }
        
        // 재고가 있다면 재고를 감소한다.
        subscription_pay_process_stock($insert_id);
        
    }

    return $insert_id;
}

function subscription_cron_token()
{
    global $g5;

    $str = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';
    $str .= G5_TABLE_PREFIX . G5_SHOP_TABLE_PREFIX . G5_TOKEN_ENCRYPTION_KEY;

    return md5($str);
}

function getBusinessDaysBefore($date, $businessDays = 0)
{
    $holidays = get_subscription_business_days(); // 공휴일 목록 가져오기
    $exception_dates = get_subscription_exception_dates(); // 예외 영업일 목록 가져오기

    // 기준 날짜를 타임스탬프로 변환
    $timestamp = strtotime($date);
    // 공휴일과 예외 날짜를 연관 배열로 변환 (효율성 개선)
    $holidays = array_flip($holidays);
    $exception_dates = array_flip($exception_dates);

    if ($businessDays > 0) {
        // 지정된 영업일 수만큼 과거로 이동
        while ($businessDays > 0) {
            $timestamp = strtotime('-1 day', $timestamp);
            $dayOfWeek = date('w', $timestamp);
            $formattedDate = date('Y-m-d', $timestamp);
            // 예외 날짜이거나 (주말/공휴일이 아니면) 영업일로 간주
            if (isset($exception_dates[$formattedDate]) || ($dayOfWeek != 0 && $dayOfWeek != 6 && !isset($holidays[$formattedDate]))) {
                $businessDays--;
            }
        }
    } else {
        // 기준 날짜가 영업일이 아니면 연휴 전 마지막 영업일로 조정
        $maxIterations = 10; // 최대 반복 횟수 제한
        $iteration = 0;
        while ($iteration++ < $maxIterations) {
            $dayOfWeek = date('w', $timestamp);
            $formattedDate = date('Y-m-d', $timestamp);
            // 예외 날짜이거나 (주말/공휴일이 아니면) 영업일로 간주
            if (isset($exception_dates[$formattedDate]) || ($dayOfWeek != 0 && $dayOfWeek != 6 && !isset($holidays[$formattedDate]))) {
                break; // 영업일이면 루프 종료
            }
            $timestamp = strtotime('-1 day', $timestamp); // 과거로 이동
        }
    }

    return date('Y-m-d', $timestamp) . ' '. SUBSCRIPTION_DEFAULT_TIME_SUFFIX;
}

function getBusinessDaysNext($date, $businessDays = 0)
{
    $holidays = get_subscription_business_days(); // 공휴일 목록 가져오기
    $exception_dates = get_subscription_exception_dates(); // 예외 영업일 목록 가져오기
    
    // 기준 날짜를 타임스탬프로 변환
    $timestamp = strtotime($date);
    // 공휴일과 예외 날짜를 연관 배열로 변환 (효율성 개선)
    $holidays = array_flip($holidays);
    $exception_dates = array_flip($exception_dates);

    if ($businessDays > 0) {
        // 지정된 영업일 수만큼 미래로 이동
        while ($businessDays > 0) {
            $timestamp = strtotime('+1 day', $timestamp);
            $dayOfWeek = date('w', $timestamp);
            $formattedDate = date('Y-m-d', $timestamp);
            // 예외 날짜이거나 (주말/공휴일이 아니면) 영업일로 간주
            if (isset($exception_dates[$formattedDate]) || ($dayOfWeek != 0 && $dayOfWeek != 6 && !isset($holidays[$formattedDate]))) {
                $businessDays--;
            }
        }
    } else {
        // 기준 날짜가 영업일이 아니면 연휴 후 첫 영업일로 조정
        $maxIterations = 10; // 최대 반복 횟수 제한
        $iteration = 0;
        while ($iteration++ < $maxIterations) {
            $dayOfWeek = date('w', $timestamp);
            $formattedDate = date('Y-m-d', $timestamp);
            // 예외 날짜이거나 (주말/공휴일이 아니면) 영업일로 간주
            if (isset($exception_dates[$formattedDate]) || ($dayOfWeek != 0 && $dayOfWeek != 6 && !isset($holidays[$formattedDate]))) {
                break; // 영업일이면 루프 종료
            }
            $timestamp = strtotime('+1 day', $timestamp); // 미래로 이동
        }
    }

    return date('Y-m-d', $timestamp) . ' '.SUBSCRIPTION_DEFAULT_TIME_SUFFIX;
}

function subscription_serial_encode($data, $od = null)
{
    return base64_encode(serialize($data));
}

function subscription_serial_decode($data, $od = null)
{
    return unserialize(base64_decode($data));
}

function calculateNextDeliveryDate($od, $date_format = '')
{
    if (!is_null_date($od['od_hope_date'])) {
        $timestamp = strtotime($od['od_hope_date']);
    } else {
        $lead_days = (int) get_subs_option('su_auto_payment_lead_days');
        $str_od_time = strtotime($od['od_time']);
        
        if ($lead_days > 0) {
            $timestamp = strtotime(getBusinessDaysNext(date('Y-m-d H:i:s', strtotime("+$lead_days days", $str_od_time))));
        } else {
            $timestamp = $str_od_time;
        }
    }
    
    //$timestamp = !is_null_date($od['od_hope_date']) ? strtotime($od['od_hope_date']) : strtotime($od['od_time']);

    // 아직 첫회차 결제전이면
    if (!$od['od_pays_total'] && $od['od_hope_date']) {
        // return date('Y-m-d', $timestamp);
    }
    
    if (isset($od['next_delivery_date']) && !is_null_date($od['next_delivery_date'])) {
        $timestamp = strtotime($od['next_delivery_date']);
    }
    
    $od_subscription_selected_data = subscription_serial_decode($od['od_subscription_selected_data']);
    $od_subscription_selected_number = subscription_serial_decode($od['od_subscription_selected_number']);
    
    $nextdate = getIntervalBasedNextDate($timestamp, $od_subscription_selected_data, $od_subscription_selected_number);
    
    if ($date_format) {
        return date($date_format, strtotime($nextdate));
    }
    
    return $nextdate;
}

//결제방식 이름을 체크하여 치환 대상인 문자열은 따로 리턴합니다.
function get_subscription_pay_name_replace($payname, $od = array(), $is_client = 0)
{

    // 기존에 저장되어 있던 카드를 재사용한 경우
    if ($payname === '카드재사용') {
        return '신용카드';
    }

    return $payname;
}

function calculateNextBillingDate($od, $od_hope_date = null)
{
    $od_subscription_selected_data = subscription_serial_decode($od['od_subscription_selected_data']);
    $od_subscription_selected_number = subscription_serial_decode($od['od_subscription_selected_number']);
    
    // 설정값: 결제 전 여유 일수
    $config_before_pay_date = get_subs_option('su_auto_payment_lead_days') ? (int) get_subs_option('su_auto_payment_lead_days') : 0;

    // 희망배송일이 있으면
    if ($od_hope_date) {

        $nextdate = getNextPaymentDate($od_hope_date, $config_before_pay_date, $od);

        return $nextdate;
    }
    
    // 현재 날짜를 DateTime 객체로 변환
    if (isset($od['next_delivery_date']) && !is_null_date($od['next_delivery_date'])) {
        //return getNextPaymentDate($od['next_delivery_date'], $config_before_pay_date, $od);
        
        $delivery_date = calculateNextDeliveryDate($od);
        
        return getNextPaymentDate($delivery_date, $config_before_pay_date, $od);
        
    } else if (is_null_date($od['next_billing_date'])) {
        $timestamp = G5_SERVER_TIME;
    } else {
        $timestamp = strtotime($od['next_billing_date']);
    }
    
    return getIntervalBasedNextDate($timestamp, $od_subscription_selected_data, $od_subscription_selected_number);
}

function get_nicepay_api_url()
{

    // 테스트인(샌드박스) 경우 나이스페이 api url
    if (get_subs_option('su_card_test')) {
        return 'https://sandbox-api.nicepay.co.kr';
    }

    // 실서버(운영계) 나이스페이 api url
    return 'https://api.nicepay.co.kr';
}

function expire_nicepay_billing($bid)
{
    global $clientId;
    global $secretKey;

    // 
    try {
        $res = requestPost(
            get_nicepay_api_url() . "/v1/subscribe/" . $bid . "/expire",
            json_encode(
                array("orderId" => uniqid())
            ),
            $clientId . ':' . $secretKey
        );

        return $res;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}

function nocache_nostore_subscription_headers()
{
    // 일부 브라우저 또는 앞으로 브라우저가 업데이트 된다면 아래의 방법이 안될수도 있습니다.

    if (headers_sent()) return;

    header_remove('Last-Modified');

    header('Expires: Sat, 17 Jan 1999 01:00:00 GMT');
    header('Cache-Control: no-transform, no-cache, no-store, must-revalidate');
}

function kcp_billing($od, $tmp_cart_id = '')
{
    global $g5, $default, $is_member, $member;

    include(G5_SUBSCRIPTION_PATH . '/settle_kcp.inc.php');

    $site_cd            = get_subs_option('su_kcp_mid'); // 사이트 코드
    // 인증서 정보(직렬화)
    $kcp_cert_info      = get_subs_option('su_kcp_cert_info');

    $cart_id = $tmp_cart_id ? $tmp_cart_id : $od['od_id'];
    $goodsname = get_subscription_goods($cart_id);

    $cust_ip            = "";
    $currency           = '410'; // 화폐 단위
    // $ordr_idxx          = $od['od_id'].'_'.md5($od['mb_id']).'_'.uniqid(); // 주문번호 
    $ordr_idxx          = generate_subscription_id($od['od_id'], $od['mb_id']);
    $good_name          = $goodsname['full_name']; // 상품명
    $buyr_name          = $od['od_name']; // 주문자명
    $buyr_mail          = $od['od_email']; // 주문자 E-mail
    $buyr_tel2          = $od['od_hp']; // 주문자 휴대폰번호

    $bt_batch_key       = get_card_billkey($od); // 배치키 정보
    $bt_group_id        = get_subs_option('su_kcp_group_id'); // 배치키 그룹아이디

    $data = array(
        "site_cd"        => $site_cd,
        "kcp_cert_info"  => $kcp_cert_info,
        "pay_method"     => SUBSCRIPTION_DEFAULT_PAYMETHOD,
        "cust_ip"        => "",
        "amount"         => $od['od_receipt_price'],
        "card_mny"       => $od['od_receipt_price'],
        "currency"       => $currency,
        "quota"          => "00",
        "ordr_idxx"      => $ordr_idxx,
        "good_name"      => $good_name,
        "buyr_name"      => $buyr_name,
        "buyr_mail"      => $buyr_mail,
        "buyr_tel2"      => $buyr_tel2,
        "card_tx_type"   => "11511000",
        "bt_batch_key"   => $bt_batch_key,
        "bt_group_id"    => $bt_group_id
    );

    $req_data = json_encode($data);

    $header_data = array("Content-Type: application/json", "charset=utf-8");

    // API REQ
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $kcp_target_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header_data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $req_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // API RES
    $res_data  = curl_exec($ch);

    curl_close($ch);
    
    $results = array('res_cd'=>null, 'res_msg'=>null);

    // $res_data 형식은 json
    if ($res_data) {
        $results = json_decode($res_data, true);
    }

    run_event('subscription_order_pg_pay', 'kcp', $results, $data);
    
    if (isset($results['res_cd']) && $results['res_cd'] === '0000') {

        // 공통형식에 맞추어야 한다.
        $results['orderId'] = $results['order_no'];
        $results['payMethod'] = $results['pay_method'];
        // $results['amount'] = $results['amount'];
        $results['receiptUrl'] = '';    // kcp 는 영수증 url 이 없다.
        $results['cardname'] = $results['card_name'];
        $results['cardnumber'] = mask_card_number($results['card_no']);   // kcp 는 정기결제시 결제카드 번호를 다 알려주지만, 여기서는 마스킹하여 저장한다.
        $results['py_app_no'] = $results['app_no'];
        $results['tid'] = $results['tno'];

        return array('code' => 'success', 'message' => $results['res_msg'], 'response' => $results);
    } else {
        return array('code' => 'fail', 'message' => $results['res_cd'] . ':' . $results['res_msg'], 'response' => $results);
    }

    return array();
}

function subscription_process_payment($od, $od_pg_service = '', $tmp_cart_id = '')
{

    $subscription_pg_service = $od_pg_service ? $od_pg_service : get_subs_option('su_pg_service');

    if ($subscription_pg_service === 'kcp') {
        return kcp_billing($od, $tmp_cart_id);
    } else if ($subscription_pg_service === 'inicis') {
        return inicis_billing($od, $tmp_cart_id);
    } else if ($subscription_pg_service === 'tosspayments') {
        return tosspayments_billing($od, $tmp_cart_id);
    } else if ($subscription_pg_service === 'nicepay') {
        return nicepay_billing($od, $tmp_cart_id);
    }

    return null;
}

function subscription_pg_cardname($od_card_name, $card = array())
{

    if ($od_card_name && strpos($od_card_name, '카드') === false) {
        $od_card_name .= '카드';
    }

    return $od_card_name;
}

function check_subscription_pay_method($od_settle_case)
{
    run_event('check_subscription_pay_method', $od_settle_case);
}

function nicepay_reqPost(array $data, $url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);                    //connection timeout 15 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));    //POST data
    curl_setopt($ch, CURLOPT_POST, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function nicepay_billing($od, $tmp_cart_id = '')
{
    global $g5;

    include(G5_SUBSCRIPTION_PATH . '/settle_nicepay.inc.php');

    // https://developers.nicepay.co.kr/manual-card-billing.php#parameter-card-billing-response
    // 빌링 결제(승인) API 요청 URL
    $postURL = "https://webapi.nicepay.co.kr/webapi/billing/billing_approve.jsp";

    $cart_id = $tmp_cart_id ? $tmp_cart_id : $od['od_id'];
    $goodsname = get_subscription_goods($cart_id);

    /*
    ****************************************************************************************
    * (요청 값 정보)
    * 아래 파라미터에 요청할 값을 알맞게 입력합니다. 
    ****************************************************************************************
    */
    $bid                 = get_card_billkey($od);                // 빌키
    $mid                 = get_subs_option('su_nicepay_mid');        // 가맹점 아이디
    // $tid 				= substr(substr($od['od_tno'], 0, 20).substr(preg_replace('/[^0-9]/', '', G5_TIME_YMDHIS), 2), 0, 30);				// 거래 ID, 30글자 제한있음, 30글자 채워야함
    // $tid 				= generate_subscription_id($od);				// 거래 ID, 30글자 제한있음, 30글자 채워야함
    // $od['od_tno'] 인 경우 subscription/orderform.php 에서 재등록카드를 사용할 경우 A212, [TID]잘못된 데이터 형식입니다. 오류가 일어난다.

    // 나이스페이 옛결제모듈의 경우 tid와 moid 는 카드등록을 한 tno와 od_id로 보내야 한다.
    $sql = "SELECT * 
            FROM {$g5['g5_subscription_mb_cardinfo_table']} 
            WHERE ci_id = '" . sql_real_escape_string($od['ci_id']) . "' 
            AND mb_id = '" . sql_real_escape_string($od['mb_id']) . "' 
            AND pg_service = '" . sql_real_escape_string($od['od_pg']) . "' 
            AND od_tno != '' 
            ORDER BY ci_id DESC 
            LIMIT 1";
    $before_nice_pay = sql_fetch($sql);

    // $tid = $before_nice_pay['od_tno'].'12';      // 일부러 실패하려고 한다면

    $tid = generate_subscription_id($before_nice_pay['od_tno'], $before_nice_pay['mb_id']);

    // $tid = $before_nice_pay['od_tno'];
    // 나이스페이 옛결제모듈의 경우 tid와 moid 는 카드등록을 한 tno와 od_id로 보내야 한다.
    // $moid 				= $before_nice_pay['first_ordernumber'];				// 가맹점 주문번호

    $moid                 = generate_subscription_id($od['od_id'], $od['mb_id']);                // 가맹점 주문번호
    $amt                 = (int) $od['od_receipt_price'];                // 결제 금액
    //$goodsName 			= $goodsname['full_name'];				// 상품명

    $goodsName             = iconv("UTF-8", "EUC-KR", $goodsname['full_name']);                // 상품명
    $cardInterest         = '0';                // 무이자 여부, 가맹점 분담 무이자 할부 이벤트 사용 여부 (0: 미사용, 1: 사용(무이자))
    $cardQuota             = '00';                // 할부 개월 수, 할부개월 (00: 일시불, 02: 2개월, 03: 3개월, ...)
    $buyerName             = iconv("UTF-8", "EUC-KR", $od['od_name']);                // 구매자명
    $buyerTel             = $od['od_hp'];                // 구매자 전화번호
    $buyerEmail         = $od['od_email'];                // 구매자 이메일

    /*
    ****************************************************************************************
    * (해쉬암호화 - 수정하지 마세요)
    * SHA-256 해쉬암호화는 거래 위변조를 막기위한 방법입니다. 
    ****************************************************************************************
    */
    $ediDate = date("YmdHis", G5_SERVER_TIME);                                                                                    // API 요청 전문 생성일시
    $merchantKey = get_subs_option('su_nicepay_key');    // 가맹점 키	
    $signData = bin2hex(hash('sha256', $mid . $ediDate . $moid . $amt . $bid . $merchantKey, true));            // 위변조 데이터 검증 값 암호화

    /*
    ****************************************************************************************
    * (API 요청부)
    * 명세서를 참고하여 필요에 따라 파라미터와 값을 'key'=>'value' 형태로 추가해주세요
    ****************************************************************************************
    */
    $data = array(
        'BID' => $bid,
        'MID' => $mid,
        'TID' => $tid,
        'EdiDate' => $ediDate,
        'Moid' => $moid,
        'Amt' => $amt,
        'GoodsName' => $goodsName,
        'SignData' => $signData,
        'CardInterest' => $cardInterest,
        'CardQuota' => $cardQuota,
        'CharSet' => 'utf-8'
    );

    $response = nicepay_reqPost($data, $postURL);                 //API 호출, 결과 데이터가 $response 변수에 저장됩니다.

    // $resp_utf = iconv("EUC-KR", "UTF-8", $response);
    $resp_utf = $response;
    $nice_response = json_decode($resp_utf, true);

    $message = '';

    // resultCode 가 3001이면 성공이고 그외이면 실패
    if ($nice_response['ResultCode'] === '3001' && isset($nice_response['TID']) && $nice_response['TID']) {
        $code = 'success';
    } else {
        $code = 'fail';
        $message = $nice_response['ResultMsg'];
    }

    // 공통형식에 맞추어야 한다.
    $nice_response['orderId'] = $nice_response['Moid'];
    $nice_response['payMethod'] = SUBSCRIPTION_DEFAULT_PAYMETHOD;
    $nice_response['amount'] = (int) $nice_response['Amt'];
    $nice_response['receiptUrl'] = '';    // kcp 는 영수증 url 이 없다.
    $nice_response['cardname'] = $nice_response['CardName'];
    $nice_response['cardnumber'] = mask_card_number($nice_response['CardNo']);   // 51881111****2222 이런 형식으로 카드, 여기서는 마스킹하여 저장한다.
    $nice_response['py_app_no'] = $nice_response['AuthCode'];     // 승인번호
    $nice_response['tid'] = $nice_response['TID'];

    run_event('subscription_order_pg_pay', 'nicepay', $nice_response, $data);

    // $res 형식은 json
    return array('code' => $code, 'message' => $message, 'response' => $nice_response);
}

function nicepay_new_billing($od, $tmp_cart_id = '')
{
    global $g5;

    include(G5_SUBSCRIPTION_PATH . '/settle_nicepay.inc.php');

    $clientId = get_subs_option('su_nice_clientid');
    $secretKey = get_subs_option('su_nice_secretkey');

    $cart_id = $tmp_cart_id ? $tmp_cart_id : $od['od_id'];
    $goodsname = get_subscription_goods($cart_id);

    $res = null;

    $bid = get_card_billkey($od);

    // https://github.com/nicepayments/nicepay-manual/blob/main/api/payment-subscribe.md#%EB%B9%8C%ED%82%A4%EC%8A%B9%EC%9D%B8
    // $nice_orderId = substr($od['od_id'].'_'.md5($od['mb_id']).'_'.uniqid(), 0, 64);  // 64길이
    $nice_orderId = generate_subscription_id($id, $od['mb_id'], 64);  // 64길이 가능
    $edi_date = date('c', G5_SERVER_TIME);
    $sign_data = bin2hex(hash('sha256', $nice_orderId . $bid . $edi_date . $secretKey, true));
    $buyerName = $od['od_name'];
    $buyerEmail = $od['od_email'];
    $buyerTel = $od['od_hp'];

    // 면세공급가액, 전체 거래금액(amount)중에서 면세에 해당하는 금액을 설정합니다.
    // $taxFreeAmt = ;

    $code = 'success';
    $message = '';
    $res = null;

    // $res 형식은 json

    $request_data = array(
        "orderId" => $nice_orderId,
        "amount" => (int) $od['od_receipt_price'],
        "goodsName" => $goodsname['full_name'],
        "cardQuota" => 0,
        "useShopInterest" => false,
        'buyerName' => $buyerName,
        'buyerTel' => $buyerTel,
        'buyerEmail' => $buyerEmail
    );

    try {
        $res = requestPost(
            get_nicepay_api_url() . "/v1/subscribe/" . $bid . "/payments",
            json_encode($request_data),
            $clientId . ':' . $secretKey
        );

        $code = 'success';
    } catch (Exception $e) {

        $code = 'fail';
        $message = $e->getMessage();
    }

    $nice_response = json_decode($res, true);

    // 공통형식에 맞추어야 한다.
    $nice_response['orderId'] = $nice_response['Moid'];
    $nice_response['payMethod'] = SUBSCRIPTION_DEFAULT_PAYMETHOD;
    $nice_response['amount'] = (int) $nice_response['Amt'];
    $nice_response['receiptUrl'] = '';    // kcp 는 영수증 url 이 없다.
    $nice_response['cardname'] = $nice_response['CardName'];
    $nice_response['cardnumber'] = mask_card_number($nice_response['CardNo']);   // 51881111****2222 이런 형식으로 카드, 여기서는 마스킹하여 저장한다.
    $nice_response['py_app_no'] = $nice_response['AuthCode'];     // 승인번호
    $nice_response['tid'] = $nice_response['TID'];

    // resultCode 가 0000 and tid 가 없으면 결제실패이다
    if (!($nice_response['resultCode'] === '0000' && isset($nice_response['tid']) && $nice_response['tid'])) {
        $code = 'fail';
        $message = $nice_response['resultMsg'];
    }

    run_event('subscription_order_pg_pay', 'nicepay', $nice_response, $request_data);

    // $res 형식은 json
    return array('code' => $code, 'message' => $message, 'response' => $nice_response);
}

function subscription_sendRequest($url, $authKey, $postData)
{
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: $authKey",
        "Content-Type: application/json"
    ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

function get_tosspayments_by_cardcode($key){
    $card_org_codes = array(
        // ===== 국내 카드사 =====
        '3K' => array('ko' => '기업비씨',   'en' => 'IBK_BC'),
        '46' => array('ko' => '광주',       'en' => 'GWANGJUBANK'),
        '71' => array('ko' => '롯데',       'en' => 'LOTTE'),
        '30' => array('ko' => '산업',       'en' => 'KDBBANK'),
        '31' => array('ko' => 'BC카드',          'en' => 'BC'),
        '51' => array('ko' => '삼성',       'en' => 'SAMSUNG'),
        '38' => array('ko' => '새마을',     'en' => 'SAEMAUL'),
        '41' => array('ko' => '신한',       'en' => 'SHINHAN'),
        '62' => array('ko' => '신협',       'en' => 'SHINHYEOP'),
        '36' => array('ko' => '씨티',       'en' => 'CITI'),
        '33' => array('ko' => '우리(BC매입)', 'en' => 'WOORI_BC'),
        'W1' => array('ko' => '우리',       'en' => 'WOORI'),
        '37' => array('ko' => '우체국',     'en' => 'POST'),
        '39' => array('ko' => '저축은행',   'en' => 'SAVINGBANK'),
        '35' => array('ko' => '전북',       'en' => 'JEONBUKBANK'),
        '42' => array('ko' => '제주',       'en' => 'JEJUBANK'),
        '15' => array('ko' => '카카오뱅크', 'en' => 'KAKAOBANK'),
        '3A' => array('ko' => '케이뱅크',   'en' => 'KBANK'),
        '24' => array('ko' => '토스뱅크',   'en' => 'TOSSBANK'),
        '21' => array('ko' => '하나',       'en' => 'HANA'),
        '61' => array('ko' => '현대',       'en' => 'HYUNDAI'),
        '11' => array('ko' => '국민',       'en' => 'KOOKMIN'),
        '91' => array('ko' => '농협',       'en' => 'NONGHYEOP'),
        '34' => array('ko' => '수협',       'en' => 'SUHYEOP'),

        // ===== 해외 카드사 =====
        'AMEX'     => array('ko' => '아메리칸 익스프레스', 'en' => 'AMERICANEXPRESS'),
        'DINERS'   => array('ko' => '다이너스 클럽',       'en' => 'DINERSCLUB'),
        'DISCOVER' => array('ko' => '디스커버',             'en' => 'DISCOVER'),
        'JCB'      => array('ko' => 'JCB',                'en' => 'JCB'),
        'MASTER'   => array('ko' => '마스터카드',           'en' => 'MASTER'),
        'UNIONPAY' => array('ko' => '유니온페이',           'en' => 'UNIONPAY'),
        'VISA'     => array('ko' => '비자',                'en' => 'VISA')
    );
        
    return isset($card_org_codes[$key]) ? $card_org_codes[$key]['ko'] : $key;
}

function tosspayments_customerkey_uuidv4($str) {
    // 다른 사용자가 이 값을 알게 되면 악의적으로 사용될 수 있습니다.
    // 자동 증가하는 숫자 또는 이메일・전화번호・사용자 아이디와 같이 유추가 가능한 값은 안전하지 않습니다. ( tosspayments 의 customerKey 메뉴얼 확인 )
    // G5_TOKEN_ENCRYPTION_KEY 값을 수정하게 된다면, 기존에 등록했던 빌링키가 유효성에 검사에 틀리게 되어 사용을 할수 없다.
    $add_hash = defined('G5_TOKEN_ENCRYPTION_KEY') ? substr(G5_TOKEN_ENCRYPTION_KEY, 0, 9) : '';
    
    // 입력값으로 md5 해시 생성
    $hash = md5($str.$add_hash);
    
    $plus_str = '_';
    
    // UUID 형식으로 포맷
    return substr($hash, 0, 8) . $plus_str .
           substr($hash, 8, 4) . $plus_str .
           substr($hash, 12, 4) . $plus_str .
           substr($hash, 16, 4) . $plus_str .
           substr($hash, 20, 12);
}

function tosspayments_billing($od, $tmp_cart_id = '')
{
    global $g5;
    
    include_once(G5_SUBSCRIPTION_PATH . '/settle_tosspayments.inc.php');

    $apiSecretKey = get_subs_option('su_tosspayments_api_secretkey');

    $encryptedApiSecretKey = "Basic " . base64_encode($apiSecretKey . ":");

    $billingKey = get_card_billkey($od);
    $cart_id = $tmp_cart_id ? $tmp_cart_id : $od['od_id'];
    $goodsname = get_subscription_goods($cart_id);

    $data = array(
        'amount' => $od['od_receipt_price'],
        // 'orderId' => substr($od['od_id'].'_'.md5($od['mb_id']).'_'.uniqid(), 0, 64),  // 64길이
        'orderId' => generate_subscription_id($od['od_id'], $od['mb_id'], 64),  // 64길이 가능
        'orderName' => $goodsname['full_name'],
        'customerEmail' => $od['od_email'],
        'customerName' => $od['od_name']
    );
    
    $postData = json_encode(array(
        'customerKey' => tosspayments_customerkey_uuidv4($od['mb_id']),
        'amount' => $data['amount'],
        'orderId' => $data['orderId'],
        'orderName' => $data['orderName'],
        'customerEmail' => $data['customerEmail'],
        'customerName' => $data['customerName']
    ));

    $response = subscription_sendRequest("https://api.tosspayments.com/v1/billing/$billingKey", $encryptedApiSecretKey, $postData);

    $res_result = json_decode($response, true);
    
    // 영카트5 정기결제 공통규격에 맞게 수정
    $res_result['payMethod'] = 'CARD';
    $res_result['amount'] = $res_result['totalAmount'];
    $res_result['receiptUrl'] = '';    // 토스페이먼츠는 영수증 url 이 없다.
    $res_result['cardname'] = get_tosspayments_by_cardcode($res_result['card']['issuerCode']);
    $res_result['cardnumber'] = $res_result['card']['number'];   // 카드 마스킹번호
    $res_result['py_app_no'] = $res_result['card']['approveNo'];     // 승인번호
    $res_result['tid'] = $res_result['lastTransactionKey'];
    
    if (isset($res_result['code']) && $res_result['code']) {
        // 자동결제 실패했음

        return array('code' => $res_result['code'], 'message' => $res_result['message'], 'response' => $res_result);
    }

    // 결제 성공시
    return array('code' => 'success', 'message' => '', 'response' => $res_result);
}

function inicis_billing($od, $tmp_cart_id = '')
{
    global $g5, $inicis_iniapi_key, $inicis_iniapi_iv;

    include_once(G5_SUBSCRIPTION_PATH . '/settle_inicis.inc.php');

    //step1. 요청을 위한 파라미터 설정
    $key = $inicis_iniapi_key;
    $iv = $inicis_iniapi_iv;
    $mid = get_subs_option('su_inicis_mid');
    $type = "billing";      // 요청서비스 ["billing" 고정]
    $paymethod = "Card";    // 지불수단 코드 [card:신용카드, HPP:휴대폰]
    $timestamp = date("YmdHis", G5_SERVER_TIME);    // 전문생성시간 [YYYYMMDDhhmmss]
    $clientIp = $_SERVER['SERVER_ADDR'];    // 가맹점 요청 서버IP (추후 거래 확인 등에 사용됨)

    $postdata = array();
    $postdata["mid"] = $mid;
    $postdata["type"] = $type;
    $postdata["paymethod"] = $paymethod;
    $postdata["timestamp"] = $timestamp;
    $postdata["clientIp"] = $clientIp;

    $cart_id = $tmp_cart_id ? $tmp_cart_id : $od['od_id'];
    $goodsname = get_subscription_goods($cart_id);

    //// Data 상세
    $detail = array();
    // $detail["url"] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['REQUEST_URI'];
    $detail["url"] = G5_SUBSCRIPTION_URL;
    // $detail["moid"] = $od['od_id'];
    $detail["moid"] = generate_subscription_id($od['od_id'], $od['mb_id']);
    $detail["goodName"] = $goodsname['full_name'];
    $detail["buyerName"] = $od['od_name'];
    $detail["buyerEmail"] = $od['od_email'];
    $detail["buyerTel"] = $od['od_tel'] ? $od['od_tel'] : $od['od_hp'];

    // 장바구니 금액이 변경될수 있으니, cron에서 updateSubscriptionItemIfChanged 함수를 이용하여 장바구니 금액 변동을 체크한다.
    $detail["price"] = $od['od_receipt_price'];

    $detail["billKey"] = get_card_billkey($od);
    $detail["authentification"] = "00";
    $detail["cardQuota"] = "00";
    $detail["quotaInterest"] = "0";

    $postdata["data"] = $detail;

    $details = str_replace('\\/', '/', json_encode($detail, JSON_UNESCAPED_UNICODE));

    //// Hash Encryption
    $plainTxt = $key . $mid . $type . $timestamp . $details;
    $hashData = hash("sha512", $plainTxt);

    $postdata["hashData"] = $hashData;

    $is_print = false;

    if ($is_print) {
        echo "plainTxt : " . $plainTxt . "<br/><br/>";
        echo "hashData : " . $hashData . "<br/><br/>";
    }

    $post_data = json_encode($postdata, JSON_UNESCAPED_UNICODE);

    if ($is_print) {
        echo "**** 요청전문 **** <br/>";
        echo str_replace(',', ',<br>', $post_data) . "<br/><br/>";
    }

    //step2. 요청전문 POST 전송

    $url = "https://iniapi.inicis.com/v2/pg/billing";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    curl_close($ch);


    //step3. 결과출력
    if ($is_print) {
        echo "**** 응답전문 **** <br/>";
        echo str_replace(',', ',<br>', $response) . "<br><br>";
    }

    // 성공이면 pay 테이블에 insert 한다. $response 형식은 json

    $inicis_res = json_decode($response, true);

    // 영카트5 정기결제 공통규격에 맞게 수정
    $inicis_res['orderId'] = $detail["moid"];
    $inicis_res['payMethod'] = 'CARD';
    $inicis_res['amount'] = $inicis_res['price'];
    $inicis_res['receiptUrl'] = '';    // 이니시스 는 영수증 url 이 없다.
    $inicis_res['cardname'] = isset($CARD_CODE[$inicis_res['cardCode']]) ? $CARD_CODE[$inicis_res['cardCode']] : $inicis_res['cardCode'];
    $inicis_res['cardnumber'] = $inicis_res['cardNumber'];   // 카드 마스킹번호
    $inicis_res['py_app_no'] = $inicis_res['payAuthCode'];     // 승인번호

    run_event('subscription_order_pg_pay', 'inicis', $inicis_res, $postdata);

    if (isset($inicis_res['resultCode']) && $inicis_res['resultCode'] === '00') {

        return array('code' => 'success', 'message' => $inicis_res['resultMsg'], 'response' => $inicis_res);
    } else {

        // 실패시
        return array('code' => 'fail', 'message' => $inicis_res['resultCode'] . ':' . $inicis_res['resultMsg'], 'response' => $inicis_res);
    }
}

function is_null_date($datetime)
{

    if (! $datetime || $datetime == null || strpos($datetime, '0000-00-00') !== false) {
        return true;
    }

    return false;
}

// 한글 요일
function get_hangul_date_format($str)
{
    $formats = array('day' => '일', 'week' => '주', 'month' => '월', 'year' => '년');

    return isset($formats[$str]) ? $formats[$str] : '';
}

function mask_card_number($string)
{
    // 문자열 길이 확인
    $length = strlen($string);

    // 카드 길이가 16자리가 아닌경우 (나이스페이의 경우 결제실패시 **** 4자리를 리턴한다.)
    if ($length !== 16) {
        return $string;
    }

    // 시작과 끝에 남길 자리 수 설정
    $start = 6;
    $end = 1;

    // 마스킹할 부분의 길이 계산
    $maskLength = $length - ($start + $end);

    // 문자열을 마스킹된 형태로 변환
    return substr($string, 0, $start) . str_repeat('*', $maskLength) . substr($string, -$end);
}

function get_subscription_uniqid($is_pay = 0, $uniqid_key = '', $length = 0)
{
    global $g5;

    sql_query(" LOCK TABLE {$g5['g5_subscription_uniqid_table']} WRITE ");

    $i = 0;
    while (1) {
        // 년월일시분초에 100분의 1초 두자리를 추가함 (1/100 초 앞에 자리가 모자르면 0으로 채움)

        if ($is_pay) {
            $key = $uniqid_key;

            if ($i > 0) {
                $pad_str = str_pad((int)((float)microtime() * 100), 2, "0", STR_PAD_LEFT);
                $key = ($length && strlen($key) >= $length) ? substr($key, 0, -2) . $pad_str : $key . $pad_str;
            }
        } else {
            $key = date('YmdHis', time()) . str_pad((int)((float)microtime() * 100), 2, "0", STR_PAD_LEFT);
        }

        $result = sql_query(" insert into {$g5['g5_subscription_uniqid_table']} set suq_key = '$key', suq_ip = '{$_SERVER['REMOTE_ADDR']}' ", false);

        if ($result) break; // 쿼리가 정상이면 빠진다.

        // insert 하지 못했으면 일정시간 쉰다음 다시 유일키를 만든다.
        usleep(10000); // 100분의 1초를 쉰다
        $i++;
    }

    sql_query(" UNLOCK TABLES ");

    return $key;
}

function generate_subscription_id($oid = '', $mb_id = '', $length = 30)
{
    global $g5, $is_member, $member;

    if (!$mb_id && $is_member) {
        $mb_id = $member['mb_id'];
    }

    // 데이터베이스에서 가장 최근 주문 ID 가져오기
    $sql = "SELECT MAX(pay_id) AS pay_id 
            FROM {$g5['g5_subscription_pay_table']}";
    $stmt = sql_fetch($sql);

    $lastId = $stmt['pay_id'];
    $lastId = $lastId ? $lastId + 1 : 1;

    $str = substr(hash('sha256', $lastId . $mb_id . microtime()), 0, 12);

    if (strlen($oid) >= $length) {
        $subscription_key = substr($oid, 0, -12) . $str;
    } else {
        $subscription_key = $oid ? $oid . $str : $str;
    }

    return get_subscription_uniqid(1, substr($subscription_key, 0, $length), $length);
}

function get_subscription_pg_id($pg_name = '')
{

    $pg = $pg_name ? $pg_name : get_subs_option('su_pg_service');

    $str = '';

    if ($pg === 'kcp') {
        $str = get_subs_option('su_kcp_mid');
    } else if ($pg === 'inicis') {
        $str = get_subs_option('su_inicis_mid');
    } else if ($pg === 'nicepay') {
        $str = get_subs_option('su_nice_clientid');
    } else if ($pg === 'tosspayments') {
        $str = get_subs_option('su_tosspayments_mid');
    }

    return $str;
}

function get_subscription_pg_apikey($pg_name = '')
{

    $pg = $pg_name ? $pg_name : get_subs_option('su_pg_service');

    $str = '';

    if ($pg === 'kcp') {
    } else if ($pg === 'inicis') {
        $str = get_subs_option('su_inicis_mid');
    } else if ($pg === 'nicepay') {
        $str = get_subs_option('su_nice_clientid');
    } else if ($pg === 'tosspayments') {
        $str = get_subs_option('su_tosspayments_api_clientkey');
    }

    return $str;
}

// 정기구독 장바구니 간소 데이터 가져오기
function get_subscription_boxcart_datas($is_cache = false)
{
    global $g5, $is_member, $member;

    // 회원이 아니면
    if (!$is_member) {
        return array();
    }

    static $cache = array();

    if ($is_cache && !empty($cache)) {
        return $cache;
    }

    // 정기구독은 회원만 결제가 가능하다.
    $cart_id = get_session("subs_cart_id");
    $add_where = '';
    
    if ($cart_id) {
        $add_where = " and  od_id = '" . sql_real_escape_string($cart_id) . "'";
    }
    
    $sql = "SELECT * 
        FROM {$g5['g5_subscription_cart_table']} 
        WHERE mb_id = '" . sql_real_escape_string($member['mb_id']) . "' $add_where
        AND ct_status = '쇼핑' 
        GROUP BY it_id";
        
    $results = sql_query($sql);
    $carts = sql_result_array($results);

    foreach ($carts as $row) {
        $key = $row['it_id'];
        $cache[$key] = $row;
    }

    return $cache;
}

//장바구니 간소 데이터 갯수 출력
function get_subscription_boxcart_datas_count()
{
    $cart_datas = get_subscription_boxcart_datas(true);

    return $cart_datas ? count($cart_datas) : 0;
}

function get_subscription_user_carts($s_cart_id, $is_cache = false)
{

    global $g5, $member;

    static $cache = array();

    $key = 'subs_' . $member['mb_id'] . '_' . $s_cart_id;

    if ($is_cache && isset($cache[$key])) {
        return $cache[$key];
    }

    $sql = "SELECT a.ct_id, a.it_id, a.it_name, a.ct_price, a.ct_point, a.ct_qty, a.ct_status, a.ct_send_cost, a.it_sc_type, 
                b.ca_id, b.ca_id2, b.ca_id3 
            FROM {$g5['g5_subscription_cart_table']} a 
            LEFT JOIN {$g5['g5_shop_item_table']} b ON (a.it_id = b.it_id) 
            WHERE a.od_id = '" . sql_real_escape_string($s_cart_id) . "' 
            GROUP BY a.it_id 
            ORDER BY a.ct_id";
    $results = sql_query($sql);

    $cart_datas = sql_result_array($results);

    $cache[$key] = $cart_datas;

    return $cart_datas;
}

function add_subscription_order_history($content, $arg = array())
{
    global $g5;

    // hs_category 카테고리의 경우 admin 은 관리자
    $sql = "INSERT INTO {$g5['g5_subscription_order_history_table']} 
            (
                hs_parent, 
                hs_type, 
                hs_category, 
                od_id, 
                mb_id, 
                hs_content, 
                hs_time
            ) 
            VALUES (
                '" . (isset($arg['hs_parent']) ? (int)$arg['hs_parent'] : 0) . "', 
                '" . (isset($arg['hs_type']) ? $arg['hs_type'] : 'subscription_history') . "', 
                '" . (isset($arg['hs_category']) ? $arg['hs_category'] : 'admin') . "', 
                '" . (isset($arg['od_id']) ? $arg['od_id'] : '') . "', 
                '" . (isset($arg['mb_id']) ? $arg['mb_id'] : '') . "', 
                '$content', 
                '" . ((isset($arg['hs_time']) && strtotime($arg['hs_time']) !== false) ? $arg['hs_time'] : G5_TIME_YMDHIS) . "'
            )";
    $result = sql_query($sql);

    return sql_insert_id();
}

function delete_subscription_order_history($hs_id)
{
    global $g5;

    $sql = "DELETE FROM {$g5['g5_subscription_order_history_table']} 
            WHERE hs_id = '$hs_id'";
    sql_query($sql, false);
}

function get_subscription_pay_full_goods($id, $is_pay = 0, $is_cache = false)
{
    global $g5;

    static $cache = array();

    // $is_pay 가 0이면 구독내역이고 1이면 정기결제내역
    $key = md5($id . '||' . $is_pay);

    if ($is_cache && isset($cache[$key])) {
        return $cache[$key];
    }

    $goods = array(
        'full_name' => '',
        'thumb' => '',
    );

    // 상품명만들기
    /*
    $sql = " select a.it_id, b.it_name from {$g5['g5_subscription_cart_table']} a, {$g5['g5_shop_item_table']} b where a.it_id = b.it_id and a.od_id = '$id' order by ct_id ";
        
    $result = sql_query($sql);
    */

    if ($is_pay) {
        $sql = "SELECT a.it_id, b.it_name 
        FROM {$g5['g5_subscription_pay_basket_table']} as a 
        JOIN {$g5['g5_shop_item_table']} as b ON a.it_id = b.it_id 
        WHERE a.pay_id = '" . sql_real_escape_string($id) . "' 
        ORDER BY a.pay_id";
        $result = sql_query($sql);
    } else {
        $sql = "SELECT a.it_id, b.it_name 
        FROM {$g5['g5_subscription_cart_table']} as a 
        JOIN {$g5['g5_shop_item_table']} as b ON a.it_id = b.it_id 
        WHERE a.od_id = '" . sql_real_escape_string($id) . "' 
        ORDER BY ct_id";
        $result = sql_query($sql);
    }

    $tmp = array();

    for ($i = 0; $row = sql_fetch_array($result); $i++) {

        $row['thumbnail'] = get_subscription_it_image($row['it_id'], 150, 150, false);

        // 대표 상품명과 대표 썸네일을 지정한다.
        if ($i === 0) {
            $goods['full_name'] = preg_replace("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", "", addslashes($row['it_name']));
            $goods['thumb'] = $row['thumbnail'];
        }

        $tmp[$i] = $row;
    }

    $goods['data'] = $tmp;
    $total_tmp = count($tmp);

    if ($tmp && $total_tmp > 1) {
        $goods['full_name'] .= ' 외 ' . ((int)$total_tmp - 1) . '건';
    }

    $cache[$key] = $goods;

    return $cache[$key];
}

function get_Ko_DayOfWeek($day, $is_print_yoil = '')
{

    // 입력된 날짜를 strtotime으로 변환하여 유효성 검사
    $timestamp = strtotime($day);
    if (!$timestamp) return '';

    $yoil = array("일", "월", "화", "수", "목", "금", "토");

    return ($yoil[date('w', $timestamp)]) . $is_print_yoil;
}

function print_subscription_pg_name($od, $pg_name = '')
{
    $txt = '';

    if (isset($od['od_pg'])) {
        $txt = get_text($od['od_pg']);
    }

    return $txt;
}

function print_subscription_card_info($od) {   
    global $g5;
    
    $cards = get_customer_card_info($od);
    
    $txt = '';
    
    if (isset($cards['card_mask_number'])) {
        $txt = get_text($cards['od_card_name']) . ' ' . get_text($cards['card_mask_number']);
    }

    return $txt;
}

function get_subscription_pay($pay_id, $od_id = '') {
    global $g5;

    $sql = "SELECT * 
            FROM {$g5['g5_subscription_pay_table']} 
            WHERE pay_id = '" . sql_real_escape_string($pay_id) . "'";

    if ($od_id) {
        $sql .= " AND od_id = '" . sql_real_escape_string($od_id) . "'";
    }

    $rows = sql_fetch($sql);

    return $rows;
}

// 주문의 금액, 배송비 과세금액 등의 정보를 가져옴
function get_subscription_pay_info($pay_id, $od_id)
{
    global $g5;

    $pay = get_subscription_pay($pay_id, $od_id);

    if (!(isset($pay['pay_id']) && $pay['pay_id'])) {
        return false;
    }

    $info = array();

    $pay_coupon = 0;
    $pay_send_coupon = 0;

    // 장바구니 주문금액정보
    $sql = " select SUM(IF(io_type = 1, (io_price * pb_qty), ((pb_price + io_price) * pb_qty))) as price,
                    SUM(cp_price) as coupon,
                    SUM( IF( pb_notax = 0, ( IF(io_type = 1, (io_price * pb_qty), ( (pb_price + io_price) * pb_qty) ) - cp_price ), 0 ) ) as tax_mny,
                    SUM( IF( pb_notax = 1, ( IF(io_type = 1, (io_price * pb_qty), ( (pb_price + io_price) * pb_qty) ) - cp_price ), 0 ) ) as free_mny
                from `{$g5['g5_subscription_pay_basket_table']}`
                where od_id = '$od_id' and pay_id = '$pay_id'
                  and pb_status IN ( '입금', '준비', '배송', '완료' ) ";
    $sum = sql_fetch($sql);

    $cart_price = $sum['price'];
    $cart_coupon = $sum['coupon'];

    // 배송비
    $send_cost = get_subscription_pay_sendcost($pay_id, $od_id);

    $od_coupon = $od_send_coupon = 0;

    // 과세, 비과세 금액정보
    $tax_mny = $sum['tax_mny'];
    $free_mny = $sum['free_mny'];

    if ($pay['py_tax_flag']) {
        $tot_tax_mny = ($tax_mny + $send_cost + $pay['py_send_cost2'])
            - ($pay_coupon + $pay_send_coupon + $pay['py_receipt_point']);
        if ($tot_tax_mny < 0) {
            $free_mny += $tot_tax_mny;
            $tot_tax_mny = 0;
        }
    } else {
        $tot_tax_mny = ($tax_mny + $free_mny + $send_cost + $pay['py_send_cost2'])
            - ($pay_coupon + $pay_send_coupon + $pay['py_receipt_point']);
        $free_mny = 0;
    }

    $pay_tax_mny = round($tot_tax_mny / 1.1);
    $pay_vat_mny = $tot_tax_mny - $pay_tax_mny;
    $pay_free_mny = $free_mny;

    // 장바구니 취소금액 정보
    $sql = " select SUM(IF(io_type = 1, (io_price * pb_qty), ((pb_price + io_price) * pb_qty))) as price
                from {$g5['g5_subscription_pay_basket_table']}
                where pay_id = '$pay_id'
                  and pb_status IN ( '취소', '반품', '품절' ) ";
    $sum = sql_fetch($sql);

    $cancel_price = $sum['price'];

    // 미수금액
    $pay_misu = ($cart_price + $send_cost + $pay['py_send_cost2'])
        - ($cart_coupon + $pay_coupon + $pay_send_coupon)
        - ($pay['py_receipt_price'] + $pay['py_receipt_point'] - $pay['py_refund_price']);

    // 장바구니상품금액
    $pay_cart_price = $cart_price + $cancel_price;

    // 결과처리
    $info['py_cart_price']      = $pay_cart_price;
    $info['py_send_cost']       = $send_cost;
    $info['py_coupon']          = $pay_coupon;
    $info['py_send_coupon']     = $pay_send_coupon;
    $info['py_cart_coupon']     = $cart_coupon;
    $info['py_tax_mny']         = $pay_tax_mny;
    $info['py_vat_mny']         = $pay_vat_mny;
    $info['py_free_mny']        = $pay_free_mny;
    $info['py_cancel_price']    = $cancel_price;
    $info['py_misu']            = $pay_misu;

    return $info;
}

// 배송비 구함
function get_subscription_pay_sendcost($pay_id, $cart_id, $selected = 1)
{
    global $default, $g5;

    $send_cost = 0;
    $total_price = 0;
    $total_send_cost = 0;
    $diff = 0;

    $sql = "SELECT DISTINCT it_id 
            FROM {$g5['g5_subscription_pay_basket_table']} 
            WHERE od_id = '" . $cart_id . "' 
            AND pb_send_cost = '0' 
            AND pb_select = '" . $selected . "' 
            AND pb_status IN ('쇼핑', '주문', '입금', '준비', '배송', '완료')";
    $result = sql_query($sql);

    for ($i = 0; $sc = sql_fetch_array($result); $i++) {
        // 합계
        $sql = "SELECT SUM(IF(io_type = 1, (io_price * pb_qty), ((pb_price + io_price) * pb_qty))) AS price, 
               SUM(pb_qty) AS qty 
        FROM {$g5['g5_subscription_pay_basket_table']} 
        WHERE it_id = '" . $sc['it_id'] . "' 
        AND od_id = '" . $cart_id . "' 
        AND pb_select = '" . $selected . "' 
        AND pb_status IN ('쇼핑', '주문', '입금', '준비', '배송', '완료')";

        $sum = sql_fetch($sql);

        $send_cost = get_subscription_pay_item_sendcost($sc['it_id'], $sum['price'], $sum['qty'], $cart_id, $pay_id);

        if ($send_cost > 0)
            $total_send_cost += $send_cost;

        if ($default['de_send_cost_case'] == '차등' && $send_cost == -1) {
            $total_price += $sum['price'];
            $diff++;
        }
    }

    $send_cost = 0;
    if ($default['de_send_cost_case'] == '차등' && $total_price >= 0 && $diff > 0) {
        // 금액별차등 : 여러단계의 배송비 적용 가능
        $send_cost_limit = explode(";", $default['de_send_cost_limit']);
        $send_cost_list  = explode(";", $default['de_send_cost_list']);
        $send_cost = 0;
        for ($k = 0; $k < count($send_cost_limit); $k++) {
            // 총판매금액이 배송비 상한가 보다 작다면
            if ($total_price < preg_replace('/[^0-9]/', '', $send_cost_limit[$k])) {
                $send_cost = preg_replace('/[^0-9]/', '', $send_cost_list[$k]);
                break;
            }
        }
    }

    return ($total_send_cost + $send_cost);
}

// 쿠폰 사용체크
function is_used_subscription_coupon($mb_id, $cp_id)
{
    global $g5;

    $used = false;

    $sql = " select count(*) as cnt from {$g5['g5_subscription_coupon_log_table']} where mb_id = '$mb_id' and cp_id = '$cp_id' ";
    $row = sql_fetch($sql);

    if (isset($row['cnt']) && $row['cnt']) {
        $used = true;
    }
    
    return $used;
}

// 상품별 배송비 (정기구독 결제)
function get_subscription_pay_item_sendcost($it_id, $price, $qty, $cart_id, $pay_id)
{
    global $g5, $default;

    $sql = "SELECT it_id, it_sc_type, it_sc_method, it_sc_price, it_sc_minimum, it_sc_qty 
            FROM {$g5['g5_subscription_pay_basket_table']} 
            WHERE it_id = '" . $it_id . "' 
            AND od_id = '" . $cart_id . "' 
            AND pay_id = '" . $pay_id . "' 
            ORDER BY ct_id 
            LIMIT 1";
    $pb = sql_fetch($sql);

    if (!(isset($pb['it_id']) && $pb['it_id']))
        return 0;

    if ($pb['it_sc_type'] > 1) {
        if ($pb['it_sc_type'] == 2) { // 조건부무료
            if ($price >= $pb['it_sc_minimum'])
                $sendcost = 0;
            else
                $sendcost = $pb['it_sc_price'];
        } else if ($pb['it_sc_type'] == 3) { // 유료배송
            $sendcost = $pb['it_sc_price'];
        } else { // 수량별 부과
            if (!$pb['it_sc_qty'])
                $pb['it_sc_qty'] = 1;

            $q = ceil((int)$qty / (int)$pb['it_sc_qty']);
            $sendcost = (int)$pb['it_sc_price'] * $q;
        }
    } else if ($pb['it_sc_type'] == 1) { // 무료배송
        $sendcost = 0;
    } else {
        $sendcost = -1;
    }

    return $sendcost;
}

function subscription_item_delivery_title()
{
    $title = get_subs_option('su_user_delivery_title');

    return $title ? sanitize_input($title) : '배송주기';
}

function subscription_item_select_title()
{
    $title = get_subs_option('su_user_select_title');

    return $title ? sanitize_input($title) : '이용횟수';
}

function subscription_user_delivery_option($index)
{

    $text = (int) get_subs_option('su_user_delivery_default_day') * (int) $index;

    return $index . '주기마다 (' . $text . '일마다)';
}

// 옵션 포맷팅 함수
function subscription_formatted_option($opt) {
    $opt_print = $opt['opt_print'] ? $opt['opt_print'] : ($opt['opt_input'] . ' 일마다'); // 기본 출력 설정
    $opt['opt_input'] = $opt['opt_input'] ? $opt['opt_input'] : 1; // 입력값 없으면 1로 설정

    if (!$opt['opt_print']) {
        if ($opt['opt_date_format'] == 'week') {
            $opt_print = (int)$opt['opt_input'] . '주에 '; // 주 단위 출력
            $opt_print .= isset($opt['opt_etc']) && $opt['opt_etc'] ? get_subscriptionDayOfWeek($opt['opt_etc']) : '한 번';
        } elseif ($opt['opt_date_format'] == 'month') {
            $opt_print = (int)$opt['opt_input'] . '달에 '; // 월 단위 출력
            $opt_print .= isset($opt['opt_etc']) && $opt['opt_etc'] ? (int)$opt['opt_etc'] . '일' : '한 번';
        } elseif ($opt['opt_date_format'] == 'year') {
            $opt_print = '1년에 한 번'; // 연 단위 출력
        }
    }

    $opt_print = str_replace("{입력}", (int)$opt['opt_input'], $opt_print); // 입력값 치환
    $opt_print = str_replace("{결제주기}", get_hangul_date_format($opt['opt_date_format']), $opt_print); // 주기 치환
    $opt_etc_str = $opt['opt_etc'] ? ($opt['opt_date_format'] == 'week' ? get_subscriptionDayOfWeek($opt['opt_etc']) : (int)$opt['opt_etc'] . '일') : ''; // 기타 정보 처리
    return str_replace("{기타}", $opt_etc_str, $opt_print); // 기타 정보 치환
}

function isValidBase64($input)
{
    // 공백, 탭, 개행 제거
    $input = preg_replace('/\s+/', '', $input);

    // URL 디코딩 (우회 방지)
    $input = rawurldecode($input);

    // 길이 확인 (4의 배수)
    if (strlen($input) % 4 !== 0) {
        return false; // Base64는 4의 배수여야 함
    }

    // 정규식으로 Base64 확인
    // $pattern = '/^([A-Za-z0-9+/]{4})*([A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$/';
    $pattern = '#^([A-Za-z0-9+/]{4})*([A-Za-z0-9+/]{2}==|[A-Za-z0-9+/]{3}=)?$#';
    return preg_match($pattern, $input) === 1;
}

// 금액표시
// $it : 상품 배열
function get_subscription_price($it)
{
    global $member;

    $price = $it['it_price'];

    return run_replace('get_subscription_price', (int)$price, $it);
}
