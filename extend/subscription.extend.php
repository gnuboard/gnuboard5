<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//------------------------------------------------------------------------------
// 정기결제 상수 모음 시작
//------------------------------------------------------------------------------

define('G5_USE_SUBSCRIPTION', true);

define('G5_SUBSCRIPTION_DIR',             'subscription');
define('G5_SUBSCRIPTION_PATH',            G5_PATH.'/'.G5_SUBSCRIPTION_DIR);
define('G5_SUBSCRIPTION_URL',             G5_URL.'/'.G5_SUBSCRIPTION_DIR);
define('G5_MSUBSCRIPTION_PATH', G5_MOBILE_PATH.'/'.G5_SUBSCRIPTION_DIR);
define('G5_MSUBSCRIPTION_URL',  G5_MOBILE_URL.'/'.G5_SUBSCRIPTION_DIR);


define('G5_SUBSCRIPTION_ADMIN_DIR',        'subscription_admin');
define('G5_SUBSCRIPTION_ADMIN_PATH',       G5_ADMIN_PATH.'/'.G5_SUBSCRIPTION_ADMIN_DIR);
define('G5_SUBSCRIPTION_ADMIN_URL',        G5_ADMIN_URL.'/'.G5_SUBSCRIPTION_ADMIN_DIR);

// 정기결제 테이블명
$g5['subscription_prefix']                = G5_TABLE_PREFIX.'subscription_';
$g5['g5_subscription_config_table'] = $g5['subscription_prefix'] .'config';     // 정기결제 설정 테이블
$g5['g5_subscription_cart_table']          = $g5['subscription_prefix'] . 'cart';
$g5['g5_subscription_category_table']           = $g5['subscription_prefix'] . 'category';
$g5['g5_subscription_item_table']         = $g5['subscription_prefix'] . 'item';
$g5['g5_subscription_order_table']            = $g5['subscription_prefix'] . 'order';
$g5['g5_subscription_order_data_table']   = $g5['subscription_prefix'] . 'order_data'; // 결제정보 임시저장 테이블
$g5['g5_subscription_item_qa_table']    = $g5['subscription_prefix'] . 'item_qa'; // 상품 질문답변 테이블

$subscriptions_default = array(
'su_card_test' => 1,
'su_inicis_mid' => '',
'su_inicis_sign_key' => '',
'de_subscription_skin' => 'basic',
'de_subscription_mobile_skin' => 'basic',
'su_pg_service' => 'kcp',    // kcp, inicis
// 'su_pg_service' => 'inicis',    // kcp, inicis
'su_hope_date_use' => 0,
'su_hope_date_after' => 0,
'su_card_use' => 1,     // 세금계산서
'su_bank_use' => 0,     // 무통장
'su_kcp_mid' => '',     // kcp 정기결제 사이트키
'su_kcp_group_id' => '', // kcp kcp_group_id
'su_nice_clientid' => '',   // 나이스페이 clientId
'su_nicepay_secretkey' => '',   // 나이스페이 secretKey
);

$config['g5_subscriptions_options'] = array_merge($subscriptions_default, (array) sql_fetch("select * from `{$g5['g5_subscription_config_table']}` limit 1", false));

function get_subs_option($key) {
    global $config;
    
    if (isset($config['g5_subscriptions_options'][$key])) {
        return $config['g5_subscriptions_options'][$key];
    }
    
    return null;
}

function set_subs_option($key, $value) {
    global $config;
    
    if (isset($config['g5_subscriptions_options'][$key])) {
        $config['g5_subscriptions_options'][$key] = $value;
    }
}

// 보안서버주소 설정
if (G5_HTTPS_DOMAIN) {
    define('G5_HTTPS_SUBSCRIPTION_URL', G5_HTTPS_DOMAIN.'/'.G5_SUBSCRIPTION_DIR);
    define('G5_HTTPS_MSUBSCRIPTION_URL', G5_HTTPS_DOMAIN.'/'.G5_MOBILE_DIR.'/'.G5_SUBSCRIPTION_DIR);
} else {
    define('G5_HTTPS_SUBSCRIPTION_URL', G5_SUBSCRIPTION_URL);
    define('G5_HTTPS_MSUBSCRIPTION_URL', G5_MSUBSCRIPTION_URL);
}

if(!defined('_THEME_PREVIEW_')) {
    // 테마 경로 설정
    if(defined('G5_THEME_PATH')) {
        define('G5_THEME_SUBSCRIPTION_PATH',   G5_THEME_PATH.'/'.G5_SUBSCRIPTION_DIR);
        define('G5_THEME_SUBSCRIPTION_URL',    G5_THEME_URL.'/'.G5_SUBSCRIPTION_DIR);
        define('G5_THEME_MSUBSCRIPTION_PATH',  G5_THEME_PATH.'/'.G5_MOBILE_DIR.'/'.G5_SUBSCRIPTION_DIR);
        define('G5_THEME_MSUBSCRIPTION_URL',   G5_THEME_URL.'/'.G5_MOBILE_DIR.'/'.G5_SUBSCRIPTION_DIR);
    }

    // 스킨 경로 설정
    if(preg_match('#^theme/(.+)$#', get_subs_option('de_subscription_skin'), $match)) {
		if(defined('G5_THEME_PATH')) {
			define('G5_SUBSCRIPTION_SKIN_PATH',  G5_THEME_PATH.'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$match[1]);
			define('G5_SUBSCRIPTION_SKIN_URL',   G5_THEME_URL .'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$match[1]);
		} else {
			define('G5_SUBSCRIPTION_SKIN_PATH',  G5_PATH.'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$match[1]);
			define('G5_SUBSCRIPTION_SKIN_URL',   G5_URL .'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$match[1]);
		}
    } else {
        define('G5_SUBSCRIPTION_SKIN_PATH',  G5_PATH.'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.get_subs_option('de_subscription_skin'));
        define('G5_SUBSCRIPTION_SKIN_URL',   G5_URL .'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.get_subs_option('de_subscription_skin'));
    }

    if(preg_match('#^theme/(.+)$#', get_subs_option('de_subscription_mobile_skin'), $match)) {
		if(defined('G5_THEME_PATH')) {
			define('G5_MSUBSCRIPTION_SKIN_PATH', G5_THEME_MOBILE_PATH.'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$match[1]);
			define('G5_MSUBSCRIPTION_SKIN_URL',  G5_THEME_URL .'/'.G5_MOBILE_DIR.'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$match[1]);
		} else {
			define('G5_MSUBSCRIPTION_SKIN_PATH', G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$match[1]);
			define('G5_MSUBSCRIPTION_SKIN_URL',  G5_MOBILE_URL .'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$match[1]);
		}
    } else {
        define('G5_MSUBSCRIPTION_SKIN_PATH', G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.get_subs_option('de_subscription_mobile_skin'));
        define('G5_MSUBSCRIPTION_SKIN_URL',  G5_MOBILE_URL .'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.get_subs_option('de_subscription_mobile_skin'));
    }
}

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

// 장바구니 금액 체크 $is_price_update 가 true 이면 장바구니 가격 업데이트한다. 
function before_check_subscription_cart_price($s_cart_id, $is_ct_select_condition=false, $is_price_update=false, $is_item_cache=false){
    global $g5, $default, $config;

    if( !$s_cart_id ){
        return;
    }

    $select_where_add = '';

    if( $is_ct_select_condition ){
        $select_where_add = " and ct_select = '0' ";
    }

    $sql = " select * from `{$g5['g5_subscription_cart_table']}` where od_id = '$s_cart_id' {$select_where_add} ";

    $result = sql_query($sql);
    $check_need_update = false;
    
    for ($i=0; $row=sql_fetch_array($result); $i++){
        if( ! $row['it_id'] ) continue;

        $it_id = $row['it_id'];
        $it = get_subscription_item($it_id, $is_item_cache);
        
        $update_querys = array();

        if(!$it['it_id'])
            continue;
        
        if( $it['it_price'] !== $row['ct_price'] ){
            // 장바구니 테이블 상품 가격과 상품 테이블의 상품 가격이 다를경우
            $update_querys['ct_price'] = $it['it_price'];
        }

        if( $row['io_id'] ){
            $io_sql = " select * from {$g5['g5_subscription_item_option_table']} where it_id = '{$it['it_id']}' and io_id = '{$row['io_id']}' ";
            $io_infos = sql_fetch( $io_sql );

            if( $io_infos['io_type'] ){
                $this_io_type = $io_infos['io_type'];
            }
            if( $io_infos['io_id'] && $io_infos['io_price'] !== $row['io_price'] ){
                // 장바구니 테이블 옵션 가격과 상품 옵션테이블의 옵션 가격이 다를경우
                $update_querys['io_price'] = $io_infos['io_price'];
            }
        }

        // 포인트
        $compare_point = 0;
        if($config['cf_use_point']) {

            // DB 에 io_type 이 1이면 상품추가옵션이며, 0이면 상품선택옵션이다
            if($row['io_type'] == 0) {
                $compare_point = get_item_point($it, $row['io_id']);
            } else {
                $compare_point = $it['it_supply_point'];
            }

            if($compare_point < 0)
                $compare_point = 0;
        }
        
        if((int) $row['ct_point'] !== (int) $compare_point){
            // 장바구니 테이블 적립 포인트와 상품 테이블의 적립 포인트가 다를경우
            $update_querys['ct_point'] = $compare_point;
        }

        if( $update_querys ){
            $check_need_update = true;
        }

        // 장바구니에 담긴 금액과 실제 상품 금액에 차이가 있고, $is_price_update 가 true 인 경우 장바구니 금액을 업데이트 합니다. 
        if( $is_price_update && $update_querys ){
            $conditions = array();

            foreach ($update_querys as $column => $value) {
                $conditions[] = "`{$column}` = '{$value}'";
            }

            if( $col_querys = implode(',', $conditions) ) {
                $sql_query = "update `{$g5['g5_subscription_cart_table']}` set {$col_querys} where it_id = '{$it['it_id']}' and od_id = '$s_cart_id' and ct_id =  '{$row['ct_id']}' ";
                sql_query($sql_query, false);
            }
        }
    }

    // 장바구니에 담긴 금액과 실제 상품 금액에 차이가 있다면
    if( $check_need_update ){
        return false;
    }

    return true;
}

// 정기결제 상품의 재고 (창고재고수량 - 주문대기수량)
function get_subscription_it_stock_qty($it_id)
{
    global $g5;

    $sql = " select it_stock_qty from {$g5['g5_subscription_item_table']} where it_id = '$it_id' ";
    
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
    global $g5;

    $sql = " select io_stock_qty
                from {$g5['g5_subscription_item_option_table']}
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
    return false;
}

function subscription_member_cert_check($id, $type){
    global $g5, $member;

    $msg = '';
    
    return $msg;
}

function get_subscription_category($sc_id) {
    global $g5, $g5_object;
    
    $add_query = '';
    
    $sql = " select * from {$g5['g5_subscription_category_table']} where sc_id = '{$sc_id}' $add_query ";
    return sql_fetch($sql);
}

function get_subscription_order($od_id) {
    global $g5;
    
    $sql = " select * from {$g5['g5_subscription_order_table']} where od_id = '{$od_id}' ";
    return sql_fetch($sql);
}

function get_subscription_item($it_id, $is_cache=false, $add_query='') {
    global $g5, $g5_object;

    $add_query_key = $add_query ? 'subscription_'.md5($add_query) : '';

    $item = $is_cache ? $g5_object->get('subscription', $it_id, $add_query_key) : null;

    if( !$item ){
        $sql = " select * from {$g5['g5_subscription_item_table']} where it_id = '{$it_id}' $add_query ";
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
        $sql = " select a.*, b.sc_name, b.sc_use from {$g5['g5_subscription_item_table']} a, {$g5['g5_subscription_category_table']} b where a.it_seo_title = '".sql_real_escape_string(generate_seo_title($seo_title))."' and a.sc_id = b.sc_id $add_query";
    } else {
        $sql = " select a.*, b.sc_name, b.sc_use from {$g5['g5_subscription_item_table']} a, {$g5['g5_subscription_category_table']} b where a.it_id = '$it_id' and a.sc_id = b.sc_id $add_query";
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
        $it_seo_title = exist_seo_title_recursive('subscription', generate_seo_title($item['it_name']), $g5['g5_subscription_item_table'], $item['it_id']);

        if( isset($item['it_seo_title']) && $it_seo_title !== $item['it_seo_title'] ){
            $sql = " update `{$g5['g5_subscription_item_table']}` set it_seo_title = '{$it_seo_title}' where it_id = '{$item['it_id']}' ";
            sql_query($sql);
        }
    }
}

class SubscriptionList
{
    // 상품유형 : 기본적으로 1~5 까지 사용할수 있으며 0 으로 설정하는 경우 상품유형별로 노출하지 않습니다.
    // 분류나 이벤트로 노출하는 경우 상품유형을 0 으로 설정하면 됩니다.
    protected $type;

    protected $list_skin;
    protected $list_mod;
    protected $list_row;
    protected $img_width;
    protected $img_height;

    // 상품상세보기 경로
    protected $href = "";

    // select 에 사용되는 필드
    protected $fields = "*";

    // 분류코드로만 사용하는 경우 상품유형($type)을 0 으로 설정하면 됩니다.
    protected $sc_id = "";
    protected $sc_id2 = "";
    protected $sc_id3 = "";

    // 노출순서
    protected $order_by = "it_order, it_id desc";

    // 스킨의 기본 css 를 다른것으로 사용하고자 할 경우에 사용합니다.
    protected $css = "";

    // 상품의 사용여부를 따져 노출합니다. 0 인 경우 모든 상품을 노출합니다.
    protected $use = 1;

    // 모바일에서 노출하고자 할 경우에 true 로 설정합니다.
    protected $is_mobile = false;

    // 기본으로 보여지는 필드들
    protected $view_it_id    = false;       // 상품코드
    protected $view_it_img   = true;        // 상품이미지
    protected $view_it_name  = true;        // 상품명
    protected $view_it_basic = true;        // 기본설명
    protected $view_it_price = true;        // 판매가격
    protected $view_it_cust_price = false;  // 소비자가
    protected $view_it_icon = false;        // 아이콘
    protected $view_sns = false;            // SNS
    protected $view_star = false;           // 별점

    // 몇번째 class 호출인지를 저장합니다.
    protected $count = 0;

    // true 인 경우 페이지를 구한다.
    protected $is_page = false;

    // 페이지 표시를 위하여 총 상품수를 구합니다.
    public $total_count = 0;

    // sql limit 의 시작 레코드
    protected $from_record = 0;

    // 외부에서 쿼리문을 넘겨줄 경우에 담아두는 변수
    protected $query = "";

    // $type        : 상품유형 (기본으로 1~5까지 사용)
    // $list_skin   : 상품리스트를 노출할 스킨을 설정합니다. 스킨위치는 skin/shop/쇼핑몰설정스킨/type??.skin.php
    // $list_mod    : 1줄에 몇개의 상품을 노출할지를 설정합니다.
    // $list_row    : 상품을 몇줄에 노출할지를 설정합니다.
    // $img_width   : 상품이미지의 폭을 설정합니다.
    // $img_height  : 상품이미지의 높이을 설정합니다. 0 으로 설정하는 경우 썸네일 이미지의 높이는 폭에 비례하여 생성합니다.
    //function __construct($type=0, $list_skin='', $list_mod='', $list_row='', $img_width='', $img_height=0, $sc_id='') {
    function __construct($list_skin='', $list_mod='', $list_row='', $img_width='', $img_height=0) {
        $this->list_skin  = $list_skin;
        $this->list_mod   = $list_mod;
        $this->list_row   = $list_row;
        $this->img_width  = $img_width;
        $this->img_height = $img_height;
        $this->set_href(G5_SUBSCRIPTION_URL.'/item.php?it_id=');
        $this->count++;
    }

    function set_type($type) {
        $this->type = $type;
        if ($type) {
            $this->set_list_skin($this->list_skin);
            $this->set_list_mod($this->list_mod);
            $this->set_list_row($this->list_row);
            $this->set_img_size($this->img_width, $this->img_height);
        }
    }

    // 분류코드로 검색을 하고자 하는 경우 아래와 같이 인수를 넘겨줍니다.
    // 1단계 분류는 (분류코드, 1)
    // 2단계 분류는 (분류코드, 2)
    // 3단계 분류는 (분류코드, 3)
    function set_category($sc_id, $level=1) {
        if ($level == 2) {
            $this->sc_id2 = $sc_id;
        } else if ($level == 3) {
            $this->sc_id3 = $sc_id;
        } else {
            $this->sc_id = $sc_id;
        }
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

    // 1줄에 몇개를 노출할지를 사용한다.
    // 특별히 설정하지 않는 경우 상품유형을 사용하는 경우는 쇼핑몰설정 값을 그대로 따릅니다.
    function set_list_mod($list_mod) {
        global $default;
        if ($this->is_mobile) {
            $this->list_mod = $list_mod ? $list_mod : $default['de_mobile_type'.$this->type.'_list_mod'];
        } else {
            $this->list_mod = $list_mod ? $list_mod : $default['de_type'.$this->type.'_list_mod'];
        }
    }

    // 몇줄을 노출할지를 사용한다.
    // 특별히 설정하지 않는 경우 상품유형을 사용하는 경우는 쇼핑몰설정 값을 그대로 따릅니다.
    function set_list_row($list_row) {
        global $default;
        if ($this->is_mobile) {
            $this->list_row = $list_row ? $list_row : $default['de_mobile_type'.$this->type.'_list_row'];
        } else {
            $this->list_row = $list_row ? $list_row : $default['de_type'.$this->type.'_list_row'];
        }
        if (!$this->list_row)
            $this->list_row = 1;
    }

    // 노출이미지(썸네일생성)의 폭, 높이를 설정합니다. 높이를 0 으로 설정하는 경우 쎰네일 비율에 따릅니다.
    // 특별히 설정하지 않는 경우 상품유형을 사용하는 경우는 쇼핑몰설정 값을 그대로 따릅니다.
    function set_img_size($img_width, $img_height=0) {
        global $default;
        if ($this->is_mobile) {
            $this->img_width = $img_width ? $img_width : $default['de_mobile_type'.$this->type.'_img_width'];
            $this->img_height = $img_height ? $img_height : $default['de_mobile_type'.$this->type.'_img_height'];
        } else {
            $this->img_width = $img_width ? $img_width : $default['de_type'.$this->type.'_img_width'];
            $this->img_height = $img_height ? $img_height : $default['de_type'.$this->type.'_img_height'];
        }
    }

    // 특정 필드만 select 하는 경우에는 필드명을 , 로 구분하여 "field1, field2, field3, ... fieldn" 으로 인수를 넘겨줍니다.
    function set_fields($str) {
        $this->fields = $str;
    }

    // 특정 필드로 정렬을 하는 경우 필드와 정렬순서를 , 로 구분하여 "field1 desc, field2 asc, ... fieldn desc " 으로 인수를 넘겨줍니다.
    function set_order_by($str) {
        $this->order_by = $str;
    }

    // 사용하는 상품외에 모든 상품을 노출하려면 0 을 인수로 넘겨줍니다.
    function set_use($use) {
        $this->use = $use;
    }

    // 모바일로 사용하려는 경우 true 를 인수로 넘겨줍니다.
    function set_mobile($mobile=true) {
        $this->is_mobile = $mobile;
    }

    // 스킨에서 특정 필드를 노출하거나 하지 않게 할수 있습니다.
    // 가령 소비자가는 처음에 노출되지 않도록 설정되어 있지만 노출을 하려면
    // ("it_cust_price", true) 와 같이 인수를 넘겨줍니다.
    // 이때 인수로 넘겨주는 값은 스킨에 정의된 필드만 가능하다는 것입니다.
    function set_view($field, $view=true) {
        $this->{"view_".$field} = $view;
    }

    // anchor 태그에 하이퍼링크를 다른 주소로 걸거나 아예 링크를 걸지 않을 수 있습니다.
    // 인수를 "" 공백으로 넘기면 링크를 걸지 않습니다.
    function set_href($href) {
        $this->href = $href;
    }

    // ul 태그의 css 를 교체할수 있다. "sct sct_abc" 를 인수로 넘기게 되면
    // 기존의 ul 태그에 걸린 css 는 무시되며 인수로 넘긴 css 가 사용됩니다.
    function set_css($css) {
        $this->css = $css;
    }

    // 페이지를 노출하기 위해 true 로 설정할때 사용합니다.
    function set_is_page($is_page) {
        $this->is_page = $is_page;
    }

    // select ... limit 의 시작값
    function set_from_record($from_record) {
        $this->from_record = $from_record;
    }

    // 외부에서 쿼리문을 넘겨줄 경우에 담아둡니다.
    function set_query($query) {
        $this->query = $query;
    }

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

            if ($this->sc_id || $this->sc_id2 || $this->sc_id3) {
                $where_sc_id = array();
                if ($this->sc_id) {
                    $where_sc_id[] = " sc_id like '{$this->sc_id}%' ";
                }
                if ($this->sc_id2) {
                    $where_sc_id[] = " sc_id2 like '{$this->sc_id2}%' ";
                }
                if ($this->sc_id3) {
                    $where_sc_id[] = " sc_id3 like '{$this->sc_id3}%' ";
                }
                $where[] = " ( " . implode(" or ", $where_sc_id) . " ) ";
            }

            if ($this->order_by) {
                $sql_order = " order by {$this->order_by} ";
            }

		$sql_select = " select {$this->fields} ";
		$sql_common = " from `{$g5['g5_subscription_item_table']}` ";

            $sql_where = " where " . implode(" and ", $where);
            $sql_limit = " limit " . $this->from_record . " , " . ($this->list_mod * $this->list_row);

            $sql = $sql_select . $sql_common . $sql_where . $sql_order . $sql_limit;
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
                    subscription_seo_title_update($row['it_id']);
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