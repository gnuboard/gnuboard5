<?php
if (!defined('_GNUBOARD_')) exit;

// 관리자 분류 업데이트시 hook
add_event('shop_admin_category_created', 'fn_subscription_update_category', 1, 1);
add_event('shop_admin_category_updated', 'fn_subscription_update_category', 1, 1);

// 관리자 상품 업데이트시 hook
add_event('shop_admin_itemformupdate', 'fn_shop_admin_itemformupdate', 1, 4);

// 관리자 분류 업데이트시 처리 함수
function fn_subscription_update_category($ca_id) {
    global $g5, $w;

    if (isset($_POST['ca_class_num'])) {
        $ca_class_num = (int) $_POST['ca_class_num'];
        $sql = " update {$g5['g5_shop_category_table']} set ca_class_num = '$ca_class_num' where ca_id = '$ca_id' ";
        
        sql_query($sql, false);
    }
}

// 관리자 상품 업데이트시 처리 함수
function fn_shop_admin_itemformupdate($it_id, $w, $old_item=array(), $old_item_options=array()) {
    global $g5, $config, $w, $ca_id, $member;

    if (isset($_POST['it_class_num'])) {
        $it_class_num = (int) $_POST['it_class_num'];
        $ca_fields = '';
        
        $sql = " update {$g5['g5_shop_item_table']} set it_class_num = '$it_class_num' where it_id = '$it_id' ";
        
        sql_query($sql, false);
        
        $ca_id = preg_replace('/[^0-9a-z]/i', '', $ca_id);
        
        // 분류적용
        if (isset($_POST['chk_ca_it_class_num']) && is_checked('chk_ca_it_class_num') && $ca_id) {
            
            sql_query(" update {$g5['g5_shop_item_table']} set it_class_num = '$it_class_num' where ca_id = '$ca_id' ", false);
        } else {
            
            $sql = " update {$g5['g5_shop_item_table']} set it_class_num = '$it_class_num' where it_id = '$it_id' ";
            sql_query($sql, false);
        }
        
        // 전체적용
        if (isset($_POST['chk_all_it_class_num'])) {
            sql_query(" update {$g5['g5_shop_item_table']} set it_class_num = '$it_class_num' ");
        }
    }
    
    // 상품 수정시 원래 정기결제에서 사용되는 상품이면 가격을 체크한다.
    if ($w === 'u' && $old_item) {
        // 상품 정보 가져오기
        $sql = "SELECT * FROM {$g5['g5_shop_item_table']} WHERE it_id = '{$it_id}'";
        $item = sql_fetch($sql);
        
        if ($item && isset($item['it_class_num']) && $item['it_class_num'] > 0) {
            
            // 가격변동이 있으면
            $old_price = (int) $old_item['it_price'];
            $new_price = (int) $item['it_price'];
            
            // POST로 넘어온 데이터
            $post_opts   = isset($_POST['opt_id']) ? (array) $_POST['opt_id'] : '';
            $post_opt_prices = isset($_POST['opt_price']) ? (array) $_POST['opt_price'] : '';
            
            $post_spls   = isset($_POST['spl_id']) ? (array) $_POST['spl_id'] : '';
            $post_spl_prices = isset($_POST['spl_price']) ? (array) $_POST['spl_price'] : '';
            
            $opt_changes = array();
            $spl_changes = array();
            
            // 상품선택옵션 변경 감지 및 처리
            if ($post_opts) {
                for ($i = 0; $i < count($post_opts); $i++) {
                    $io_id = trim($post_opts[$i]);
                    $new_opt_price = (int) $post_opt_prices[$i];

                    if (!isset($old_item_options[$io_id])) continue;

                    $old_opt_price = (int) $old_item_options[$io_id]['io_price'];
                    
                    if ($old_opt_price === $new_opt_price) continue; // 가격 변경 없음
                    
                    $old_item_options[$io_id]['change_price'] = $new_opt_price;
                    
                    // 상품선택옵션이 맞으면
                    if (isset($old_item_options[$io_id]['io_type']) && $old_item_options[$io_id]['io_type'] == 0) {
                        $opt_changes[] = $old_item_options[$io_id];
                    }
                }
            }
            
            // 상품추가옵션 변경 감지 및 처리
            if ($post_spls) {
                for ($i = 0; $i < count($post_spls); $i++) {
                    $io_id = trim($post_spls[$i]);
                    $new_spl_price = (int) $post_spl_prices[$i];

                    if (!isset($old_item_options[$io_id])) continue;

                    $old_spl_price = (int) $old_item_options[$io_id]['io_price'];
                    
                    if ($old_spl_price === $new_spl_price) continue; // 가격 변경 없음
                    
                    $old_item_options[$io_id]['change_price'] = $new_spl_price;
                    
                    // 상품추가옵션이 맞으면
                    if (isset($old_item_options[$io_id]['io_type']) && $old_item_options[$io_id]['io_type'] == 1) {
                        $spl_changes[] = $old_item_options[$io_id];
                    }
                }
            }
            
            // 판매가격이 변경 또는 상품선택옵션 가격 변경 또는 상품추가옵션 변경이 있다면
            if ($old_price !== $new_price || $opt_changes || $spl_changes) {
                
                // 이 상품을 주문한 주문번호, 고객 이메일 추출 (종료된 것은 제외, 활성화된 주문만)
                $sql = "
                    SELECT DISTINCT od.od_id, od.od_email, od.od_name
                    FROM {$g5['g5_subscription_order_table']} od
                    JOIN {$g5['g5_subscription_cart_table']} ct ON od.od_id = ct.od_id
                    WHERE ct.it_id = '{$it_id}' and od_enable_status = 1
                ";
                $result = sql_query($sql);
                
                $mails = array();
                $ods = array();
                
                $memo_msgs = array();
                $mail_msgs = array();
                
                // 판매가격이 변경되었으면
                if ($old_price !== $new_price) {
                    $memo_msgs[] = "[가격변경] {$item['it_name']} 상품 가격이 ".number_format($old_price) ."원 에서 ".number_format($new_price)."원 으로 변경됨";
                    $mail_msgs[] = "<li>기존 판매가격: <del>" . number_format($old_price) . "원</del></li>
                                <li>변경 후 판매가격: <strong>" . number_format($new_price) . "원</strong></li>";
                }
                
                // 상품선택옵션이 변경되었다면
                if ($opt_changes) {
                    foreach($opt_changes as $c_opt) {
                        
                        if (empty($c_opt)) {
                            continue;
                        }
                        
                        $memo_msgs[] = "[가격변경] {$item['it_name']} > {$c_opt['io_id']} 선택옵션의 가격이 ".number_format($c_opt['io_price']) ."원 에서 ".number_format($c_opt['change_price'])."원 으로 변경됨";
                        $mail_msgs[] = "<li>기존 {$c_opt['io_id']} 선택옵션가격: <del>" . number_format($c_opt['io_price']) . "원</del></li>
                                <li>변경 후 {$c_opt['io_id']} 선택옵션가격: <strong>" . number_format($c_opt['change_price']) . "원</strong></li>";
                    }
                }
                
                // 상품추가옵션이 변경되었다면
                if ($spl_changes) {
                    foreach($spl_changes as $c_opt) {
                        
                        if (empty($c_opt)) {
                            continue;
                        }
                        
                        $memo_msgs[] = "[가격변경] {$item['it_name']} > {$c_opt['io_id']} 추가옵션의 가격이 ".number_format($c_opt['io_price']) ."원 에서 ".number_format($c_opt['change_price'])."원 으로 변경됨";
                        $mail_msgs[] = "<li>기존 {$c_opt['io_id']} 추가옵션가격: <del>" . number_format($c_opt['io_price']) . "원</del></li>
                                <li>변경 후 {$c_opt['io_id']} 추가옵션가격: <strong>" . number_format($c_opt['change_price']) . "원</strong></li>";
                    }
                }
                
                while ($row = sql_fetch_array($result)) {
                    
                    $str_memo_msg = implode("\n", $memo_msgs);
                    
                    // 히스토리 내역에 저장
                    add_subscription_order_history($str_memo_msg, array(
                        'hs_type' => 'subscription_item_price_change',
                        'hs_category' => 'all',     // hs_category 가 all 이면 사용자와 관리자 둘다 보기 가능, admin 이면 관리자만 보기 가능
                        'od_id' => $row['od_id'],
                        'mb_id' => $member['mb_id']
                    ));
                    
                    // 메모에도 저장
                    $sql = " update {$g5['g5_subscription_order_table']}
                                set od_subscription_memo = concat(od_subscription_memo, \"\\n".$str_memo_msg." - ".G5_TIME_YMDHIS."\")
                                where od_id = '{$row['od_id']}' ";
                    
                    sql_query($sql);
                    
                    $mails[] = array('od_email'=> $row['od_email'], 'od_name'=> $row['od_name'], 'od_id'=> $row['od_id']);
                    $ods[] = $row['od_id'];
                }
                
                // 이메일 기준으로 정리
                $mail_datas = array();

                foreach ($mails as $order) {
                    $email = $order['od_email'];
                    $name = $order['od_name'];
                    $od_id = $order['od_id'];

                    if (!isset($mail_datas[$email])) {
                        $mail_datas[$email] = [
                            'od_name' => $name,
                            'od_ids' => array(),
                        ];
                    }

                    $mail_datas[$email]['od_ids'][] = $od_id;
                }
                
                run_event('subscription_item_price_change', $mail_datas, $ods, $old_price, $new_price, $opt_changes, $spl_changes, $memo_msgs, $mail_msgs, $it_id, $w, $old_item);
                
                // 정기구독에서 담긴 상품가격이 변동된 내용을 메일로 보낸다.
                if (defined('IS_MAIL_SUBSCRIPTION_ITEM_PRICECHANGED') && IS_MAIL_SUBSCRIPTION_ITEM_PRICECHANGED) {
                    
                    include_once(G5_LIB_PATH.'/mailer.lib.php');
                    
                    foreach ($mail_datas as $email => $data) {
                        $subject = "[가격변경안내] {$item['it_name']} 상품 가격이 변경되었습니다.";
                        
                        $content = "
                            <p>{$data['od_name']} 고객님,</p>
                            <p>고객님께서 주문하신 <strong>{$item['it_name']}</strong> 상품의 가격이 다음과 같이 변경되었습니다.</p>
                            ".implode('', $mail_msgs)."
                            <p>구매해 주셔서 감사합니다.<br> - " . $config['cf_title'] . " 쇼핑몰</p>
                        ";

                        // 메일 발송
                        mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $email, $subject, $content, 1);
                    }
                }
                
            }
        }
    }
}