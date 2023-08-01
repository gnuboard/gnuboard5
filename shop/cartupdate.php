<?php
include_once('./_common.php');

// print_r2($_POST); exit;

// 보관기간이 지난 상품 삭제
cart_item_clean();

$sw_direct = (isset($_REQUEST['sw_direct']) && $_REQUEST['sw_direct']) ? 1 : 0;

// cart id 설정
set_cart_id($sw_direct);

if($sw_direct)
    $tmp_cart_id = get_session('ss_cart_direct');
else
    $tmp_cart_id = get_session('ss_cart_id');

// 브라우저에서 쿠키를 허용하지 않은 경우라고 볼 수 있음.
if (!$tmp_cart_id)
{
    alert('더 이상 작업을 진행할 수 없습니다.\\n\\n브라우저의 쿠키 허용을 사용하지 않음으로 설정한것 같습니다.\\n\\n브라우저의 인터넷 옵션에서 쿠키 허용을 사용으로 설정해 주십시오.\\n\\n그래도 진행이 되지 않는다면 쇼핑몰 운영자에게 문의 바랍니다.');
}

$tmp_cart_id = preg_replace('/[^a-z0-9_\-]/i', '', $tmp_cart_id);
$act = isset($_POST['act']) ? clean_xss_tags($_POST['act'], 1, 1) : '';
$post_ct_chk = (isset($_POST['ct_chk']) && is_array($_POST['ct_chk'])) ? $_POST['ct_chk'] : array();
$post_it_ids = (isset($_POST['it_id']) && is_array($_POST['it_id'])) ? $_POST['it_id'] : array();

// 레벨(권한)이 상품구입 권한보다 작다면 상품을 구입할 수 없음.
if ($member['mb_level'] < $default['de_level_sell'])
{
    alert('상품을 구입할 수 있는 권한이 없습니다.');
}

if($act == "buy")
{
    if(!count($post_ct_chk))
        alert("주문하실 상품을 하나이상 선택해 주십시오.");

    // 선택필드 초기화
    $sql = " update {$g5['g5_shop_cart_table']} set ct_select = '0' where od_id = '$tmp_cart_id' ";
    sql_query($sql);

    $fldcnt = count($post_it_ids);
    for($i=0; $i<$fldcnt; $i++) {
        $ct_chk = isset($post_ct_chk[$i]) ? 1 : 0;
        if($ct_chk) {
            $it_id = isset($post_it_ids[$i]) ? safe_replace_regex($post_it_ids[$i], 'it_id') : '';
            
            if( !$it_id ) continue;

            // 본인인증, 성인인증체크
            if(!$is_admin) {
                $msg = shop_member_cert_check($it_id, 'item');
                if($msg)
                    alert($msg, G5_SHOP_URL);
            }

            // 주문 상품의 재고체크
            // 동일 상품 옵션이 레코드에 있는 경우 재고를 제대로 체크하지 못하는 오류가 있음
            // $sql = " select ct_qty, it_name, ct_option, io_id, io_type from {$g5['g5_shop_cart_table']} where od_id = '$tmp_cart_id' and it_id = '$it_id' ";

            $sql = " select sum(ct_qty) as ct_qty, it_name, ct_option, io_id, io_type
                        from {$g5['g5_shop_cart_table']}
                        where od_id = '$tmp_cart_id'
                          and it_id = '$it_id' GROUP BY od_id, it_id, it_name, ct_option, io_id, io_type ";

            $result = sql_query($sql);

            for($k=0; $row=sql_fetch_array($result); $k++) {
                $sql = " select SUM(ct_qty) as cnt from {$g5['g5_shop_cart_table']}
                          where od_id <> '$tmp_cart_id'
                            and it_id = '$it_id'
                            and io_id = '{$row['io_id']}'
                            and io_type = '{$row['io_type']}'
                            and ct_stock_use = 0
                            and ct_status = '쇼핑'
                            and ct_select = '1' ";

                $sum = sql_fetch($sql);
                // $sum['cnt'] 가 null 일때 재고 반영이 제대로 안되는 오류 수정 (그누위즈님,210614)
                // $sum_qty = $sum['cnt'];
                $sum_qty = isset($sum['cnt']) ? (int) $sum['cnt'] : 0;

                // 재고 구함
                $ct_qty = $row['ct_qty'];
                if(!$row['io_id'])
                    $it_stock_qty = get_it_stock_qty($it_id);
                else
                    $it_stock_qty = get_option_stock_qty($it_id, $row['io_id'], $row['io_type']);

                if ($ct_qty + $sum_qty > $it_stock_qty)
                {
                    $item_option = $row['it_name'];
                    if($row['io_id'])
                        $item_option .= '('.$row['ct_option'].')';

                    alert($item_option." 의 재고수량이 부족합니다.\\n\\n현재 재고수량 : " . number_format($it_stock_qty - $sum_qty) . " 개");
                }
            }

            $sql = " update {$g5['g5_shop_cart_table']}
                        set ct_select = '1',
                            ct_select_time = '".G5_TIME_YMDHIS."'
                        where od_id = '$tmp_cart_id'
                          and it_id = '$it_id' ";
            sql_query($sql);
        }
    }

    if ($is_member) // 회원인 경우
        goto_url(G5_SHOP_URL.'/orderform.php');
    else
        goto_url(G5_BBS_URL.'/login.php?url='.urlencode(G5_SHOP_URL.'/orderform.php'));
}
else if ($act == "alldelete") // 모두 삭제이면
{
    $sql = " delete from {$g5['g5_shop_cart_table']}
              where od_id = '$tmp_cart_id' ";
    sql_query($sql);
}
else if ($act == "seldelete") // 선택삭제
{
    if(!count($post_ct_chk))
        alert("삭제하실 상품을 하나이상 선택해 주십시오.");

    $fldcnt = count($post_it_ids);
    for($i=0; $i<$fldcnt; $i++) {
        $ct_chk = isset($post_ct_chk[$i]) ? 1 : 0;
        if($ct_chk) {
            $it_id = isset($post_it_ids[$i]) ? safe_replace_regex($post_it_ids[$i], 'it_id') : '';
            if( $it_id ){
                $sql = " delete from {$g5['g5_shop_cart_table']} where it_id = '$it_id' and od_id = '$tmp_cart_id' ";
                sql_query($sql);
            }
        }
    }
}
else // 장바구니에 담기
{
    $count = count($post_it_ids);
    if ($count < 1)
        alert('장바구니에 담을 상품을 선택하여 주십시오.');

    $ct_count = 0;
    $post_chk_it_id = (isset($_POST['chk_it_id']) && is_array($_POST['chk_it_id'])) ? $_POST['chk_it_id'] : array();
    $post_io_ids = (isset($_POST['io_id']) && is_array($_POST['io_id'])) ? $_POST['io_id'] : array();
    $post_io_types = (isset($_POST['io_type']) && is_array($_POST['io_type'])) ? $_POST['io_type'] : array();
    $post_ct_qtys = (isset($_POST['ct_qty']) && is_array($_POST['ct_qty'])) ? $_POST['ct_qty'] : array();

    for($i=0; $i<$count; $i++) {
        // 보관함의 상품을 담을 때 체크되지 않은 상품 건너뜀
        if($act == 'multi' && ! (isset($post_chk_it_id[$i]) && $post_chk_it_id[$i]))
            continue;

        $it_id = isset($post_it_ids[$i]) ? safe_replace_regex($post_it_ids[$i], 'it_id') : '';

        if( !$it_id ) continue;

        $opt_count = (isset($post_io_ids[$it_id]) && is_array($post_io_ids[$it_id])) ? count($post_io_ids[$it_id]) : 0;

        if($opt_count && isset($post_io_types[$it_id][0]) && $post_io_types[$it_id][0] != 0)
            alert('상품의 선택옵션을 선택해 주십시오.');

        for($k=0; $k<$opt_count; $k++) {
            if (isset($post_ct_qtys[$it_id][$k]) && $post_ct_qtys[$it_id][$k] < 1)
                alert('수량은 1 이상 입력해 주십시오.');
        }

        // 본인인증, 성인인증체크
        if(!$is_admin) {
            $msg = shop_member_cert_check($it_id, 'item');
            if($msg)
                alert($msg, G5_SHOP_URL);
        }

        // 상품정보
        $it = get_shop_item($it_id, false);
        if(!$it['it_id'])
            alert('상품정보가 존재하지 않습니다.');

        // 바로구매에 있던 장바구니 자료를 지운다.
        if($i == 0 && $sw_direct)
            sql_query(" delete from {$g5['g5_shop_cart_table']} where od_id = '$tmp_cart_id' and ct_direct = 1 ", false);

        // 최소, 최대 수량 체크
        if($it['it_buy_min_qty'] || $it['it_buy_max_qty']) {
            $sum_qty = 0;
            for($k=0; $k<$opt_count; $k++) {
                if($_POST['io_type'][$it_id][$k] == 0)
                    $sum_qty += (int) $_POST['ct_qty'][$it_id][$k];
            }

            if($it['it_buy_min_qty'] > 0 && $sum_qty < $it['it_buy_min_qty'])
                alert($it['it_name'].'의 선택옵션 개수 총합 '.number_format($it['it_buy_min_qty']).'개 이상 주문해 주십시오.');

            if($it['it_buy_max_qty'] > 0 && $sum_qty > $it['it_buy_max_qty'])
                alert($it['it_name'].'의 선택옵션 개수 총합 '.number_format($it['it_buy_max_qty']).'개 이하로 주문해 주십시오.');

            // 기존에 장바구니에 담긴 상품이 있는 경우에 최대 구매수량 체크
            if($it['it_buy_max_qty'] > 0) {
                $sql4 = " select sum(ct_qty) as ct_sum
                            from {$g5['g5_shop_cart_table']}
                            where od_id = '$tmp_cart_id'
                              and it_id = '$it_id'
                              and io_type = '0'
                              and ct_status = '쇼핑' ";
                $row4 = sql_fetch($sql4);

				$option_sum_qty = ( $act === 'optionmod' ) ? $sum_qty : $sum_qty + $row4['ct_sum'];

                if(($option_sum_qty) > $it['it_buy_max_qty'])
                    alert($it['it_name'].'의 선택옵션 개수 총합 '.number_format($it['it_buy_max_qty']).'개 이하로 주문해 주십시오.', './cart.php');
            }
        }

        // 옵션정보를 얻어서 배열에 저장
        $opt_list = array();
        $sql = " select * from {$g5['g5_shop_item_option_table']} where it_id = '$it_id' and io_use = 1 order by io_no asc ";
        $result = sql_query($sql);
        $lst_count = 0;
        for($k=0; $row=sql_fetch_array($result); $k++) {
            $opt_list[$row['io_type']][$row['io_id']]['id'] = $row['io_id'];
            $opt_list[$row['io_type']][$row['io_id']]['use'] = $row['io_use'];
            $opt_list[$row['io_type']][$row['io_id']]['price'] = $row['io_price'];
            $opt_list[$row['io_type']][$row['io_id']]['stock'] = $row['io_stock_qty'];

            // 선택옵션 개수
            if(!$row['io_type'])
                $lst_count++;
        }

        //--------------------------------------------------------
        //  재고 검사, 바로구매일 때만 체크
        //--------------------------------------------------------
        // 이미 주문폼에 있는 같은 상품의 수량합계를 구한다.
        if($sw_direct) {
            for($k=0; $k<$opt_count; $k++) {
                $io_id = isset($_POST['io_id'][$it_id][$k]) ? preg_replace(G5_OPTION_ID_FILTER, '', $_POST['io_id'][$it_id][$k]) : '';
                $io_type = isset($_POST['io_type'][$it_id][$k]) ? preg_replace('#[^01]#', '', $_POST['io_type'][$it_id][$k]) : '';
                $io_value = isset($_POST['io_value'][$it_id][$k]) ? $_POST['io_value'][$it_id][$k] : '';

                $sql = " select SUM(ct_qty) as cnt from {$g5['g5_shop_cart_table']}
                          where od_id <> '$tmp_cart_id'
                            and it_id = '$it_id'
                            and io_id = '$io_id'
                            and io_type = '$io_type'
                            and ct_stock_use = 0
                            and ct_status = '쇼핑'
                            and ct_select = '1' ";
                $row = sql_fetch($sql);
                $sum_qty = $row['cnt'];

                // 재고 구함
                $ct_qty = isset($_POST['ct_qty'][$it_id][$k]) ? (int) $_POST['ct_qty'][$it_id][$k] : 0;
                if(!$io_id)
                    $it_stock_qty = get_it_stock_qty($it_id);
                else
                    $it_stock_qty = get_option_stock_qty($it_id, $io_id, $io_type);

                if ($ct_qty + $sum_qty > $it_stock_qty)
                {
                    alert($io_value." 의 재고수량이 부족합니다.\\n\\n현재 재고수량 : " . number_format($it_stock_qty - $sum_qty) . " 개");
                }
            }
        }
        //--------------------------------------------------------

        // 옵션수정일 때 기존 장바구니 자료를 먼저 삭제
        if($act == 'optionmod')
            sql_query(" delete from {$g5['g5_shop_cart_table']} where od_id = '$tmp_cart_id' and it_id = '$it_id' ");

        // 장바구니에 Insert
        // 바로구매일 경우 장바구니가 체크된것으로 강제 설정
        if($sw_direct) {
            $ct_select = 1;
            $ct_select_time = G5_TIME_YMDHIS;
        } else {
            $ct_select = 0;
            $ct_select_time = '0000-00-00 00:00:00';
        }

        // 장바구니에 Insert
        $comma = '';
        $sql = " INSERT INTO {$g5['g5_shop_cart_table']}
                        ( od_id, mb_id, it_id, it_name, it_sc_type, it_sc_method, it_sc_price, it_sc_minimum, it_sc_qty, ct_status, ct_price, ct_point, ct_point_use, ct_stock_use, ct_option, ct_qty, ct_notax, io_id, io_type, io_price, ct_time, ct_ip, ct_send_cost, ct_direct, ct_select, ct_select_time )
                    VALUES ";

        for($k=0; $k<$opt_count; $k++) {
            $io_id = isset($_POST['io_id'][$it_id][$k]) ? preg_replace(G5_OPTION_ID_FILTER, '', $_POST['io_id'][$it_id][$k]) : '';
            $io_type = isset($_POST['io_type'][$it_id][$k]) ? preg_replace('#[^01]#', '', $_POST['io_type'][$it_id][$k]) : '';
            $io_value = isset($_POST['io_value'][$it_id][$k]) ? $_POST['io_value'][$it_id][$k] : '';

            // 선택옵션정보가 존재하는데 선택된 옵션이 없으면 건너뜀
            if($lst_count && $io_id == '')
                continue;

            // 구매할 수 없는 옵션은 건너뜀
            if($io_id && !$opt_list[$io_type][$io_id]['use'])
                continue;

            $io_price = isset($opt_list[$io_type][$io_id]['price']) ? $opt_list[$io_type][$io_id]['price'] : 0;
            $ct_qty = isset($_POST['ct_qty'][$it_id][$k]) ? (int) $_POST['ct_qty'][$it_id][$k] : 0;

            // 구매가격이 음수인지 체크
            if($io_type) {
                if((int)$io_price < 0)
                    alert('구매금액이 음수인 상품은 구매할 수 없습니다.');
            } else {
                if((int)$it['it_price'] + (int)$io_price < 0)
                    alert('구매금액이 음수인 상품은 구매할 수 없습니다.');
            }

            // 동일옵션의 상품이 있으면 수량 더함
            $sql2 = " select ct_id, io_type, ct_qty
                        from {$g5['g5_shop_cart_table']}
                        where od_id = '$tmp_cart_id'
                          and it_id = '$it_id'
                          and io_id = '$io_id'
                          and ct_status = '쇼핑' ";
            $row2 = sql_fetch($sql2);
            if(isset($row2['ct_id']) && $row2['ct_id']) {
                // 재고체크
                $tmp_ct_qty = $row2['ct_qty'];
                if(!$io_id)
                    $tmp_it_stock_qty = get_it_stock_qty($it_id);
                else
                    $tmp_it_stock_qty = get_option_stock_qty($it_id, $io_id, $row2['io_type']);

                if ($tmp_ct_qty + $ct_qty > $tmp_it_stock_qty)
                {
                    alert($io_value." 의 재고수량이 부족합니다.\\n\\n현재 재고수량 : " . number_format($tmp_it_stock_qty) . " 개");
                }

                $sql3 = " update {$g5['g5_shop_cart_table']}
                            set ct_qty = ct_qty + '$ct_qty'
                            where ct_id = '{$row2['ct_id']}' ";
                sql_query($sql3);
                continue;
            }

            // 포인트
            $point = 0;
            if($config['cf_use_point']) {
                if($io_type == 0) {
                    $point = get_item_point($it, $io_id);
                } else {
                    $point = $it['it_supply_point'];
                }

                if($point < 0)
                    $point = 0;
            }
            
            $ct_send_cost = isset($_REQUEST['ct_send_cost']) ? (int) $_REQUEST['ct_send_cost'] : 0;

            // 배송비결제
            if($it['it_sc_type'] == 1)
                $ct_send_cost = 2; // 무료
            else if($it['it_sc_type'] > 1 && $it['it_sc_method'] == 1)
                $ct_send_cost = 1; // 착불
            
            $io_value = sql_real_escape_string(strip_tags($io_value));
            $remote_addr = get_real_client_ip();

            $sql .= $comma."( '$tmp_cart_id', '{$member['mb_id']}', '{$it['it_id']}', '".addslashes($it['it_name'])."', '{$it['it_sc_type']}', '{$it['it_sc_method']}', '{$it['it_sc_price']}', '{$it['it_sc_minimum']}', '{$it['it_sc_qty']}', '쇼핑', '{$it['it_price']}', '$point', '0', '0', '$io_value', '$ct_qty', '{$it['it_notax']}', '$io_id', '$io_type', '$io_price', '".G5_TIME_YMDHIS."', '$remote_addr', '$ct_send_cost', '$sw_direct', '$ct_select', '$ct_select_time' )";
            $comma = ' , ';
            $ct_count++;
        }

        if($ct_count > 0)
            sql_query($sql);
    }
}

// 바로 구매일 경우
if ($sw_direct)
{
    if ($is_member)
    {
    	goto_url(G5_SHOP_URL."/orderform.php?sw_direct=$sw_direct");
    }
    else
    {
    	goto_url(G5_BBS_URL."/login.php?url=".urlencode(G5_SHOP_URL."/orderform.php?sw_direct=$sw_direct"));
    }
}
else
{
    goto_url(G5_SHOP_URL.'/cart.php');
}