<?php
include_once './_common.php';

// print_r2($_POST); exit;

$fnonce = isset($_POST['fnonce']) ? $_POST['fnonce'] : '';
$form_id = isset($_POST['form_id']) ? $_POST['form_id'] : '';

$pattern = '#[/\'\"%=*\#\(\)\|\+\&\!\$~\{\}\[\]`;:\?\^\,]#';
$it_id = isset($_POST['it_id']) ? preg_replace($pattern, '', $_POST['it_id']) : '';
$od_id = isset($_POST['od_id']) ? safe_replace_regex($_POST['od_id'], 'od_id') : '';
$return_query = isset($_POST['return_query']) ? $_POST['return_query'] : '';
$ct_dels = isset($_POST['ct_dels']) ? clean_xss_tags($_POST['ct_dels']) : '';

if (!$od_id) {
    exit;
}

alert_verify_form_nonce($fnonce, $form_id);

$od = get_subscription_order($od_id);

if (!(isset($od['od_id']) && $od['od_id'])) {
    die('no-order');
}

if ($is_admin !== 'super' && $member['mb_id'] !== $od['mb_id']) {
    die('권한이 없습니다.');
}

// 재고를 체크할 경우
$sw_direct = 1;

$act = isset($_POST['act']) ? clean_xss_tags($_POST['act'], 1, 1) : '';
$post_it_ids = (isset($_POST['it_id']) && is_array($_POST['it_id'])) ? $_POST['it_id'] : array();

$count = count($post_it_ids);

if ($count < 1) {
    alert('장바구니에 담을 상품을 선택하여 주십시오.');
}

$log_messages = array();
$ct_count = 0;
$post_chk_it_id = (isset($_POST['chk_it_id']) && is_array($_POST['chk_it_id'])) ? $_POST['chk_it_id'] : array();
$post_io_ids = (isset($_POST['io_id']) && is_array($_POST['io_id'])) ? $_POST['io_id'] : array();
$post_io_types = (isset($_POST['io_type']) && is_array($_POST['io_type'])) ? $_POST['io_type'] : array();
$post_ct_qtys = (isset($_POST['ct_qty']) && is_array($_POST['ct_qty'])) ? $_POST['ct_qty'] : array();
$post_ct_ids = (isset($_POST['ct_id']) && is_array($_POST['ct_id'])) ? $_POST['ct_id'] : array();

if ($ct_dels) {
    $arr_ct_dels = explode(',', $ct_dels);
    $sql = "select count(*) as total from `{$g5['g5_subscription_cart_table']}` where od_id = '{$od['od_id']}' and mb_id = '{$od['mb_id']}' ";
    $ex = sql_fetch($sql);
    
    if (isset($ex['total']) && $ex['total'] && (int)$ex['total'] <= count($arr_ct_dels)) {
        alert('모든 옵션을 삭제할 수는 없습니다. 하나 이상의 옵션이 필요합니다.');
    }
    
    foreach($arr_ct_dels as $ct_id) {
        if (empty($ct_id)) {
            continue;
        }
        
        $sql_before = "SELECT ct_option, ct_qty, it_name
                       FROM {$g5['g5_subscription_cart_table']} 
                       WHERE od_id = '{$od['od_id']}' 
                       AND mb_id = '{$od['mb_id']}' 
                       AND ct_id = '{$ct_id}'";
        $ct = sql_fetch($sql_before);
            
        $log_messages[] = sprintf(
            "옵션 %s %s 수량 %d를 장바구니에서 삭제했습니다. %s",
            strip_tags($ct['it_name']),
            strip_tags($ct['ct_option']),
            $ct['ct_qty']
        );
                
        $sql = "delete from `{$g5['g5_subscription_cart_table']}` where od_id = '{$od['od_id']}' and mb_id = '{$od['mb_id']}' and ct_id = '". (int)$ct_id ."' ";
        sql_query($sql, false);
    }
}

for ($i = 0; $i < $count; ++$i) {

    $it_id = isset($post_it_ids[$i]) ? safe_replace_regex($post_it_ids[$i], 'it_id') : '';

    if (!$it_id) {
        continue;
    }

    $opt_count = (isset($post_io_ids[$it_id]) && is_array($post_io_ids[$it_id])) ? count($post_io_ids[$it_id]) : 0;

    if ($opt_count && isset($post_io_types[$it_id][0]) && $post_io_types[$it_id][0] != 0) {
        alert('상품의 선택옵션을 선택해 주십시오.');
    }

    for ($k = 0; $k < $opt_count; ++$k) {
        if (isset($post_ct_qtys[$it_id][$k]) && $post_ct_qtys[$it_id][$k] < 1) {
            alert('수량은 1 이상 입력해 주십시오.');
        }
    }
    
    // 상품정보
    $it = get_subscription_item($it_id, false);
    if (! (isset($it['it_id']) && $it['it_id'])) {
        alert('상품정보가 존재하지 않습니다.');
    }

    // 최소, 최대 수량 체크
    if ($it['it_buy_min_qty'] || $it['it_buy_max_qty']) {
        $sum_qty = 0;
        for ($k = 0; $k < $opt_count; ++$k) {
            if ($_POST['io_type'][$it_id][$k] == 0) {
                $sum_qty += (int) $_POST['ct_qty'][$it_id][$k];
            }
        }

        if ($it['it_buy_min_qty'] > 0 && $sum_qty < $it['it_buy_min_qty']) {
            alert($it['it_name'] . '의 선택옵션 개수 총합 ' . number_format($it['it_buy_min_qty']) . '개 이상 주문해 주십시오.');
        }

        if ($it['it_buy_max_qty'] > 0 && $sum_qty > $it['it_buy_max_qty']) {
            alert($it['it_name'] . '의 선택옵션 개수 총합 ' . number_format($it['it_buy_max_qty']) . '개 이하로 주문해 주십시오.');
        }

        // 기존에 장바구니에 담긴 상품이 있는 경우에 최대 구매수량 체크
        if ($it['it_buy_max_qty'] > 0) {
            $sql4 = " select sum(ct_qty) as ct_sum
                        from {$g5['g5_subscription_cart_table']}
                        where od_id = '$od_id'
                          and it_id = '$it_id'
                          and io_type = '0'
                          and ct_status = '쇼핑' ";
            $row4 = sql_fetch($sql4);

            $option_sum_qty = ($act === 'optionmod') ? $sum_qty : $sum_qty + $row4['ct_sum'];

            if ($option_sum_qty > $it['it_buy_max_qty']) {
                alert($it['it_name'] . '의 선택옵션 개수 총합 ' . number_format($it['it_buy_max_qty']) . '개 이하로 주문해 주십시오.');
            }
        }
    }

    // 옵션정보를 얻어서 배열에 저장
    $opt_list = array();
    $sql = " select * from {$g5['g5_shop_item_option_table']} where it_id = '$it_id' and io_use = 1 order by io_no asc ";
    $result = sql_query($sql);
    $lst_count = 0;
    for ($k = 0; $row = sql_fetch_array($result); ++$k) {
        $opt_list[$row['io_type']][$row['io_id']]['id'] = $row['io_id'];
        $opt_list[$row['io_type']][$row['io_id']]['use'] = $row['io_use'];
        $opt_list[$row['io_type']][$row['io_id']]['price'] = $row['io_price'];
        $opt_list[$row['io_type']][$row['io_id']]['stock'] = $row['io_stock_qty'];

        // 선택옵션 개수
        if (!$row['io_type']) {
            ++$lst_count;
        }
    }

    // --------------------------------------------------------
    //  재고 검사, 바로구매일 때만 체크
    // --------------------------------------------------------
    // 이미 주문폼에 있는 같은 상품의 수량합계를 구한다.
    if ($sw_direct) {
        for ($k = 0; $k < $opt_count; ++$k) {
            $io_id = isset($_POST['io_id'][$it_id][$k]) ? preg_replace(G5_OPTION_ID_FILTER, '', $_POST['io_id'][$it_id][$k]) : '';
            $io_type = isset($_POST['io_type'][$it_id][$k]) ? preg_replace('#[^01]#', '', $_POST['io_type'][$it_id][$k]) : '';
            $io_value = isset($_POST['io_value'][$it_id][$k]) ? $_POST['io_value'][$it_id][$k] : '';

            $sql = " select SUM(ct_qty) as cnt from {$g5['g5_subscription_cart_table']}
                      where od_id <> '$od_id'
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
            if (!$io_id) {
                $it_stock_qty = get_subscription_it_stock_qty($it_id);
            } else {
                $it_stock_qty = get_subscription_option_stock_qty($it_id, $io_id, $io_type);
            }

            if ($ct_qty + $sum_qty > $it_stock_qty) {
                alert($io_value . ' 의 재고수량이 부족합니다.\\n\\n현재 재고수량 : ' . number_format($it_stock_qty - $sum_qty) . ' 개');
            }
        }
    }
    // -------------------------------------------------------- 

    // 포인트
    $point = 0;

    if ($config['cf_use_point']) {
        if ($io_type == 0) {
            $point = get_item_point($it, $io_id);
        } else {
            $point = $it['it_supply_point'];
        }

        if ($point < 0)
            $point = 0;
    }
            
    // 장바구니에 Insert
    // 바로구매일 경우 장바구니가 체크된것으로 강제 설정
    if ($sw_direct) {
        $ct_select = 1;
        $ct_select_time = G5_TIME_YMDHIS;
    } else {
        $ct_select = 0;
        $ct_select_time = '0000-00-00 00:00:00';
    }

    // 장바구니에 Insert
    $comma = '';
    /*
    $sql = " INSERT INTO {$g5['g5_subscription_cart_table']}
                    ( od_id, mb_id, it_id, it_name, it_sc_type, it_sc_method, it_sc_price, it_sc_minimum, it_sc_qty, ct_status, ct_price, ct_point, ct_point_use, ct_stock_use, ct_option, ct_qty, ct_notax, io_id, io_type, io_price, ct_time, ct_ip, ct_send_cost, ct_direct, ct_select, ct_select_time )
                VALUES ";
    */
    
    // print_r2($_POST);

    // print_r($opt_count);
    // exit;
    
    $updates = array();
    $is_update = 0;
    
    for ($k = 0; $k < $opt_count; ++$k) {
        $io_id = isset($_POST['io_id'][$it_id][$k]) ? preg_replace(G5_OPTION_ID_FILTER, '', $_POST['io_id'][$it_id][$k]) : '';
        $io_type = isset($_POST['io_type'][$it_id][$k]) ? preg_replace('#[^01]#', '', $_POST['io_type'][$it_id][$k]) : '';
        $io_value = isset($_POST['io_value'][$it_id][$k]) ? $_POST['io_value'][$it_id][$k] : '';

        // 선택옵션정보가 존재하는데 선택된 옵션이 없으면 건너뜀
        if ($lst_count && $io_id == '') {
            continue;
        }

        // 구매할 수 없는 옵션은 건너뜀
        if ($io_id && !$opt_list[$io_type][$io_id]['use']) {
            continue;
        }

        $io_price = isset($opt_list[$io_type][$io_id]['price']) ? $opt_list[$io_type][$io_id]['price'] : 0;
        $ct_qty = isset($_POST['ct_qty'][$it_id][$k]) ? (int) $_POST['ct_qty'][$it_id][$k] : 0;
        $ct_id = isset($_POST['ct_id'][$it_id][$k]) ? (int) $_POST['ct_id'][$it_id][$k] : 0;
        
        // 구매가격이 음수인지 체크
        if ($io_type) {
            if ((int) $io_price < 0) {
                alert('구매금액이 음수인 상품은 구매할 수 없습니다.');
            }
        } else {
            if ((int) $it['it_price'] + (int) $io_price < 0) {
                alert('구매금액이 음수인 상품은 구매할 수 없습니다.');
            }
        }

        $ct_send_cost = isset($_REQUEST['ct_send_cost']) ? (int) $_REQUEST['ct_send_cost'] : 0;

        // 배송비결제
        if ($it['it_sc_type'] == 1) {
            $ct_send_cost = 2;
        } // 무료
        elseif ($it['it_sc_type'] > 1 && $it['it_sc_method'] == 1) {
            $ct_send_cost = 1;
        } // 착불
        
        $before_data = null;
        
        if ($ct_id) {
            
            // 변경 전 데이터 조회
            $sql_before = "SELECT ct_option, ct_qty 
                           FROM {$g5['g5_subscription_cart_table']} 
                           WHERE od_id = '{$od['od_id']}' 
                           AND mb_id = '{$od['mb_id']}' 
                           AND ct_id = '{$ct_id}'";
            $before_data = sql_fetch($sql_before);
            
            $updates['ct_option'] = sql_real_escape_string(strip_tags($io_value));
            $updates['ct_qty'] = $ct_qty;
            $updates['io_id'] = $io_id;
            $updates['io_type'] = $io_type;
            $updates['io_price'] = $io_price;
            
            $valueSets = array();

            foreach ($updates as $key => $value) {
                $valueSets[] = $key . " = '" . $value . "'";
            }
            
            $sql = "UPDATE `{$g5['g5_subscription_cart_table']}` SET " . implode(', ', $valueSets) . " where od_id = '{$od['od_id']}' and mb_id = '{$od['mb_id']}' and ct_id = '{$ct_id}' ";

            $result = sql_query($sql);
        
        } else {
            
            $inserts = array(
                'od_id'           => $od['od_id'],
                'mb_id'           => $od['mb_id'],
                'it_id'           => $it['it_id'],
                'it_name'         => sql_real_escape_string($it['it_name']),
                'it_sc_type'      => $it['it_sc_type'],
                'it_sc_method'    => $it['it_sc_method'],
                'it_sc_price'     => $it['it_sc_price'],
                'it_sc_minimum'   => $it['it_sc_minimum'],
                'it_sc_qty'       => $it['it_sc_qty'],
                'ct_status'       => '구독',
                'ct_price'        => $it['it_price'],
                'ct_point'        => $point,
                'ct_point_use'    => '0',
                'ct_stock_use'    => '0',
                'ct_option'       => sql_real_escape_string(strip_tags($io_value)),
                'ct_qty'          => $ct_qty,
                'ct_notax'        => $it['it_notax'],
                'io_id'           => $io_id,
                'io_type'         => $io_type,
                'io_price'        => $io_price,
                'ct_time'         => G5_TIME_YMDHIS,
                'ct_ip'           => $_SERVER['REMOTE_ADDR'],
                'ct_send_cost'    => $ct_send_cost,
                'ct_direct'       => $sw_direct,
                'ct_select'       => $ct_select,
                'ct_select_time'  => $ct_select_time
            );
            
            // https://stackoverflow.com/questions/10054633/insert-array-into-mysql-database-with-php
            $columns = implode(', ', array_keys($inserts));
            $values = implode("', '", array_values($inserts));

            $sql = "INSERT INTO `{$g5['g5_subscription_cart_table']}`($columns) VALUES ('$values')";
            
            $result = sql_query($sql);
        }
        
        if ($result) {
            
            $is_update++;
            
            // 로그 메시지 생성
            if ($before_data) {
                
                if ($before_data['ct_option'] != $io_value || $before_data['ct_qty'] != $ct_qty) {
                    $log_messages[] = sprintf(
                        "기존 옵션 %s 이 %s 수량 %d에서 %s 수량 %d로 변경되었습니다.",
                        strip_tags($it['it_name']),
                        strip_tags($before_data['ct_option']),
                        $before_data['ct_qty'],
                        strip_tags($io_value),
                        $ct_qty
                    );
                }
                
            } else {
                // 신규 상품 추가된 경우
                $log_messages[] = sprintf(
                    "새로운 옵션 %s %s 수량 %d이 장바구니에 추가되었습니다.",
                    strip_tags($it['it_name']),
                    strip_tags($io_value),
                    $ct_qty
                );
            }
            
        }
        
        /*
        $sql .= $comma . "( '$od_id', '{$member['mb_id']}', '{$it['it_id']}', '" . addslashes($it['it_name']) . "', '{$it['it_sc_type']}', '{$it['it_sc_method']}', '{$it['it_sc_price']}', '{$it['it_sc_minimum']}', '{$it['it_sc_qty']}', '쇼핑', '{$it['it_price']}', '$point', '0', '0', '$io_value', '$ct_qty', '{$it['it_notax']}', '$io_id', '$io_type', '$io_price', '" . G5_TIME_YMDHIS . "', '$remote_addr', '$ct_send_cost', '$sw_direct', '$ct_select', '$ct_select_time' )";
        $comma = ' , ';
        */
    }
    
    if ($is_update) {
        $subs = get_subscription_total_amount($od);
        
        if ($subs && isset($subs['cart_price'])) {
            $sql = "UPDATE `{$g5['g5_subscription_order_table']}` SET od_cart_price = '" . (int)$subs['cart_price'] . "', od_receipt_price = '" . (int)$subs['total_amount'] . "' where od_id = '{$od['od_id']}' and mb_id = '{$od['mb_id']}' ";
            
            $result = sql_query($sql);
        }
    }
    
    if ($log_messages) {
        
        $log_message = implode("\n", $log_messages);
        
        add_subscription_order_history($log_message, array(
            'hs_type' => 'subscription_cart_update',
            'od_id' => $od['od_id'],
            'mb_id' => $member['mb_id']
        ));
    }
}

goto_url(G5_SUBSCRIPTION_ADMIN_URL . '/orderform.php?'.$return_query);