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

    $sql = " select * from {$g5['g5_subscription_category_table']}
                where sc_use = '1' ";
    if($sc_id)
        $sql .= " and sc_id like '$sc_id%' ";
    $sql .= " and length(sc_id) = '$len' order by sc_order, sc_id ";

    return $sql;
}

function get_weekend_yoil($date) {
    
    $yoil = array("일","월","화","수","목","금","토");
    
    return $yoil[date('w', strtotime($date))];
}

// 상품명과 건수를 반환
function get_subscription_goods($cart_id) {
    global $g5;

    // 상품명만들기
    $row = sql_fetch(" select a.it_id, b.it_name from {$g5['g5_subscription_cart_table']} a, {$g5['g5_subscription_item_table']} b where a.it_id = b.it_id and a.od_id = '$cart_id' order by ct_id limit 1 ");
    
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
    $py_cardname = $pg_data['card']['cardName'];
    $py_cardnumber = $pg_data['card']['cardNum'];
    $py_app_no = $pg_data['py_app_no'];
        
    // 나이스페이인 경우 메뉴얼 : https://github.com/nicepayments/nicepay-manual/blob/main/api/payment-subscribe.md#%EB%B9%8C%ED%82%A4%EC%8A%B9%EC%9D%B8
    // issuedCashReceipt 현금영수증 발급여부 true:발행 / false:미발행
    // useEscrow 에스크로 거래 여부 false:일반거래 / true:에스크로 거래
    // approveNo 제휴사 승인 번호 신용카드, 계좌이체, 휴대폰
    // balanceAmt 취소 가능 잔액, 부분취소 거래인경우, 전체금액에서 현재까지 취소된 금액을 차감한 금액.
    
    $inserts = array(
        'od_id' => $od['od_id'],
        'mb_id' => $od['mb_id'],
        'subscription_id' => $subscription_id,
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
        'py_round_no' => $pay_round_no
        );
     
     
     print_r2($inserts);
     
    $columns = implode(', ', array_keys($inserts));
    $values = implode("', '", array_values($inserts));

    // 주문서에 입력
    $sql = "INSERT INTO `{$g5['g5_subscription_pay_table']}`($columns) VALUES ('$values')";
    
    echo $sql;
    
    sql_query($sql);
    
    return sql_insert_id();
}

function calculateNextBillingDate($od){
    
    // 현재 날짜를 DateTime 객체로 변환
    if (is_null_date($od['next_billing_date'])) {
        $timestamp = G5_SERVER_TIME;
    } else {
        $timestamp = strtotime($od['next_billing_date']);
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

function kcp_billing($od, $tmp_cart_id='') {
    global $g5;
    
    include(G5_SUBSCRIPTION_PATH.'/settle_kcp.inc.php');
    
    $site_cd            = get_subs_option('su_kcp_mid'); // 사이트 코드
    // 인증서 정보(직렬화)
    $kcp_cert_info      = get_subs_option('su_kcp_cert_info');
    
    $cart_id = $tmp_cart_id ? $tmp_cart_id : $od['od_id'];
    $goodsname = get_subscription_goods($cart_id);
    
    $cust_ip            = "";
    $currency           = '410'; // 화폐 단위
    $ordr_idxx          = $od['od_id'].'_'.$od['mb_id'].'_'.uniqid(); // 주문번호 
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
    
    if (isset($res['res_cd']) && $res['res_cd'] = '0000') {
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
    } else if ($subscription_pg_service === 'nicepay') {
        return nicepay_billing($od, $tmp_cart_id);
    }
    
    return null;
}

function nicepay_billing($od, $tmp_cart_id='') {
    global $g5;
    
    include(G5_SUBSCRIPTION_PATH.'/settle_nicepay.inc.php');
    
	$clientId = get_subs_option('su_nice_clientid');
	$secretKey = get_subs_option('su_nice_secretkey');
    
    $cart_id = $tmp_cart_id ? $tmp_cart_id : $od['od_id'];
    $goodsname = get_subscription_goods($cart_id);
    
    $res = null;
    
    $bid = $od['card_billkey'];
    
    // https://github.com/nicepayments/nicepay-manual/blob/main/api/payment-subscribe.md#%EB%B9%8C%ED%82%A4%EC%8A%B9%EC%9D%B8
    $nice_orderId = substr($od['od_id'].'_'.get_string_encrypt($od['mb_id']).'_'.uniqid(), 0, 64);  // 64길이
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

function inicis_billing($od, $tmp_cart_id='') {
    global $g5;
    
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

function mask_card_number($string) {
    // 문자열 길이 확인
    $length = strlen($string);
    
    // 시작과 끝에 남길 자리 수 설정
    $start = 6;
    $end = 1;
    
    // 마스킹할 부분의 길이 계산
    $maskLength = $length - ($start + $end);
    
    // 문자열을 마스킹된 형태로 변환
    return substr($string, 0, $start) . str_repeat('*', $maskLength) . substr($string, -$end);
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

// 금액표시
// $it : 상품 배열
function get_subscription_price($it)
{
    global $member;

    $price = $it['it_price'];

    return run_replace('get_subscription_price', (int)$price, $it);
}