<?php
if (!defined('_GNUBOARD_')) exit;

function get_dir_path($pathComponents, $is_end=0) {
    
    $dir_separator = '/';       // DIRECTORY_SEPARATOR
    $filteredComponents = array_filter($pathComponents);
    
    return implode($dir_separator, $filteredComponents) . ($is_end ? $dir_separator : '');
}

function subscription_category_url($sc_id, $add_param=''){
    global $config;

    if( $config['cf_bbs_rewrite'] ){
        // return get_pretty_url('shop', 'list-'.$sc_id, $add_param);
    }
	
	$add_params = $add_param ? '&'.$add_param : '';
    return G5_SUBSCRIPTION_URL.'/list.php?sc_id='.urlencode($sc_id).$add_params;
}

function subscription_item_url($it_id, $add_param=''){
    global $config;

    if( $config['cf_bbs_rewrite'] ){
        // return get_pretty_url('shop', $it_id, $add_param);
    }
	
	$add_params = $add_param ? '&'.$add_param : '';
    return G5_SUBSCRIPTION_URL.'/item.php?it_id='.urlencode($it_id).$add_params;
}

// cart id 설정
function set_subscription_cart_id($direct)
{
    global $g5, $default, $member;

    if ($direct) {
        $tmp_cart_id = get_session('subs_cart_direct');
        if(!$tmp_cart_id) {
            $tmp_cart_id = get_uniqid();
            set_session('subs_cart_direct', $tmp_cart_id);
        }
    } else {
        // 비회원장바구니 cart id 쿠키설정
        if($default['de_guest_cart_use']) {
            $tmp_cart_id = preg_replace('/[^a-z0-9_\-]/i', '', get_cookie('ck_guest_cart_id'));
            if($tmp_cart_id) {
                set_session('subs_cart_id', $tmp_cart_id);
                //set_cookie('ck_guest_cart_id', $tmp_cart_id, ($default['de_cart_keep_term'] * 86400));
            } else {
                $tmp_cart_id = get_uniqid();
                set_session('subs_cart_id', $tmp_cart_id);
                set_cookie('ck_guest_cart_id', $tmp_cart_id, ($default['de_cart_keep_term'] * 86400));
            }
        } else {
            $tmp_cart_id = get_session('subs_cart_id');
            if(!$tmp_cart_id) {
                $tmp_cart_id = get_uniqid();
                set_session('subs_cart_id', $tmp_cart_id);
            }
        }

        // 보관된 회원장바구니 자료 cart id 변경
        if($member['mb_id'] && $tmp_cart_id) {
            $sql = " update {$g5['g5_subscription_cart_table']}
                        set od_id = '$tmp_cart_id'
                        where mb_id = '{$member['mb_id']}'
                          and ct_direct = '0'
                          and ct_status = '쇼핑' ";
            sql_query($sql);
        }
    }
}

// 정기결제 장바구니 건수 검사
function get_subscription_cart_count($cart_id)
{
    global $g5, $default;

    $sql = " select count(ct_id) as cnt from {$g5['g5_subscription_cart_table']} where od_id = '$cart_id' ";
    $row = sql_fetch($sql);
    $cnt = (int)$row['cnt'];
    return $cnt;
}

function subscription_print_item_options($it_id, $cart_id, $is_get=0)
{
    global $g5;
    
    $subscription_carts = sql_bind_select_array($g5['g5_subscription_cart_table'], 'ct_option, ct_qty, io_price', array('it_id'=>$it_id, 'od_id'=>$cart_id), array('orderBy'=>'io_type asc, ct_id', 'orderType' => 'asc'));
    
    $str = '';
    $i = 0;
    
    $datas =  array();
    
    foreach($subscription_carts as $row) {
        if($i == 0) {
            $str .= '<ul>'.PHP_EOL;
        }
        
        $price_plus = '';
        
        if($row['io_price'] >= 0) {
            $price_plus = '+';
        }
        
        $datas[] = array(
            'ct_option' => $row['ct_option'],
            'ct_qty' => $row['ct_qty'],
            'price_plus' => $price_plus,
            'io_price' => $row['io_price']
            );
        
        $str .= '<li>'.get_text($row['ct_option']).' '.$row['ct_qty'].'개 ('.$price_plus.display_price($row['io_price']).')</li>'.PHP_EOL;
        
        $i++;
    }
    
    if ($is_get) {
        return $datas;
    }
    
    if($i > 0)
        $str .= '</ul>';
    
    return $str;
}

// 장바구니 금액 체크 $is_price_update 가 true 이면 장바구니 가격 업데이트한다. 
function before_check_subscription_cart_price($s_cart_id, $is_ct_select_condition=false, $is_price_update=false, $is_item_cache=false){
    global $g5, $default, $config;

    if (!$s_cart_id) return;

    $select_where_add = array('od_id' => $s_cart_id);
    
    if ($is_ct_select_condition) {
        $select_where_add['ct_select'] = 0;
    }

    $rows = sql_bind_select_array($g5['g5_subscription_cart_table'], '*', $select_where_add);
    
    $check_need_update = false;
    
    foreach($rows as $row) {
        if (!$row['it_id']) continue;

        $it_id = $row['it_id'];
        $it = get_subscription_item($it_id, $is_item_cache);
        
        $update_querys = array();

        if (!$it['it_id']) continue;
        
        if ((int) $it['it_price'] !== (int) $row['ct_price']) {
            // 장바구니 테이블 상품 가격과 상품 테이블의 상품 가격이 다를경우
            $update_querys['ct_price'] = $it['it_price'];
        }

        if ($row['io_id']) {
            /*
            $io_sql = " select * from {$g5['g5_shop_item_option_table']} where it_id = '{$it['it_id']}' and io_id = '{$row['io_id']}' ";
            $io_infos = sql_fetch( $io_sql );
            */
            
            $io_infos = sql_bind_select_fetch($g5['g5_shop_item_option_table'], '*', array('it_id'=>$it['it_id'], 'io_id' => $row['io_id']));
            
            if( $io_infos['io_type'] ){
                $this_io_type = $io_infos['io_type'];
            }
            
            if( $io_infos['io_id'] && (int) $io_infos['io_price'] !== (int) $row['io_price'] ){
                
                echo "<br>".$io_sql;
                print_r($row);
                echo "<br>";
                
                // 장바구니 테이블 옵션 가격과 상품 옵션테이블의 옵션 가격이 다를경우
                $update_querys['io_price'] = $io_infos['io_price'];
            }
        }

        // 포인트
        $compare_point = 0;
        if ($config['cf_use_point']) {
            //print_r( $row );
            
            // DB 에 io_type 이 1이면 상품추가옵션이며, 0이면 상품선택옵션이다
            if($row['io_type'] == 0) {
                $compare_point = get_item_point($it, $row['io_id']);
            } else {
                $compare_point = $it['it_supply_point'];
            }

            if($compare_point < 0)
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
        if( $is_price_update && $update_querys ){
            $conditions = array();

            foreach ($update_querys as $column => $value) {
                $conditions[$column] = $value;
            }

            if ($conditions) {
                sql_bind_update($g5['g5_subscription_cart_table'], $conditions, array('it_id' => $it['it_id'], 'od_id' => $s_cart_id, 'ct_id' => $row['ct_id']));
                /*
                $sql_query = "update `{$g5['g5_subscription_cart_table']}` set {$col_querys} where it_id = '{$it['it_id']}' and od_id = '$s_cart_id' and ct_id =  '{$row['ct_id']}' ";
                sql_query($sql_query, false);
                */
            }
        }
    }

    // 장바구니에 담긴 금액과 실제 상품 금액에 차이가 있다면
    if ($check_need_update) {
        
        echo "왜?";
        exit;
        return false;
    }

    return true;
}

function subscription_order_pay_price($od_id) {
    
    $pay_infos = get_subscription_cart_data($od_id);
    
    $total_price = (int)$pay_infos['tot_sell_price'] + (int)$pay_infos['send_cost'];
    
    return $total_price;
}

function get_subscription_cart_data($s_cart_id, $is_pay=0) {
    global $g5;
    
    // $s_cart_id 로 현재 장바구니 자료 쿼리
    $select_field =
    'a.ct_id,
    a.it_id,
    a.it_name,
    a.ct_price,
    a.ct_point,
    a.ct_qty,
    a.ct_status,
    a.ct_send_cost,
    a.it_sc_type,
    b.ca_id,
    b.ca_id2,
    b.ca_id3,
    b.it_notax';
                                    
    $rows = sql_bind_select_array("{$g5['g5_subscription_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )",
        $select_field,
        array('a.od_id' => $s_cart_id, 'a.ct_select' => 1),
        array('groupBy' => 'a.it_id', 'orderBy' => 'a.ct_id')
    );
    
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
    
    foreach($rows as $row) {
        $select_field2 = 'SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price, SUM(ct_point * ct_qty) as point, SUM(ct_qty) as qty';
        $sum = sql_bind_select_fetch($g5['g5_subscription_cart_table'], $select_field2, array('it_id' => $row['it_id'], 'od_id' => $s_cart_id));
        
        $item_name = preg_replace("/\'|\"|\||\,|\&|\;/", '', $row['it_name']);
        $image = get_it_image($row['it_id'], 80, 80);
        
        // if (!in_array($item_name, $goods)) {
        //    $goods[] = $item_name;
        // }
        
        $images[] = $image;
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
    $send_cost = get_sendcost($s_cart_id);
    
    return array('goods'=>$goods, 'it_options' => $it_options, 'tot_point' => $tot_point, 'tot_sell_price' => $tot_sell_price, 'send_cost' => $send_cost);
    
    // return array('goods'=>$goods, 'images' => $images, 'it_options' => $it_options, 'tot_point' => $tot_point, 'tot_sell_price' => $tot_sell_price, 'send_cost' => $send_cost);
}

// 정기결제 상품의 재고 (창고재고수량 - 주문대기수량)
function get_subscription_it_stock_qty($it_id)
{
    /*
    global $g5;

    $sql = " select it_stock_qty from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
    
    $row = sql_fetch($sql);
    $jaego = (int)$row['it_stock_qty'];

    // 재고에서 빼지 않았고 주문인것만
    $sql = " select SUM(ct_qty) as sum_qty
               from {$g5['g5_subscription_cart_table']}
              where it_id = '$it_id'
                and io_id = ''
                and ct_stock_use = 0
                and ct_status in ('주문', '입금', '준비') ";
                
    $row = sql_fetch($sql);
    $daegi = (int)$row['sum_qty'];

    return $jaego - $daegi;
    */
    
    return get_it_stock_qty($it_id);
}

// 배송비 구함
function get_subscription_sendcost($cart_id, $selected=1)
{
    global $default, $g5;

    $send_cost = 0;
    $total_price = 0;
    $total_send_cost = 0;
    $diff = 0;

    $sql = " select distinct it_id
                from {$g5['g5_subscription_cart_table']}
                where od_id = '$cart_id'
                  and ct_send_cost = '0'
                  and ct_status IN ( '쇼핑', '주문', '입금', '준비', '배송', '완료' )
                  and ct_select = '$selected' ";

    $result = sql_query($sql);
    for($i=0; $sc=sql_fetch_array($result); $i++) {
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

        if($send_cost > 0)
            $total_send_cost += $send_cost;

        if($default['de_send_cost_case'] == '차등' && $send_cost == -1) {
            $total_price += $sum['price'];
            $diff++;
        }
    }

    $send_cost = 0;
    if($default['de_send_cost_case'] == '차등' && $total_price >= 0 && $diff > 0) {
        // 금액별차등 : 여러단계의 배송비 적용 가능
        $send_cost_limit = explode(";", $default['de_send_cost_limit']);
        $send_cost_list  = explode(";", $default['de_send_cost_list']);
        $send_cost = 0;
        for ($k=0; $k<count($send_cost_limit); $k++) {
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
    if(!$ct['it_id'])
        return 0;

    if($ct['it_sc_type'] > 1) {
        if($ct['it_sc_type'] == 2) { // 조건부무료
            if($price >= $ct['it_sc_minimum'])
                $sendcost = 0;
            else
                $sendcost = $ct['it_sc_price'];
        } else if($ct['it_sc_type'] == 3) { // 유료배송
            $sendcost = $ct['it_sc_price'];
        } else { // 수량별 부과
            if(!$ct['it_sc_qty'])
                $ct['it_sc_qty'] = 1;

            $q = ceil((int)$qty / (int)$ct['it_sc_qty']);
            $sendcost = (int)$ct['it_sc_price'] * $q;
        }
    } else if($ct['it_sc_type'] == 1) { // 무료배송
        $sendcost = 0;
    } else {
        $sendcost = -1;
    }

    return $sendcost;
}

// 정기결제 옵션의 재고 (창고재고수량 - 주문대기수량)
function get_subscription_option_stock_qty($it_id, $io_id, $type)
{
    /*
    global $g5;

    $sql = " select io_stock_qty
                from {$g5['g5_shop_item_option_table']}
                where it_id = '$it_id' and io_id = '$io_id' and io_type = '$type' and io_use = '1' ";
    $row = sql_fetch($sql);
    $jaego = (int)$row['io_stock_qty'];

    // 재고에서 빼지 않았고 주문인것만
    $sql = " select SUM(ct_qty) as sum_qty
               from {$g5['g5_subscription_cart_table']}
              where it_id = '$it_id'
                and io_id = '$io_id'
                and io_type = '$type'
                and ct_stock_use = 0
                and ct_status in ('주문', '입금', '준비') ";
    $row = sql_fetch($sql);
    $daegi = (int)$row['sum_qty'];

    return $jaego - $daegi;
    */
    
    return get_option_stock_qty($it_id, $io_id, $type);
}

// 정기결제 장바구니 상품삭제
function subscription_cart_item_clean() {
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
    if($cart_stock_limit > 0) {
        if($cart_stock_limit > $keep_term * 24)
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

function subscription_is_soldout($it_id, $is_cache=false) {
    return is_soldout($it_id, $is_cache);
}

function subscription_member_cert_check($id, $type){
    global $g5, $member;

    $msg = '';
    
    return $msg;
}

function get_subscription_category($sc_id) {
    global $g5, $g5_object;
    
    $add_query = '';
    
    $sql = " select * from {$g5['g5_shop_category_table']} where sc_id = '{$sc_id}' $add_query ";
    return sql_fetch($sql);
}

function get_subscription_order($od_id) {
    global $g5;
    
    return sql_bind_select_fetch($g5['g5_subscription_order_table'], '*', array('od_id'=>$od_id));
    
    $sql = " select * from {$g5['g5_subscription_order_table']} where od_id = '{$od_id}' ";
    return sql_fetch($sql);
}

function get_subscription_item($it_id, $is_cache=false, $add_query='') {
    global $g5, $g5_object;

    $add_query_key = $add_query ? 'subscription_'.md5($add_query) : '';

    $item = $is_cache ? $g5_object->get('subscription', $it_id, $add_query_key) : null;

    if( !$item ){
        $sql = " select * from {$g5['g5_shop_item_table']} where it_id = '{$it_id}' and it_class_num IN (1, 2) $add_query ";
        $item = sql_fetch($sql);

        $g5_object->set('subscription', $it_id, $item, $add_query_key);
    }
    
    if( isset($item['it_basic']) ) {
        $item['it_basic'] = conv_content($item['it_basic'], 1);
    }

    if( ! isset($item['it_id']) ){
        $item['it_id'] = '';
    }

    return $item;
}

function get_subscription_item_with_category($it_id, $seo_title='', $add_query=''){
    
    global $g5, $default;

    if( $seo_title ){
        $sql = " select a.*, b.ca_name, b.ca_use from {$g5['g5_shop_item_table']} a, {$g5['g5_shop_category_table']} b where a.it_seo_title = '".sql_real_escape_string(generate_seo_title($seo_title))."' and a.ca_id = b.ca_id $add_query";
    } else {
        $sql = " select a.*, b.ca_name, b.ca_use from {$g5['g5_shop_item_table']} a, {$g5['g5_shop_category_table']} b where a.it_id = '$it_id' and a.ca_id = b.ca_id $add_query";
    }
    
    $item = sql_fetch($sql);

    if( isset($item['it_basic']) ) {
        $item['it_basic'] = conv_content($item['it_basic'], 1);
    }

    return $item;
}

function subscription_seo_title_update($it_id, $is_edit=false){
    global $g5;

	$subscription_item_cache = $is_edit ? false : true;
    $item = get_subscription_item($it_id, $subscription_item_cache);

    if( (! $item['it_seo_title'] || $is_edit) && $item['it_name'] ){
        $it_seo_title = exist_seo_title_recursive('subscription', generate_seo_title($item['it_name']), $g5['g5_shop_item_table'], $item['it_id']);

        if( isset($item['it_seo_title']) && $it_seo_title !== $item['it_seo_title'] ){
            $sql = " update `{$g5['g5_shop_item_table']}` set it_seo_title = '{$it_seo_title}' where it_id = '{$item['it_id']}' ";
            sql_query($sql);
        }
    }
}

class SubscriptionList extends item_list {
    
    // $type        : 상품유형 (기본으로 1~5까지 사용)
    // $list_skin   : 상품리스트를 노출할 스킨을 설정합니다. 스킨위치는 skin/shop/쇼핑몰설정스킨/type??.skin.php
    // $list_mod    : 1줄에 몇개의 상품을 노출할지를 설정합니다.
    // $list_row    : 상품을 몇줄에 노출할지를 설정합니다.
    // $img_width   : 상품이미지의 폭을 설정합니다.
    // $img_height  : 상품이미지의 높이을 설정합니다. 0 으로 설정하는 경우 썸네일 이미지의 높이는 폭에 비례하여 생성합니다.
    //function __construct($type=0, $list_skin='', $list_mod='', $list_row='', $img_width='', $img_height=0, $ca_id='') {
    function __construct($list_skin='', $list_mod='', $list_row='', $img_width='', $img_height=0) {
        parent::__construct($list_skin, $list_mod, $list_row, $img_width, $img_height);
        $this->set_href(G5_SUBSCRIPTION_URL.'/item.php?it_id=');
        $this->count++;
    }
    
    // 리스트 스킨을 바꾸고자 하는 경우에 사용합니다.
    // 리스트 스킨의 위치는 skin/shop/쇼핑몰설정스킨/type??.skin.php 입니다.
    // 특별히 설정하지 않는 경우 상품유형을 사용하는 경우는 쇼핑몰설정 값을 그대로 따릅니다.
    function set_list_skin($list_skin) {
        global $default;
        if ($this->is_mobile) {
            $this->list_skin = $list_skin ? $list_skin : G5_MSUBSCRIPTION_SKIN_PATH.'/'.preg_replace('/[^A-Za-z0-9 _ .-]/', '', $default['de_mobile_type'.$this->type.'_list_skin']);
        } else {
            $this->list_skin = $list_skin ? $list_skin : G5_SUBSCRIPTION_SKIN_PATH.'/'.preg_replace('/[^A-Za-z0-9 _ .-]/', '', $default['de_type'.$this->type.'_list_skin']);
        }
    }
    
    // 외부에서 쿼리문을 넘겨줄 경우에 담아둡니다.
    function set_query($query='') {
        
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
    
    /*
    // class 에 설정된 값으로 최종 실행합니다.
    function run() {

        global $g5, $config, $member, $default;
        
        $list = array();

        if ($this->query) {

            $sql = $this->query;
            $result = sql_query($sql);
            $this->total_count = @sql_num_rows($result);

        } else {

            $where = array();
            if ($this->use) {
                $where[] = " it_use = '1' ";
            }

            if ($this->type) {
                $where[] = " it_type{$this->type} = '1' ";
            }

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

            $sql = $sql_select . $sql_common . $sql_where . $sql_order . $sql_limit;
            
            echo $sql;
            
            $result = sql_query($sql);

            if ($this->is_page) {
                $sql2 = " select count(*) as cnt " . $sql_common . $sql_where;
                $row2 = sql_fetch($sql2);
                $this->total_count = $row2['cnt'];
            }
        }

        if( isset($result) && $result ){
            while ($row=sql_fetch_array($result)) {
                
                if( isset($row['it_seo_title']) && ! $row['it_seo_title'] ){
                    shop_seo_title_update($row['it_id']);
                }
                
                $row['it_basic'] = conv_content($row['it_basic'], 1);
                $list[] = $row;
            }

            if(function_exists('sql_data_seek')){
                sql_data_seek($result, 0);
            }
        }

        $file = $this->list_skin;
        
        if ($this->list_skin == "") {
            return $this->count."번 item_list() 의 스킨파일이 지정되지 않았습니다.";
        } else if (!file_exists($file)) {
            return $file." 파일을 찾을 수 없습니다.";
        } else {
            ob_start();
            $list_mod = $this->list_mod;
            include($file);
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }
    }
    */
}

// 상품이미지에 유형 아이콘 출력
function subscription_item_icon($it) {
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
                where cp_start <= '".G5_TIME_YMD."'
                  and cp_end >= '".G5_TIME_YMD."'
                  and (
                        ( cp_method = '0' and cp_target = '{$it['it_id']}' )
                        OR
                        ( cp_method = '1' and ( cp_target IN ( '{$it['sc_id']}', '{$it['sc_id2']}', '{$it['sc_id3']}' ) ) )
                      ) ";
    $row = sql_fetch($sql);
    if($row['cnt'])
        $icon .= '<span class="shop_icon shop_icon_coupon">쿠폰</span>';

    $icon .= '</span>';

    return $icon;
}

// 상품 이미지를 얻는다
function get_subscription_it_image($it_id, $width, $height=0, $anchor=false, $img_id='', $img_alt='', $is_crop=false)
{
    global $g5;

    if(!$it_id || !$width)
        return '';

    $row = get_subscription_item($it_id, true);

    if(!$row['it_id'])
        return '';

    $filename = $thumb = $img = '';
    
    $img_width = 0;
    for($i=1;$i<=10; $i++) {
        $file = G5_DATA_PATH.'/item/'.$row['it_img'.$i];
        if(is_file($file) && $row['it_img'.$i]) {
            $size = @getimagesize($file);
            if(! isset($size[2]) || $size[2] < 1 || $size[2] > 3)
                continue;

            $filename = basename($file);
            $filepath = dirname($file);
            $img_width = $size[0];
            $img_height = $size[1];

            break;
        }
    }

    if($img_width && !$height) {
        $height = round(($width * $img_height) / $img_width);
    }

    if($filename) {
       $thumb = thumbnail($filename, $filepath, $filepath, $width, $height, false, $is_crop, 'center', false, $um_value='80/0.5/3');
    }

    if($thumb) {
        $file_url = str_replace(G5_PATH, G5_URL, $filepath.'/'.$thumb);
        $img = '<img src="'.$file_url.'" width="'.$width.'" height="'.$height.'" alt="'.$img_alt.'"';
    } else {
        $img = '<img src="'.G5_SHOP_URL.'/img/no_image.gif" width="'.$width.'"';
        if($height)
            $img .= ' height="'.$height.'"';
        $img .= ' alt="'.$img_alt.'"';
    }

    if($img_id)
        $img .= ' id="'.$img_id.'"';
    $img .= '>';

    if($anchor)
        $img = $img = '<a href="'.subscription_item_url($it_id).'">'.$img.'</a>';

    return run_replace('get_subscription_it_image_tag', $img, $thumb, $it_id, $width, $height, $anchor, $img_id, $img_alt, $is_crop);
}

function get_subscription_navigation_data($is_cache, $sc_id, $sc_id2='', $sc_id3=''){
    
    $all_categories = get_subscription_category_array($is_cache);

    $datas = array();
    
    if( strlen($sc_id) >= 2 && $all_categories ){
        foreach((array) $all_categories as $category1 ){
            $datas[0][] = $category1['text'];
        }
    }

    $select_sc_id = $sc_id2 ? $sc_id2 : $sc_id;
    $item_categories2 = $select_sc_id ? get_subscription_category_by($is_cache, 'sc_id', $select_sc_id) : array();

    if( strlen($select_sc_id) >= 4 && $item_categories2 ){
        foreach((array) $item_categories2 as $key=>$category2 ){
            if( $key === 'text' ) continue;

            $datas[1][] = $category2['text'];
        }
    }

    $select_sc_id = $sc_id3 ? $sc_id3 : $sc_id;
    $item_categories3 = $select_sc_id ? get_subscription_category_by($is_cache, 'sc_id', $select_sc_id) : array();

    if( strlen($select_sc_id) >= 6 && $item_categories3 && isset($item_categories3[substr($select_sc_id,0,4)]) ){
        $sub_categories = $item_categories3[substr($select_sc_id,0,4)];

        foreach((array) $sub_categories as $key=>$category3 ){
            if( $key === 'text' ) continue;

            $datas[2][] = $category3['text'];
        }
    }

    return $datas;
}

function get_subscription_category_by($is_cache, $case, $value){
    
    if( $case === 'sc_id' ){
        $categories = get_subscription_category_array($is_cache);

        $key = substr(preg_replace('/[^0-9a-z]/i', '', $value), 0, 2);
        
        if( isset($categories[$key]) ){
            return $categories[$key];
        }
    }

    return array();
}

function get_subscription_category_array($is_cache=false){

    static $categories = array();
    
    $categories = run_replace('get_subscription_category_array', $categories, $is_cache);

    if( $is_cache && !empty($categories) ){
        return $categories;
    }

    $result = sql_query(get_subscription_category_sql('', 2));

    for($i=0; $row=sql_fetch_array($result); $i++) {

        $row['url'] = subscription_category_url($row['sc_id']);
        $categories[$row['sc_id']]['text'] = $row;
        
        if( $row['sc_id'] ){
            $result2 = sql_query(get_subscription_category_sql($row['sc_id'], 4));

            for($j=0; $row2=sql_fetch_array($result2); $j++) {

                $row2['url'] = subscription_category_url($row2['sc_id']);
                $categories[$row['sc_id']][$row2['sc_id']]['text'] = $row2;
                
                if( $row2['sc_id'] ){
                    $result3 = sql_query(get_subscription_category_sql($row2['sc_id'], 6));
                    for($k=0; $row3=sql_fetch_array($result3); $k++) {

                        $row3['url'] = subscription_category_url($row3['sc_id']);
                        $categories[$row['sc_id']][$row2['sc_id']][$row3['sc_id']]['text'] = $row3;
                    }
                }   //end if
            }   //end for
        }   //end if
    }   //end for
    
    return $categories;
}

function get_subscription_category_sql($sc_id, $len){
    global $g5;

    $sql = " select * from {$g5['g5_shop_category_table']}
                where sc_use = '1' ";
    if($sc_id)
        $sql .= " and sc_id like '$sc_id%' ";
    $sql .= " and length(sc_id) = '$len' order by sc_order, sc_id ";

    return $sql;
}

function get_weekend_yoil($date, $full=0) {
    
    return get_yoil($date, $full);
}

// 상품명과 건수를 반환
function get_subscription_goods($cart_id) {
    global $g5;

    // 상품명만들기
    $row = sql_fetch(" select a.it_id, b.it_name from {$g5['g5_subscription_cart_table']} a, {$g5['g5_shop_item_table']} b where a.it_id = b.it_id and a.od_id = '$cart_id' order by ct_id limit 1 ");
    
    // 상품명에 "(쌍따옴표)가 들어가면 오류 발생함
    $goods['it_id'] = $row['it_id'];
    $goods['full_name']= addslashes($row['it_name']);
    // 특수문자제거
    $goods['full_name'] = preg_replace ("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", "",  $goods['full_name']);

    // 상품건수
    $row = sql_fetch(" select count(*) as cnt from {$g5['g5_subscription_cart_table']} where od_id = '$cart_id' ");
    $cnt = ($row['cnt']) ? (int) $row['cnt'] - 1 : 0;
    
    if ($cnt) {
        $goods['full_name'] .= ' 외 '.$cnt.'건';
    }
    
    $goods['count'] = $row['cnt'];

    return $goods;
}

function get_subscription_order_goods($od_id) {
    global $g5;
    
    $sql = " select * from {$g5['g5_subscription_cart_table']} where od_id = '$od_id'";
    $cart = sql_fetch($sql);
}

function subscription_order_pay($od, $pg_data, $pay_round_no) {
    global $g5;
    
    // $od['py_receipt_price'] ?
    // inicis : $pg_data['price']
    
    $py_receipt_time = date('Y년m월d일', strtotime($pg_data['payDate'].$pg_data['payTime']));
    
    $subscription_id = $pg_data['orderId'];
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
    
    $inserts = array(
        'od_id' => $od['od_id'],
        'mb_id' => $od['mb_id'],
        'subscription_id' => $subscription_id,
        'py_name' => $od['od_name'],
        'py_email' => $od['od_email'],
        'py_hp' => $od['od_hp'],
        'py_b_name' => $od['od_b_name'],
        'py_b_hp' => $od['od_b_hp'],
        'py_b_zip1' => $od['od_b_zip1'],
        'py_b_zip2' => $od['od_b_zip2'],
        'py_b_addr1' => $od['od_b_addr1'],
        'py_b_addr2' => $od['od_b_addr2'],
        'py_b_addr3' => $od['od_b_addr3'],
        'py_b_addr_jibeon' => $od['od_b_addr_jibeon'],
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
        );
     
     
    print_r2($inserts);
     
    $columns = implode(', ', array_keys($inserts));
    $values = implode("', '", array_values($inserts));

    // 주문서에 입력
    $sql = "INSERT INTO `{$g5['g5_subscription_pay_table']}`($columns) VALUES ('$values')";
    
    // echo $sql;
    
    sql_query($sql);
    
    $insert_id = sql_insert_id();
    
    if ($insert_id) {
        // 상품명만들기
        $result = sql_query(" select * from {$g5['g5_subscription_cart_table']} where od_id = '".$od['od_id']."' order by ct_id asc ");
        
        // 결제 될때 당시 결제된 장바구니 정보를 따로 저장한다. (pay_basket 테이블에)
        for ($i = 0; $row = sql_fetch_array($result); ++$i) {
            $inserts = array(
                'od_id' => $row['od_id'],
                'pay_id' => $insert_id,
                'mb_id' => $row['mb_id'],
                'it_id' => $row['it_id'],
                'it_name' => $row['it_name'],
                'it_sc_type' => $row['it_sc_type'],
                'it_sc_method' => $row['it_sc_method'],
                'it_sc_price' => $row['it_sc_price'],
                'it_sc_minimum' => $row['it_sc_minimum'],
                'it_sc_qty' => $row['it_sc_qty'],
                'pb_status' => $row['ct_status'],
                'pb_history' => $row['ct_history'],
                'pb_price' => $row['ct_price'],
                'pb_point' => $row['ct_point'],
                'cp_price' => $row['cp_price'],
                'pb_point_use' => $row['ct_point_use'],
                'pb_stock_use' => $row['ct_stock_use'],
                'pb_option' => $row['ct_option'],
                'pb_qty' => $row['ct_qty'],
                'pb_notax' => $row['ct_notax'],
                'io_id' => $row['io_id'],
                'io_type' => $row['io_type'],
                'io_price' => $row['io_price'],
                'pb_time' => $row['ct_time'],
                'pb_ip' => $row['ct_ip'],
                'pb_send_cost' => $row['ct_send_cost'],
                'pb_direct' => $row['ct_direct'],
                'pb_select' => $row['ct_select'],
                'pb_select_time' => $row['ct_select_time'],
                'pb_subscription_number' => $row['ct_subscription_number'],
                'pb_firstshipment_date' => $row['ct_firstshipment_date'],
                'pb_date_format' => $row['ct_date_format']
                );
            
            $columns = implode(', ', array_keys($inserts));
            $values = implode("', '", array_values($inserts));
            
            // 주문서에 입력
            $sql = "INSERT INTO `{$g5['g5_subscription_pay_basket_table']}`($columns) VALUES ('$values')";
            
            echo $sql;
            
            sql_query($sql, false);
        }
    }
    
    return $insert_id;
}

function subscription_cron_token() {
    global $g5;
    
    $str = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';
    $str .= G5_TABLE_PREFIX.G5_SHOP_TABLE_PREFIX.G5_TOKEN_ENCRYPTION_KEY;
    
    return md5($str);
}

function get_next_delivery_date($od){
    return calculateNextDeliveryDate($od);
}

function getBusinessDaysBefore($date, $businessDays = 0, $holidays=array()) {
    // $date: 기준 날짜 (YYYY-MM-DD 형식의 문자열)
    // $businessDays: 몇 영업일 전으로 이동할 것인지
    // $holidays: 공휴일 배열 (YYYY-MM-DD 형식의 문자열 배열)

    // 기준 날짜를 타임스탬프로 변환
    $timestamp = strtotime($date);
    
    if ($businessDays) {
        while ($businessDays > 0) {
            // 하루 전으로 이동
            $timestamp = strtotime('-1 day', $timestamp);
            
            // 요일 가져오기 (0: 일요일, 6: 토요일)
            $dayOfWeek = date('w', $timestamp);
            
            // 날짜 포맷 (YYYY-MM-DD)
            $formattedDate = date('Y-m-d', $timestamp);
            
            // 주말(토, 일)이 아니고 공휴일이 아니면 영업일로 간주
            if ($dayOfWeek != 0 && $dayOfWeek != 6 && !($holidays && in_array($formattedDate, $holidays))) {
                $businessDays--;
            }
        }
    } else {
        // 영업일 검증 (0일일 때 배송일 확인)
        while (true) {
            $dayOfWeek = date('w', $timestamp);
            $formattedDate = date('Y-m-d', $timestamp);

            // 영업일이면 반환
            if ($dayOfWeek != 0 && $dayOfWeek != 6 && !($holidays && in_array($formattedDate, $holidays))) {
                break;
            }

            // 주말 또는 공휴일이면 하루 전으로 이동
            $prevTimestamp = strtotime('-1 day', $timestamp);
            $nextTimestamp = strtotime('+1 day', $timestamp);
            
            $prevDayOfWeek = date('w', $prevTimestamp);
            $nextDayOfWeek = date('w', $nextTimestamp);
            
            $prevDate = date('Y-m-d', $prevTimestamp);
            $nextDate = date('Y-m-d', $nextTimestamp);
            
            // 이전 날짜가 영업일인지 확인
            if ($prevDayOfWeek != 0 && $prevDayOfWeek != 6 && !in_array($prevDate, $holidays)) {
                $timestamp = $prevTimestamp;
            }
            // 다음 날짜가 영업일인지 확인
            elseif ($nextDayOfWeek != 0 && $nextDayOfWeek != 6 && !in_array($nextDate, $holidays)) {
                $timestamp = $nextTimestamp;
            } 
            // 만약 전후 날짜가 모두 주말/공휴일이면 루프를 계속 진행
            else {
                $timestamp = strtotime('-1 day', $timestamp);
            }
        }
    }


    return date('Y-m-d', $timestamp).' 09:00:01';
}

function getBusinessDaysNext($date, $businessDays = 0, $holidays = array()) {
    // $date: 기준 날짜 (YYYY-MM-DD 형식의 문자열)
    // $businessDays: 몇 영업일 후로 이동할 것인지
    // $holidays: 공휴일 배열 (YYYY-MM-DD 형식의 문자열 배열)

    // 기준 날짜를 타임스탬프로 변환
    $timestamp = strtotime($date);

    if ($businessDays) {
        while ($businessDays > 0) {
            // 하루 앞으로 이동
            $timestamp = strtotime('+1 day', $timestamp);

            // 요일 가져오기 (0: 일요일, 6: 토요일)
            $dayOfWeek = date('w', $timestamp);

            // 날짜 포맷 (YYYY-MM-DD)
            $formattedDate = date('Y-m-d', $timestamp);

            // 주말(토, 일)이 아니고 공휴일이 아니면 영업일로 간주
            if ($dayOfWeek != 0 && $dayOfWeek != 6 && !($holidays && in_array($formattedDate, $holidays))) {
                $businessDays--;
            }
        }
    } else {
        // 영업일 검증 (0일일 때 배송일 확인)
        while (true) {
            $dayOfWeek = date('w', $timestamp);
            $formattedDate = date('Y-m-d', $timestamp);

            // 영업일이면 반환
            if ($dayOfWeek != 0 && $dayOfWeek != 6 && !($holidays && in_array($formattedDate, $holidays))) {
                break;
            }

            // 주말 또는 공휴일이면 하루 앞으로 이동
            $timestamp = strtotime('+1 day', $timestamp);
        }
    }

    return date('Y-m-d', $timestamp) . ' 09:00:01';
}

function subscription_serial_encode($data, $od=null) {
    return base64_encode(serialize($data));
}

function subscription_serial_decode($data, $od=null) {
    return unserialize(base64_decode($data));
}

function calculateNextDeliveryDate($od){
    
    $timestamp = !is_null_date($od['od_hope_date']) ? strtotime($od['od_hope_date']) : strtotime($od['od_time']);
    
    if (isset($od['next_delivery_date']) && $od['next_delivery_date']) {
        $timestamp = strtotime($od['next_delivery_date']);
    }
    
    $od_subscription_selected_data = subscription_serial_decode($od['od_subscription_selected_data']);
    $od_subscription_selected_number = subscription_serial_decode($od['od_subscription_selected_number']);
    
    $od_subscription_date_format = isset($od['od_subscription_date_format']) ? $od['od_subscription_date_format'] : null;
    $od_subscription_number = isset($od['od_subscription_number']) ? $od['od_subscription_number'] : null;
    
    if (isset($od_subscription_selected_data['opt_date_format']) && $od_subscription_selected_data['opt_date_format']) {
        $od_subscription_date_format = $od_subscription_selected_data['opt_date_format'];
    }
    
    if (isset($od_subscription_selected_data['opt_input']) && $od_subscription_selected_data['opt_input']) {
        $od_subscription_number = (int) $od_subscription_selected_data['opt_input'];
    }
    
    $interval = $od_subscription_date_format ? $od_subscription_date_format : 'day';
    $plus = abs($od_subscription_number);
    
    // 주어진 interval에 따라 날짜를 증가시킴
    switch ($interval) {
        case 'day':
            $timestamp = strtotime('+'.$plus.' day', $timestamp);
            break;
        case 'week':
            $timestamp = strtotime('+'.$plus.' week', $timestamp);
            break;
        case 'month':
            $timestamp = strtotime('+'.$plus.' month', $timestamp);
            break;
        case 'year':
            $timestamp = strtotime('+'.$plus.' year', $timestamp);
            break;
        default:
            throw new Exception("Unknown billing interval: $interval");
    }

    // 다음 청구일을 YYYY-MM-DD 형식으로 반환
    return getBusinessDaysBefore(date('Y-m-d H:i:s', $timestamp), $config_before_pay_date);
}

function calculateNextBillingDate2($od, $od_hope_date=null){
    
    // 현재 날짜를 DateTime 객체로 변환
    if (is_null_date($od['next_billing_date'])) {
        $timestamp = G5_SERVER_TIME;
    } else {
        $timestamp = strtotime($od['next_billing_date']);
    }
    
    /*
    // 이 코드를 넣으면 안됨 제거해야 됨
    if ($od_hope_date === null && !is_null_date($od['od_hope_date'])) {
        $od_hope_date = $od['od_hope_date'];
    }
    */
    
    $od_subscription_selected_data = subscription_serial_decode($od['od_subscription_selected_data']);
    $od_subscription_selected_number = subscription_serial_decode($od['od_subscription_selected_number']);
    
    $od_subscription_date_format = isset($od['od_subscription_date_format']) ? $od['od_subscription_date_format'] : null;
    $od_subscription_number = isset($od['od_subscription_number']) ? $od['od_subscription_number'] : null;
    
    if (isset($od_subscription_selected_data['opt_date_format']) && $od_subscription_selected_data['opt_date_format']) {
        $od_subscription_date_format = $od_subscription_selected_data['opt_date_format'];
    }
    
    if (isset($od_subscription_selected_data['opt_input']) && $od_subscription_selected_data['opt_input']) {
        $od_subscription_number = (int) $od_subscription_selected_data['opt_input'];
    }
    
    echo $od_subscription_date_format;
    
    echo "<Br>";
    
    echo $od_subscription_number;
    
    echo "<Br>";
    
    // 아래 구문은 틀렸다
    //if (isset($od_subscription_selected_number['use_input']) && $od_subscription_selected_number['use_input']) {
    //    $od_subscription_number = (int) $od_subscription_selected_number['use_input'];
    //}
    
    $config_before_pay_date = (int) get_subs_option('su_auto_payment_lead_days');
    
    // 희망배송일이 있으면
    if ($od_hope_date) {
        $nextdate = getBusinessDaysBefore($od_hope_date, $config_before_pay_date);
        
        return $nextdate.' 09:00:01';
    }
    
    $interval = $od_subscription_date_format ? $od_subscription_date_format : 'day';
    $plus = abs($od_subscription_number);
    
    $is_check_before = false;
    
    // 주어진 interval에 따라 날짜를 증가시킴
    switch ($interval) {
        case 'day':
            $timestamp = strtotime('+'.$plus.' day', $timestamp);
            break;
        case 'week':
            $timestamp = strtotime('+'.$plus.' week', $timestamp);
            break;
        case 'month':
            $timestamp = strtotime('+'.$plus.' month', $timestamp);
            $is_check_before = true;
            break;
        case 'year':
            $timestamp = strtotime('+'.$plus.' year', $timestamp);
            $is_check_before = true;
            break;
        default:
            throw new Exception("Unknown billing interval: $interval");
    }
    
    if ($is_check_before) {
        
        // 이전날로 구함
        return getBusinessDaysBefore(date('Y-m-d H:i:s', $timestamp));
    }
    
    // 다음날로 구함
    return getBusinessDaysnext(date('Y-m-d H:i:s', $timestamp));
    
    // 다음 청구일을 YYYY-MM-DD 형식으로 반환
    // return getBusinessDaysBefore(date('Y-m-d H:i:s', $timestamp), $config_before_pay_date);
    
}

//결제방식 이름을 체크하여 치환 대상인 문자열은 따로 리턴합니다.
function get_subscription_pay_name_replace($payname, $od=array(), $is_client=0) {
    
    // 기존에 저장되어 있던 카드를 재사용한 경우
    if ($payname === '카드재사용') {
        return '신용카드';
    }
    
    return $payname;
}

function calculateNextBillingDate($od, $od_hope_date=null){
    
    return calculateNextBillingDate2($od, $od_hope_date);
    
    // 현재 날짜를 DateTime 객체로 변환
    if (is_null_date($od['next_billing_date'])) {
        $timestamp = G5_SERVER_TIME;
    } else {
        $timestamp = strtotime($od['next_billing_date']);
    }
    
    if ($od_hope_date) {
        $nextdate = getBusinessDaysBefore($od_hope_date, (int) get_subs_option('su_auto_payment_lead_days'));
        
        return $nextdate.' 00:00:01';
    }

    
    $interval = $od['od_subscription_date_format'] ? $od['od_subscription_date_format'] : 'day';
    $plus = abs($od['od_subscription_number']);
        
    // 주어진 interval에 따라 날짜를 증가시킴
    switch ($interval) {
        case 'day':
            $timestamp = strtotime('+'.$plus.' day', $timestamp);
            break;
        case 'week':
            $timestamp = strtotime('+'.$plus.' week', $timestamp);
            break;
        case 'month':
            $timestamp = strtotime('+'.$plus.' month', $timestamp);
            break;
        case 'year':
            $timestamp = strtotime('+'.$plus.' year', $timestamp);
            break;
        default:
            throw new Exception("Unknown billing interval: $interval");
    }

    // 다음 청구일을 YYYY-MM-DD 형식으로 반환
    return date('Y-m-d H:i:s', $timestamp);
    
}

function get_nicepay_api_url(){
    
    // 테스트인(샌드박스) 경우 나이스페이 api url
    if (get_subs_option('su_card_test')) {
        return 'https://sandbox-api.nicepay.co.kr';
    }
    
    // 실서버(운영계) 나이스페이 api url
    return 'https://api.nicepay.co.kr';
}

function expire_nicepay_billing($bid) {
	global $clientId;
	global $secretKey;
    
    // 
	try {
		$res = requestPost(
			get_nicepay_api_url()."/v1/subscribe/" . $bid . "/expire",
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

function nocache_nostore_subscription_headers() {
    // 일부 브라우저 또는 앞으로 브라우저가 업데이트 된다면 아래의 방법이 안될수도 있습니다.
    
    if (headers_sent()) return;
    
    header_remove('Last-Modified');

    header('Expires: Sat, 17 Jan 1999 01:00:00 GMT');
    header('Cache-Control: no-transform, no-cache, no-store, must-revalidate');
}

function kcp_billing($od, $tmp_cart_id='') {
    global $g5;
    
    include_once(G5_SUBSCRIPTION_PATH.'/settle_kcp.inc.php');
    
    $site_cd            = get_subs_option('su_kcp_mid'); // 사이트 코드
    // 인증서 정보(직렬화)
    $kcp_cert_info      = get_subs_option('su_kcp_cert_info');
    
    $cart_id = $tmp_cart_id ? $tmp_cart_id : $od['od_id'];
    $goodsname = get_subscription_goods($cart_id);
    
    $cust_ip            = "";
    $currency           = '410'; // 화폐 단위
    // $ordr_idxx          = $od['od_id'].'_'.md5($od['mb_id']).'_'.uniqid(); // 주문번호 
    $ordr_idxx          = generate_subscription_id($od['od_id']); // 주문번호 
    $good_name          = $goodsname['full_name']; // 상품명
    $buyr_name          = $od['od_name']; // 주문자명
    $buyr_mail          = $od['od_email']; // 주문자 E-mail
    $buyr_tel1          = $od['od_tel']; // 주문자 전화번호번호
    $buyr_tel2          = $od['od_hp']; // 주문자 휴대폰번호

    $bt_batch_key       = $od['card_billkey']; // 배치키 정보
    $bt_group_id        = get_subs_option('su_kcp_group_id'); // 배치키 그룹아이디
    
    $posts = array(
        'pay_method' => SUBSCRIPTION_DEFAULT_PAYMETHOD,
        'ordr_idxx' => $ordr_idxx,
        'good_name' => $good_name,
        'good_mny' => $od['od_receipt_price'],
        'buyr_name' => $buyr_name,
        'buyr_mail' => $buyr_mail,
        'buyr_tel1' => $buyr_tel1,
        'buyr_tel2' => $buyr_tel2,
        'req_tx' => 'pay',  // req_tx : 요청종류 승인(pay)/취소,매입(mod) 요청시 사용
        'currency' => $currency,
        'mod_type' => '',   // 변경TYPE(승인취소시 필요)
        'mod_desc' => '',   // 변경사유
        'card_pay_method' => 'Batch',
        'quotaopt' => "00",
        'bt_group_id' => $bt_group_id,
        'bt_batch_key' => $bt_batch_key,
    );
    
    include_once(G5_SUBSCRIPTION_PATH.'/kcp/pay_pp_cli_hub.php');
    
    run_event('subscription_order_pg_pay', 'kcp', $results, $posts);
    
    if (isset($results['res_cd']) && $results['res_cd'] === '0000') {
        
        // 공통형식에 맞추어야 한다.
        $results['orderId'] = $results['ordr_idxx'];
        $results['payMethod'] = $results['pay_method'];
        $results['amount'] = $results['good_mny'];
        $results['receiptUrl'] = '';    // kcp 는 영수증 url 이 없다.
        $results['cardname'] = $results['card_name'];
        $results['cardnumber'] = mask_card_number($results['card_no']);   // kcp 는 정기결제시 결제카드 번호를 다 알려주지만, 여기서는 마스킹하여 저장한다.
        $results['py_app_no'] = $results['app_no'];
        $results['tid'] = $results['tno'];
        
        return array('code'=>'success', 'message'=>$results['res_msg'], 'response'=>$results);
    } else {
        return array('code'=>'fail', 'message'=>$results['res_cd'].':'.$results['res_msg'], 'response'=>$results);
    }
    
    return array();
}

function kcp_new_billing($od, $tmp_cart_id='') {
    global $g5;
    
    include(G5_SUBSCRIPTION_PATH.'/settle_kcp.inc.php');
    
    $site_cd            = get_subs_option('su_kcp_mid'); // 사이트 코드
    // 인증서 정보(직렬화)
    $kcp_cert_info      = get_subs_option('su_kcp_cert_info');
    
    $cart_id = $tmp_cart_id ? $tmp_cart_id : $od['od_id'];
    $goodsname = get_subscription_goods($cart_id);
    
    $cust_ip            = "";
    $currency           = '410'; // 화폐 단위
    // $ordr_idxx          = $od['od_id'].'_'.md5($od['mb_id']).'_'.uniqid(); // 주문번호 
    $ordr_idxx          = generate_subscription_id($od['od_id']);
    $good_name          = $goodsname['full_name']; // 상품명
    $buyr_name          = $od['od_name']; // 주문자명
    $buyr_mail          = $od['od_email']; // 주문자 E-mail
    $buyr_tel2          = $od['od_hp']; // 주문자 휴대폰번호

    $bt_batch_key       = $od['card_billkey']; // 배치키 정보
    $bt_group_id        = get_subs_option('su_kcp_group_id'); // 배치키 그룹아이디
    
    $data = array(
        "site_cd"        => $site_cd,
        "kcp_cert_info"  => $kcp_cert_info,
        "pay_method"     => "CARD",
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
    
    if (function_exists('add_log')) {
        add_log($data, false, 'kcp');
    }
    
    $req_data = json_encode($data);
    
    $header_data = array( "Content-Type: application/json", "charset=utf-8" );
    
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
    
    if (function_exists('add_log')) {
        add_log($res_data, false, 'kcp');
    }
    
    curl_close($ch);
    
    // 요청 DATA 변수
    //print_r($req_data);
    
    //echo "<br><br>";
    
    // 응답 DATA 변수
    //print_r($res_data);
    
    $res = null;
    
    // $res_data 형식은 json
    if ($res_data) {
        $res = json_decode($res_data, true);
    }
    
    run_event('subscription_order_pg_pay', 'kcp', $res, $data);
    
    if (isset($res['res_cd']) && $res['res_cd'] === '0000') {
        
        // 공통형식에 맞추어야 한다.
        $results['orderId'] = $results['ordr_idxx'];
        $results['payMethod'] = $results['pay_method'];
        $results['amount'] = $results['good_mny'];
        $results['receiptUrl'] = '';    // kcp 는 영수증 url 이 없다.
        $results['cardname'] = $results['card_name'];
        $results['cardnumber'] = mask_card_number($results['card_no']);   // kcp 는 정기결제시 결제카드 번호를 다 알려주지만, 여기서는 마스킹하여 저장한다.
        $results['py_app_no'] = $results['app_no'];
        $results['tid'] = $results['tno'];
        
        return array('code'=>'success', 'message'=>$res['res_msg'], 'response'=>$res);
    } else {
        return array('code'=>'fail', 'message'=>$res['res_cd'].':'.$res['res_msg'], 'response'=>$res);
    }
    
    return array();
}

function subscription_process_payment($od, $od_pg_service='', $tmp_cart_id='') {
    
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

function subscription_pg_cardname($od_card_name, $card=array()) {
    
    if ($od_card_name && strpos($od_card_name, '카드') === false) {
        $od_card_name .= '카드';
    }
    
    return $od_card_name;
}

function check_subscription_pay_method($od_settle_case) {
    global $default;
    
    /*
    $is_block = 0;

    if ($od_settle_case === '무통장') {
        if (! $default['de_bank_use']) {
            $is_block = 1;
        }
    } else if ($od_settle_case === '계좌이체') {
        if (! $default['de_iche_use']) {
            $is_block = 1;
        }
    } else if ($od_settle_case === '가상계좌') {
        if (! $default['de_vbank_use']) {
            $is_block = 1;
        }
    } else if ($od_settle_case === '휴대폰') {
        if (! $default['de_hp_use']) {
            $is_block = 1;
        }
    } else if ($od_settle_case === '신용카드') {
        if (! $default['de_card_use']) {
            $is_block = 1;
        }
    }

    if ($is_block) {
        alert($od_settle_case.' 은 결제수단에서 사용이 금지되어 있습니다.', G5_SHOP_URL);
        die('');
    }
    */
    
}

function nicepay_reqPost(Array $data, $url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);					//connection timeout 15 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));	//POST data
	curl_setopt($ch, CURLOPT_POST, true);
	$response = curl_exec($ch);
	curl_close($ch);	 
	return $response;
}

function nicepay_billing($od, $tmp_cart_id='') {
    global $g5;
    
    include(G5_SUBSCRIPTION_PATH.'/settle_nicepay.inc.php');
    
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
    $bid 				= $od['card_billkey'];				// 빌키
    $mid 				= get_subs_option('su_nicepay_mid');		// 가맹점 아이디
    // $tid 				= substr(substr($od['od_tno'], 0, 20).substr(preg_replace('/[^0-9]/', '', G5_TIME_YMDHIS), 2), 0, 30);				// 거래 ID, 30글자 제한있음, 30글자 채워야함
    // $tid 				= generate_subscription_id($od);				// 거래 ID, 30글자 제한있음, 30글자 채워야함
    // $od['od_tno'] 인 경우 subscription/orderform.php 에서 재등록카드를 사용할 경우 A212, [TID]잘못된 데이터 형식입니다. 오류가 일어난다.
    
    // 나이스페이 옛결제모듈의 경우 tid와 moid 는 카드등록을 한 tno와 od_id로 보내야 한다.
    $before_nice_pay = sql_bind_select_fetch($g5['g5_subscription_mb_cardinfo_table'], '*', array('ci_id'=>$od['ci_id'], 'mb_id'=>$od['mb_id'], 'pg_service' => $od['od_pg'], 'od_tno' => array('!=' => '')),
        array('orderBy' => 'ci_id', 'limit' => 1, 'orderType' => 'desc'));
    $tid = $before_nice_pay['od_tno'].'12';      // 일부러 실패하려고 한다면
    
    $tid = generate_subscription_id($before_nice_pay['od_tno']);
    
    // $tid = $before_nice_pay['od_tno'];
    // 나이스페이 옛결제모듈의 경우 tid와 moid 는 카드등록을 한 tno와 od_id로 보내야 한다.
    // $moid 				= $before_nice_pay['first_ordernumber'];				// 가맹점 주문번호
    
    $moid 				= generate_subscription_id($od['od_id']);				// 가맹점 주문번호
    $amt 				= (int) $od['od_receipt_price'];				// 결제 금액
    //$goodsName 			= $goodsname['full_name'];				// 상품명
    
    $goodsName 			= iconv("UTF-8", "EUC-KR", $goodsname['full_name']);				// 상품명
    $cardInterest 		= '0';				// 무이자 여부, 가맹점 분담 무이자 할부 이벤트 사용 여부 (0: 미사용, 1: 사용(무이자))
    $cardQuota 			= '00';				// 할부 개월 수, 할부개월 (00: 일시불, 02: 2개월, 03: 3개월, ...)
    $buyerName 			= iconv("UTF-8", "EUC-KR", $od['od_name']);				// 구매자명
    $buyerTel 			= $od['od_hp'];				// 구매자 전화번호
    $buyerEmail 		= $od['od_email'];				// 구매자 이메일

    /*
    ****************************************************************************************
    * (해쉬암호화 - 수정하지 마세요)
    * SHA-256 해쉬암호화는 거래 위변조를 막기위한 방법입니다. 
    ****************************************************************************************
    */	
    $ediDate = date("YmdHis", G5_SERVER_TIME);																					// API 요청 전문 생성일시
    $merchantKey = get_subs_option('su_nicepay_key');	// 가맹점 키	
    $signData = bin2hex(hash('sha256', $mid . $ediDate . $moid . $amt . $bid . $merchantKey, true));			// 위변조 데이터 검증 값 암호화

    /*
    ****************************************************************************************
    * (API 요청부)
    * 명세서를 참고하여 필요에 따라 파라미터와 값을 'key'=>'value' 형태로 추가해주세요
    ****************************************************************************************
    */
    $data = Array(
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
    
    print_r( $data );
    
    $response = nicepay_reqPost($data, $postURL); 				//API 호출, 결과 데이터가 $response 변수에 저장됩니다.

    // $resp_utf = iconv("EUC-KR", "UTF-8", $response);
    $resp_utf = $response; 
    $nice_response = json_decode($resp_utf, true);
    
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
        
    if (function_exists('add_log')) {
        add_log($nice_response, false, 'nice');
    }
    
    run_event('subscription_order_pg_pay', 'nicepay', $nice_response, $data);
    
    // $res 형식은 json
    return array('code'=>$code, 'message'=>$message, 'response'=>$nice_response);
}

function nicepay_new_billing($od, $tmp_cart_id='') {
    global $g5;
    
    include(G5_SUBSCRIPTION_PATH.'/settle_nicepay.inc.php');
    
	$clientId = get_subs_option('su_nice_clientid');
	$secretKey = get_subs_option('su_nice_secretkey');
    
    $cart_id = $tmp_cart_id ? $tmp_cart_id : $od['od_id'];
    $goodsname = get_subscription_goods($cart_id);
    
    $res = null;
    
    $bid = $od['card_billkey'];
    
    // https://github.com/nicepayments/nicepay-manual/blob/main/api/payment-subscribe.md#%EB%B9%8C%ED%82%A4%EC%8A%B9%EC%9D%B8
    // $nice_orderId = substr($od['od_id'].'_'.md5($od['mb_id']).'_'.uniqid(), 0, 64);  // 64길이
    $nice_orderId = generate_subscription_id($id, 64);  // 64길이 가능
    $edi_date = date('c', G5_SERVER_TIME);
    $sign_data = bin2hex(hash('sha256', $nice_orderId.$bid.$edi_date.$secretKey, true));
    $buyerName = $od['od_name'];
    $buyerEmail = $od['od_email'];
    $buyerTel = $od['od_hp'];
    
    // 면세공급가액, 전체 거래금액(amount)중에서 면세에 해당하는 금액을 설정합니다.
    // $taxFreeAmt = ;
    
    $code = 'success';
    $message = '';
    $res = null;
    
    // $res 형식은 json
    
    $request_data = array("orderId" => $nice_orderId, 
                            "amount" => (int) $od['od_receipt_price'],
                            "goodsName" => $goodsname['full_name'],
                            "cardQuota" => 0,
                            "useShopInterest" => false,
                            'buyerName' => $buyerName,
                            'buyerTel' => $buyerTel,
                            'buyerEmail' => $buyerEmail
                        );
    
    if (function_exists('add_log')) {
        add_log($request_data, false, 'nice');
    }
    
	try {
		$res = requestPost(
			get_nicepay_api_url()."/v1/subscribe/" . $bid . "/payments",
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
    
    if (function_exists('add_log')) {
        add_log($nice_response, false, 'nice');
    }
    
    run_event('subscription_order_pg_pay', 'nicepay', $nice_response, $request_data);
    
    // $res 형식은 json
    return array('code'=>$code, 'message'=>$message, 'response'=>$nice_response);
}

function get_subscription_billing_key($od) {
    
    return $od['card_billkey'];
}

function subscription_sendRequest($url, $authKey, $postData) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: $authKey",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

function tosspayments_billing($od, $tmp_cart_id='') {
    global $g5;
    
    include_once(G5_SUBSCRIPTION_PATH.'/settle_tosspayments.inc.php');
    
    $apiSecretKey = get_subs_option('su_tosspayments_api_secretkey');
    
    $encryptedApiSecretKey = "Basic " . base64_encode($apiSecretKey . ":");

    $billingKey = get_subscription_billing_key($od);
    $cart_id = $tmp_cart_id ? $tmp_cart_id : $od['od_id'];
    $goodsname = get_subscription_goods($cart_id);
    
    $data = array(
        'customerKey' => $billingKey,
        'amount' => $od['od_receipt_price'],
        // 'orderId' => substr($od['od_id'].'_'.md5($od['mb_id']).'_'.uniqid(), 0, 64),  // 64길이
        'orderId' => generate_subscription_id($od['od_id'], 64),  // 64길이 가능
        'orderName' => $goodsname['full_name'],
        'customerEmail' => $od['od_email'],
        'customerName' => $od['od_name']
        );
    
    $postData = json_encode(array(
        'customerKey' => $od['od_id'],
        'amount' => $data['amount'],
        'orderId' => $data['orderId'],
        'orderName' => $data['orderName'],
        'customerEmail' => $data['customerEmail'],
        'customerName' => $data['customerName']
    ));
    
    $response = subscription_sendRequest("https://api.tosspayments.com/v1/billing/$billingKey", $encryptedApiSecretKey, $postData);
    
    $res_result = json_decode($response, true);
    
    print_r( $res_result );
    
    if (isset($res_result['code']) && $res_result['code']) {
        // 자동결제 실패했음
        
        return array('code'=>$res_result['code'], 'message'=>$res_result['message']);
        
    }
    
    // 결제 성공시
    return array('code'=>'success', 'message'=>'', 'response'=>$res_result);
}

function inicis_billing($od, $tmp_cart_id='') {
    global $g5, $inicis_iniapi_key, $inicis_iniapi_iv;
    
    include_once(G5_SUBSCRIPTION_PATH.'/settle_inicis.inc.php');
        
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
	$detail["moid"] = $od['od_id'];
	$detail["goodName"] = $goodsname['full_name'];
	$detail["buyerName"] = $od['od_name'];
	$detail["buyerEmail"] = $od['od_email'];
	$detail["buyerTel"] = $od['od_hp'];
    
    // 장바구니 금액이 변경될수 있으니, $od['od_receipt_price'] 가 아니라 장바구니 금액을 체크해서 가져와야 한다.
	$detail["price"] = $od['od_receipt_price'];
    
	$detail["billKey"] = $od['card_billkey'];
	$detail["authentification"] = "00";
	$detail["cardQuota"] = "00";
	$detail["quotaInterest"] = "0";
    
    $postdata["data"] = $detail;
    
	$details = str_replace('\\/', '/', json_encode($detail, JSON_UNESCAPED_UNICODE));

	//// Hash Encryption
	$plainTxt = $key.$mid.$type.$timestamp.$details;
    $hashData = hash("sha512", $plainTxt);

	$postdata["hashData"] = $hashData;
    
    $is_print = false;
    
    if ($is_print) {
        echo "plainTxt : ".$plainTxt."<br/><br/>";
        echo "hashData : ".$hashData."<br/><br/>"; 
    }

	$post_data = json_encode($postdata, JSON_UNESCAPED_UNICODE);
	
    if ($is_print) {
        echo "**** 요청전문 **** <br/>" ; 
        echo str_replace(',', ',<br>', $post_data)."<br/><br/>" ; 
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
        echo "**** 응답전문 **** <br/>" ;
        echo str_replace(',', ',<br>', $response)."<br><br>";
    }
    
    // 성공이면 pay 테이블에 insert 한다. $response 형식은 json
    
    $inicis_res = json_decode($response, true);
    
    run_event('subscription_order_pg_pay', 'inicis', $inicis_res, $postdata);
    
    if (isset($inicis_res['resultCode']) && $inicis_res['resultCode'] === '00') {
        
        return array('code'=>'success', 'message'=>$inicis_res['resultMsg'], 'response'=>$inicis_res);
        
    } else {
        
        // 실패시
        return array('code'=>'fail', 'message'=>$inicis_res['resultCode'].':'.$inicis_res['resultMsg'], 'response'=>$inicis_res);
    }
}

function is_null_date($datetime){
    
    if (! $datetime || $datetime == null || strpos($datetime, '0000-00-00') !== false) {
        return true;
    }
    
    return false;
}

// 한글 요일
function get_hangul_date_format($str)
{
    $formats = array ('day' => '일', 'week' => '주', 'month' => '월', 'year' => '년');

    return isset($formats[$str]) ? $formats[$str] : '';
}

function mask_card_number($string) {
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

function get_subscription_uniqid($is_pay=0, $uniqid_key='', $length=0) {
    global $g5;

    sql_query(" LOCK TABLE {$g5['g5_subscription_uniqid_table']} WRITE ");
    $i = 0;
    while (1) {
        // 년월일시분초에 100분의 1초 두자리를 추가함 (1/100 초 앞에 자리가 모자르면 0으로 채움)
        
        if ($is_pay) {
            $key = $uniqid_key;
            
            if ($i > 0) {
                $pad_str = str_pad((int)((float)microtime()*100), 2, "0", STR_PAD_LEFT);
                $key = ($length && strlen($key) >= $length) ? substr($key, 0, -2).$pad_str : $key.$pad_str;
            }
        } else {
            $key = date('YmdHis', time()) . str_pad((int)((float)microtime()*100), 2, "0", STR_PAD_LEFT);
        }

        // $result = sql_query(" insert into {$g5['g5_subscription_uniqid_table']} set suq_key = '$key', suq_ip = '{$_SERVER['REMOTE_ADDR']}' ", false);
        
        $result = sql_bind_insert($g5['g5_subscription_uniqid_table'], array('suq_key' => $key, 'suq_ip' => $_SERVER['REMOTE_ADDR']));
        
        if ($result) break; // 쿼리가 정상이면 빠진다.

        // insert 하지 못했으면 일정시간 쉰다음 다시 유일키를 만든다.
        usleep(10000); // 100분의 1초를 쉰다
        $i++;
    }
    sql_query(" UNLOCK TABLES ");

    return $key;
}

function generate_subscription_id($oid='', $length=30) {
    global $g5, $is_member, $member;
    
    /*
    if (is_array($od)) {
        $oid = isset($od['od_tno']) ? $od['od_tno'] : $od['od_id'];
        
        if ($od['od_pg'] === 'nicepay') {
            $before_nice_pay = sql_bind_select_fetch($g5['g5_subscription_mb_cardinfo_table'], '*', array('ci_id'=>$od['ci_id'], 'mb_id'=>$od['mb_id'], 'pg_service' => $od['od_pg']));
            
            $oid = $before_nice_pay['od_tno'];
        }
    } else {
        $oid = $od;
    }
    */
    
    // 데이터베이스에서 가장 최근 주문 ID 가져오기
    $stmt = sql_bind_select_fetch($g5['g5_subscription_pay_table'], 'MAX(id)');
    
    $lastId = $stmt['id'];
    $lastId = $lastId ? $lastId + 1 : 1;
    
    $str = substr(hash('sha256', $lastId . $member['md_id'] . microtime()), 0, 12);
    
    if (strlen($oid) >= $length) {
        $subscription_key = substr($oid, 0, -12).$str;
    } else {
        $subscription_key = $oid ? $oid.$str : $str;
    }
    
    return get_subscription_uniqid(1, substr($subscription_key, 0, $length), $length);
}

function get_subscription_pg_id($pg_name=''){
    
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

function get_subscription_pg_apikey($pg_name=''){
    
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
function get_subscription_boxcart_datas($is_cache=false)
{
    global $g5, $is_member, $member;
    
    $sql  = " select * from {$g5['g5_subscription_cart_table']} ";
    $sql .= " where od_id = '".$cart_id."' group by it_id ";
    
    if (!$is_member){
        return array();
    }

    static $cache = array();

    if ($is_cache && !empty($cache)) {
        return $cache;
    }
    
    // 정기구독은 회원만 결제가 가능하다.
    $cart_id = get_session("subs_cart_id");
    
    if (!$cart_id) {
        $wheres = array('mb_id' => $member['mb_id'], 'ct_status' => '쇼핑');
    } else {
        $wheres = array('od_id' => $cart_id, 'ct_status' => '쇼핑');
    }
    
    $carts = sql_bind_select_array($g5['g5_subscription_cart_table'], '*', $wheres, array('groupBy'=> 'it_id'));
    
    /*
    $sql  = " select * from {$g5['g5_subscription_cart_table']} ";
    $sql .= " where od_id = '".$cart_id."' group by it_id ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $key = $row['it_id'];
        $cache[$key] = $row;
    }
    */
    
    foreach($carts as $row) {
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

function get_subscription_user_carts($s_cart_id) {
    
    global $g5, $member;
    
    static $cache = array();

    $key = 'subs_'.$member['mb_id'].'_'.$s_cart_id;

    if ($is_cache && isset($cache[$key])) {
        return $cache[$key];
    }
    
    $select_filed = 'a.ct_id,
                    a.it_id,
                    a.it_name,
                    a.ct_price,
                    a.ct_point,
                    a.ct_qty,
                    a.ct_status,
                    a.ct_send_cost,
                    a.it_sc_type,
                    b.ca_id,
                    b.ca_id2,
                    b.ca_id3';
                    
    $cart_datas = sql_bind_select_array("{$g5['g5_subscription_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )", $select_filed, array('a.od_id' => $s_cart_id), array('groupBy' => 'a.it_id', 'orderBy' => 'a.ct_id'));
    
    $cache[$key] = $cart_datas;
    
    return $cart_datas;
}

function add_subscription_order_history($content, $arg=array()){
    global $g5;
    
    $inserts = array(
        'hs_parent' => isset($arg['hs_parent']) ? (int) $arg['hs_parent'] : 0,
        'hs_type' => isset($arg['type']) ? $arg['type'] : '',
        'hs_category' => isset($arg['hs_category']) ? $arg['hs_category'] : '',
        'od_id' => isset($arg['od_id']) ? $arg['od_id'] : '',
        'mb_id' => isset($arg['mb_id']) ? $arg['mb_id'] : '',
        'hs_content' => $content,
        'hs_time' => G5_TIME_YMDHIS,
        );
    
    // https://stackoverflow.com/questions/10054633/insert-array-into-mysql-database-with-php
    $columns = implode(', ', array_keys($inserts));
    $values = implode("', '", array_values($inserts));

    // 주문서에 입력
    $sql = "INSERT INTO `{$g5['g5_subscription_order_history_table']}`($columns) VALUES ('$values')";
    
    $result = sql_query($sql, false);
    
    return sql_insert_id();
}

function get_subscription_pay_full_goods($pay_id, $is_cache=false) {
    global $g5;
    
    static $cache = array();

    $key = md5($pay_id);

    if( $is_cache && isset($cache[$key]) ){
        return $cache[$key];
    }
    
    $goods = array(
        'full_name' => '',
        'thumb' => '',
        );
    
    // 상품명만들기
    /*
    $sql = " select a.it_id, b.it_name from {$g5['g5_subscription_cart_table']} a, {$g5['g5_shop_item_table']} b where a.it_id = b.it_id and a.od_id = '$pay_id' order by ct_id ";
        
    $result = sql_query($sql);
    */
    
    $result = sql_bind_select("{$g5['g5_subscription_cart_table']} as a join {$g5['g5_shop_item_table']} as b ON a.it_id = b.it_id", "a.it_id, b.it_name", array('a.od_id' => $pay_id), array('orderBy' => 'ct_id'));
    
    $tmp = array();
    
    for($i=0; $row=sql_fetch_array($result); $i++) {
        
        $row['thumbnail'] = get_subscription_it_image($row['it_id'], 65, 65, true);
        
        // 대표 상품명과 대표 썸네일을 지정한다.
        if ($i === 0) {
            $goods['full_name'] = preg_replace ("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", "", addslashes($row['it_name']));
            $goods['thumb'] = $row['thumbnail'];
        }
        
        $tmp[$i] = $row;
    }
    
    $goods['data'] = $tmp;
    $total_tmp = count($tmp);
    
    if ($tmp && $total_tmp > 1) {
        $goods['full_name'] .= ' 외 '.((int)$total_tmp - 1).'건';
    }
    
    $cache[$key] = $goods;
    
    return $cache[$key];
}

function get_Ko_DayOfWeek($day, $is_print_yoil=''){

    // 입력된 날짜를 strtotime으로 변환하여 유효성 검사
    $timestamp = strtotime($day);
    if (!$timestamp) return '';
    
    $yoil = array("일","월","화","수","목","금","토");

    return ($yoil[date('w', $timestamp)]).$is_print_yoil;

}

function print_subscription_pg_name($od, $pg_name='') {
    $txt = '';
    
    if (isset($od['od_pg'])) {
        $txt = get_text($od['od_pg']);
    }
    
    return $txt;
}

function print_subscription_card_info($od) {
    $txt = '';
    
    if (isset($od['card_mask_number'])) {
        $txt = get_text($od['od_card_name']).' '.get_text($od['card_mask_number']);
    }
    
    return $txt;
}

function subscription_item_delivery_title($it) {
    $title = get_subs_option('su_user_delivery_title');
    
    return $title ? get_text($title) : '주기일입력';
}

function subscription_user_delivery_option($index) {
    
    $text = (int) get_subs_option('su_user_delivery_default_day') * (int) $index;
    
    return $index.'주기마다 ('.$text.'일마다)';
}

function isValidBase64($input) {
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