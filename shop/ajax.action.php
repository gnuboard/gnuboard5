<?php
include_once('./_common.php');

$action = isset($_REQUEST['action']) ? preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['action']) : '';

switch ($action) {
    case 'refresh_cart' :

        // 보관기간이 지난 상품 삭제
        cart_item_clean();

        $s_cart_id = preg_replace('/[^a-z0-9_\-]/i', '', get_session('ss_cart_id'));

        // 선택필드 초기화
        if( $s_cart_id ){
            $sql = " update {$g5['g5_shop_cart_table']} set ct_select = '0' where od_id = '$s_cart_id' ";
            sql_query($sql);
        }

        include_once(G5_SHOP_SKIN_PATH.'/boxcart.skin.php'); // 장바구니
        break;

    case 'refresh_wish' :
        
        if( !$is_member ){
            die('');
        }

        include_once(G5_SHOP_SKIN_PATH.'/boxwish.skin.php'); // 위시리스트
        break;

    case 'cart_delete' :
        
        // 보관기간이 지난 상품 삭제
        cart_item_clean();

        $s_cart_id = get_session('ss_cart_id');
        $it_id = isset($_POST['it_id']) ? safe_replace_regex($_POST['it_id'], 'it_id') : '';

        // 장바구니 상품삭제
        $sql = " delete from {$g5['g5_shop_cart_table']}
                    where od_id = '".$s_cart_id."'
                      and it_id = '{$it_id}' ";
        sql_query($sql);

        die(json_encode(array('error' => '')));

        break;
    case 'cart_update' : 

        // 보관기간이 지난 상품 삭제
        cart_item_clean();

        // cart id 설정
        set_cart_id($sw_direct);

        if($sw_direct)
            $tmp_cart_id = get_session('ss_cart_direct');
        else
            $tmp_cart_id = get_session('ss_cart_id');

        // 브라우저에서 쿠키를 허용하지 않은 경우라고 볼 수 있음.
        if (!$tmp_cart_id)
        {
            die(json_encode(array('error' => '더 이상 작업을 진행할 수 없습니다.\n\n브라우저의 쿠키 허용을 사용하지 않음으로 설정한것 같습니다.\n\n브라우저의 인터넷 옵션에서 쿠키 허용을 사용으로 설정해 주십시오.\n\n그래도 진행이 되지 않는다면 쇼핑몰 운영자에게 문의 바랍니다.')));
        }
        
        $tmp_cart_id = preg_replace('/[^a-z0-9_\-]/i', '', $tmp_cart_id);

        // 레벨(권한)이 상품구입 권한보다 작다면 상품을 구입할 수 없음.
        if ($member['mb_level'] < $default['de_level_sell'])
        {
            die(json_encode(array('error' => '상품을 구입할 수 있는 권한이 없습니다.')));
        }

        $count = (isset($_POST['it_id']) && is_array($_POST['it_id'])) ? count($_POST['it_id']) : 0;

        if ($count < 1)
            die(json_encode(array('error' => '장바구니에 담을 상품을 선택하여 주십시오.')));

        $ct_count = 0;
        for($i=0; $i<$count; $i++) {
            $it_id = isset($_POST['it_id'][$i]) ? safe_replace_regex($_POST['it_id'][$i], 'it_id') : '';
            $opt_count = (isset($_POST['io_id'][$it_id]) && is_array($_POST['io_id'][$it_id])) ? count($_POST['io_id'][$it_id]) : 0;

            // 상품정보
            $it = get_shop_item($it_id, false);
            if(!$it['it_id'])
                die(json_encode(array('error' => '상품정보가 존재하지 않습니다.')));

            // 옵션정보를 얻어서 배열에 저장
            $opt_list = array();
            $sql = " select * from {$g5['g5_shop_item_option_table']} where it_id = '$it_id' order by io_no asc ";
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

            if($lst_count > 0 && !trim($_POST['io_id'][$it_id][$i]) && $_POST['io_type'][$it_id][$i] == 0)
                die(json_encode(array('error' => '상품의 선택옵션을 선택해 주십시오.')));

            for($k=0; $k<$opt_count; $k++) {
                $post_ct_qty = isset($_POST['ct_qty'][$it_id][$k]) ? (int) $_POST['ct_qty'][$it_id][$k] : 0;
                if ($post_ct_qty < 1)
                    die(json_encode(array('error' => '수량은 1 이상 입력해 주십시오.')));
            }

            // 바로구매에 있던 장바구니 자료를 지운다.
            if($i == 0 && $sw_direct)
                sql_query(" delete from {$g5['g5_shop_cart_table']} where od_id = '$tmp_cart_id' and ct_direct = 1 ", false);

            // 최소, 최대 수량 체크
            if($it['it_buy_min_qty'] || $it['it_buy_max_qty']) {
                $sum_qty = 0;
                for($k=0; $k<$opt_count; $k++) {
                    if(isset($_POST['io_type'][$it_id][$k]) && $_POST['io_type'][$it_id][$k] == 0){
                        $post_ct_qty = isset($_POST['ct_qty'][$it_id][$k]) ? (int) $_POST['ct_qty'][$it_id][$k] : 0;
                        $sum_qty += $post_ct_qty;
                    }
                }

                if($it['it_buy_min_qty'] > 0 && $sum_qty < $it['it_buy_min_qty'])
                    die(json_encode(array('error' => $it['it_name'].'의 선택옵션 개수 총합 '.number_format($it['it_buy_min_qty']).'개 이상 주문해 주십시오.')));

                if($it['it_buy_max_qty'] > 0 && $sum_qty > $it['it_buy_max_qty'])
                    die(json_encode(array('error' => $it['it_name'].'의 선택옵션 개수 총합 '.number_format($it['it_buy_max_qty']).'개 이하로 주문해 주십시오.')));

                // 기존에 장바구니에 담긴 상품이 있는 경우에 최대 구매수량 체크
                if($it['it_buy_max_qty'] > 0) {
                    $sql4 = " select sum(ct_qty) as ct_sum
                                from {$g5['g5_shop_cart_table']}
                                where od_id = '$tmp_cart_id'
                                  and it_id = '$it_id'
                                  and io_type = '0'
                                  and ct_status = '쇼핑' ";
                    $row4 = sql_fetch($sql4);

                    if(($sum_qty + $row4['ct_sum']) > $it['it_buy_max_qty'])
                        die(json_encode(array('error' => $it['it_name'].'의 선택옵션 개수 총합 '.number_format($it['it_buy_max_qty']).'개 이하로 주문해 주십시오.', './cart.php')));
                }
            }

            // 장바구니에 Insert
            // 바로구매일 경우 장바구니가 체크된것으로 강제 설정
            $ct_select = 0;
            $ct_select_time = '0000-00-00 00:00:00';

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
                
                $opt_list_type_id_use = isset($opt_list[$io_type][$io_id]['use']) ? $opt_list[$io_type][$io_id]['use'] : '';
                // 구매할 수 없는 옵션은 건너뜀
                if($io_id && ! $opt_list_type_id_use)
                    continue;

                $io_price = isset($opt_list[$io_type][$io_id]['price']) ? $opt_list[$io_type][$io_id]['price'] : 0;
                $ct_qty = isset($_POST['ct_qty'][$it_id][$k]) ? (int) $_POST['ct_qty'][$it_id][$k] : 0;

                // 구매가격이 음수인지 체크
                if($io_type) {
                    if((int)$io_price < 0)
                        die(json_encode(array('error' => '구매금액이 음수인 상품은 구매할 수 없습니다.')));
                } else {
                    if((int)$it['it_price'] + (int)$io_price < 0)
                        die(json_encode(array('error' => '구매금액이 음수인 상품은 구매할 수 없습니다.')));
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
                        die(json_encode(array('error' => $io_value." 의 재고수량이 부족합니다.\n\n현재 재고수량 : " . number_format($tmp_it_stock_qty) . " 개")));
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
                
                $ct_send_cost = 0;

                // 배송비결제
                if($it['it_sc_type'] == 1)
                    $ct_send_cost = 2; // 무료
                else if($it['it_sc_type'] > 1 && $it['it_sc_method'] == 1)
                    $ct_send_cost = 1; // 착불

                $io_value = sql_real_escape_string(strip_tags($io_value));

                $sql .= $comma."( '$tmp_cart_id', '{$member['mb_id']}', '{$it['it_id']}', '".addslashes($it['it_name'])."', '{$it['it_sc_type']}', '{$it['it_sc_method']}', '{$it['it_sc_price']}', '{$it['it_sc_minimum']}', '{$it['it_sc_qty']}', '쇼핑', '{$it['it_price']}', '$point', '0', '0', '$io_value', '$ct_qty', '{$it['it_notax']}', '$io_id', '$io_type', '$io_price', '".G5_TIME_YMDHIS."', '".$_SERVER['REMOTE_ADDR']."', '$ct_send_cost', '$sw_direct', '$ct_select', '$ct_select_time' )";
                $comma = ' , ';
                $ct_count++;
            }

            if($ct_count > 0)
                sql_query($sql);
        }

        die(json_encode(array('error' => '')));
        break;

    case 'get_item_option' :
        
        $it = get_shop_item($it_id, true);

        if(!$it['it_id'])
            die(json_encode(array('error' => '상품정보가 존재하지 않습니다.')));

        // 상품품절체크
        $is_soldout = is_soldout($it['it_id']);

        // 주문가능체크
        $is_orderable = true;
        if(!$it['it_use'] || $it['it_tel_inq'] || $is_soldout)
            die(json_encode(array('error' => '상품을 구매할 수 없습니다.')));

        $item_ct_qty = 1;
        if($it['it_buy_min_qty'] > 1)
            $item_ct_qty = $it['it_buy_min_qty'];

        $action_url = G5_SHOP_URL.'/ajax.action.php';

        $is_option   = 0;
        $option_item = get_shop_item_options($it['it_id'], $it['it_option_subject'], 0);

        ob_start();
        ?>
        <div class="sct_cartop_wr">
            <form name="fcart" method="post" action="<?php echo $action_url; ?>">
            <input type="hidden" name="action" value="cart_update">
            <input type="hidden" name="it_id[]" value="<?php echo $it['it_id']; ?>">
            <input type="hidden" name="it_name[]" value="<?php echo stripslashes($it['it_name']); ?>">
            <input type="hidden" name="it_price[]" value="<?php echo get_price($it); ?>">
            <input type="hidden" name="it_stock[]" value="<?php echo get_it_stock_qty($it['it_id']); ?>">
            <input type="hidden" name="io_type[<?php echo $it['it_id']; ?>][]" value="0">
            <input type="hidden" name="io_id[<?php echo $it['it_id']; ?>][]" value="">
            <input type="hidden" name="io_value[<?php echo $it['it_id']; ?>][]" value="">
            <input type="hidden" name="io_price[<?php echo $it['it_id']; ?>][]" value="">
            <input type="hidden" name="ct_qty[<?php echo $it['it_id']; ?>][]" value="<?php echo $item_ct_qty; ?>">
            <input type="hidden" name="sw_direct" value="0">
                <?php
                if($option_item) {
                    $is_option = 1;
                ?>

                <?php // 선택옵션
                    echo $option_item;
                ?>

                <button type="button" class="cartopt_cart_btn">장바구니 담기</button>
                <button type="button" class="cartopt_close_btn">닫기</button>

                <?php } ?>
            </form>
        </div>
        <?php
        $content = ob_get_contents();
        ob_end_clean();

        $result = array(
            'error'  => '',
            'option' => $is_option,
            'html'   => $content
        );

        die(json_encode($result));

        break;
    case 'wish_update' :
        
        $it_id = isset($_POST['it_id']) ? safe_replace_regex($_POST['it_id'], 'it_id') : '';

        if (!$is_member)
            die('회원 전용 서비스 입니다.');

        if(!$it_id)
            die('상품 코드가 올바르지 않습니다.');

        // 상품정보 체크
        $row = get_shop_item($it_id, true);

        if(! (isset($row['it_id']) && $row['it_id']))
            die('상품정보가 존재하지 않습니다.');

        $sql = " select wi_id from {$g5['g5_shop_wish_table']}
                  where mb_id = '{$member['mb_id']}' and it_id = '$it_id' ";
        $row = sql_fetch($sql);

        if (! (isset($row['wi_id']) && $row['wi_id'])) {
            $sql = " insert {$g5['g5_shop_wish_table']}
                        set mb_id = '{$member['mb_id']}',
                            it_id = '$it_id',
                            wi_time = '".G5_TIME_YMDHIS."',
                            wi_ip = '".$_SERVER['REMOTE_ADDR']."' ";
            sql_query($sql);

            die('OK');
        } else {
            die('위시리스트에 이미 등록된 상품입니다.');
        }

        break;
    default :
}