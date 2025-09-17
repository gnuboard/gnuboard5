<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (!get_subs_option('su_pg_service')) {
    alert('관리자에서 정기결제 PG사를 설정해 주세요.', G5_SHOP_URL);
}

$temp_point = 0;
require_once G5_MSUBSCRIPTION_PATH . '/settle_' . get_subs_option('su_pg_service') . '.inc.php';
?>

<div id="sod_approval_frm">
    <?php
    ob_start();
    ?>

    <ul class="sod_list">
        <?php
        $tot_point = 0;
        $tot_sell_price = 0;

        $goods = $goods_it_id = "";
        $goods_count = -1;

        // $s_cart_id 로 현재 장바구니 자료 쿼리
        $sql = "SELECT a.ct_id, a.it_id, a.it_name, a.ct_price, a.ct_point, a.ct_qty, a.ct_status, a.ct_send_cost, a.it_sc_type, 
                    b.ca_id, b.ca_id2, b.ca_id3, b.it_notax 
                FROM {$g5['g5_subscription_cart_table']} a 
                LEFT JOIN {$g5['g5_shop_item_table']} b ON (a.it_id = b.it_id) 
                WHERE a.od_id = '" . $s_cart_id . "' 
                AND a.ct_select = '1' 
                GROUP BY a.it_id 
                ORDER BY a.ct_id";
        $result = sql_query($sql);

        $good_info = '';
        $it_send_cost = 0;
        $it_cp_count = 0;

        $comm_tax_mny = 0; // 과세금액
        $comm_vat_mny = 0; // 부가세
        $comm_free_mny = 0; // 면세금액
        $tot_tax_mny = 0;

        for ($i = 0; $row = sql_fetch_array($result); $i++) {
            // 합계금액 계산
            $sql = "SELECT SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) AS price, 
                        SUM(ct_point * ct_qty) AS point, 
                        SUM(ct_qty) AS qty 
                    FROM {$g5['g5_subscription_cart_table']} 
                    WHERE it_id = '" . sql_real_escape_string($row['it_id']) . "' 
                    AND od_id = '" . sql_real_escape_string($s_cart_id) . "'";
            $sum = sql_fetch($sql);

            if (!$goods) {
                //$goods = addslashes($row[it_name]);
                //$goods = get_text($row[it_name]);
                $goods = preg_replace("/\?|\'|\"|\||\,|\&|\;/", "", $row['it_name']);
                $goods_it_id = $row['it_id'];
            }
            $goods_count++;

            $a1 = '<strong>';
            $a2 = '</strong>';
            $image_width = 80;
            $image_height = 80;
            $image = get_it_image($row['it_id'], $image_width, $image_height);

            $it_name = $a1 . stripslashes($row['it_name']) . $a2;
            $it_options = subscription_print_item_options($row['it_id'], $s_cart_id);


            // 복합과세금액
            if ($default['de_tax_flag_use']) {
                if ($row['it_notax']) {
                    $comm_free_mny += $sum['price'];
                } else {
                    $tot_tax_mny += $sum['price'];
                }
            }

            $point      = $sum['point'];
            $sell_price = $sum['price'];

            $cp_button = '';
            // 쿠폰
            if ($is_member) {
                $cp_count = 0;

                $sql = " select cp_id
                            from {$g5['g5_shop_coupon_table']}
                            where mb_id IN ( '{$member['mb_id']}', '전체회원' )
                              and cp_start <= '" . G5_TIME_YMD . "'
                              and cp_end >= '" . G5_TIME_YMD . "'
                              and cp_minimum <= '$sell_price'
                              and (
                                    ( cp_method = '0' and cp_target = '{$row['it_id']}' )
                                    OR
                                    ( cp_method = '1' and ( cp_target IN ( '{$row['ca_id']}', '{$row['ca_id2']}', '{$row['ca_id3']}' ) ) )
                                  ) ";
                $res = sql_query($sql);

                for ($k = 0; $cp = sql_fetch_array($res); $k++) {
                    if (is_used_coupon($member['mb_id'], $cp['cp_id']))
                        continue;

                    $cp_count++;
                }

                if ($cp_count) {
                    $cp_button = '<div class="li_cp"><button type="button" class="cp_btn">쿠폰적용</button></div>';
                    $it_cp_count++;
                }
            }

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

                if ($sendcost == 0)
                    $ct_send_cost = '무료';
            }
        ?>

            <li class="sod_li">
                <input type="hidden" name="it_id[<?php echo $i; ?>]" value="<?php echo get_text($row['it_id']); ?>">
                <input type="hidden" name="it_name[<?php echo $i; ?>]" value="<?php echo get_text($row['it_name']); ?>">
                <input type="hidden" name="it_price[<?php echo $i; ?>]" value="<?php echo get_text($sell_price); ?>">
                <?php if ($default['de_tax_flag_use']) { ?>
                    <input type="hidden" name="it_notax[<?php echo $i; ?>]" value="<?php echo $row['it_notax']; ?>">
                <?php } ?>
                <input type="hidden" name="cp_id[<?php echo $i; ?>]" value="">
                <input type="hidden" name="cp_price[<?php echo $i; ?>]" value="0">
                <div class="li_name">
                    <?php echo $it_name; ?>
                </div>
                <div class="li_op_wr">
                    <span class="total_img"><?php echo $image; ?></span>
                    <div class="sod_opt"><?php echo $it_options; ?></div>
                    <div class="li_mod"><?php echo $cp_button; ?></div>
                </div>

                <div class="li_prqty">
                    <span class="prqty_price li_prqty_sp"><span>판매가 </span><?php echo number_format($row['ct_price']); ?></span>
                    <span class="prqty_qty li_prqty_sp"><span>수량 </span><?php echo number_format($sum['qty']); ?></span>
                    <span class="prqty_sc li_prqty_sp"><span>배송비 </span><?php echo $ct_send_cost; ?></span>
                    <span class="total_point li_prqty_sp"><span>적립포인트 </span><strong><?php echo number_format($sum['point']); ?></strong></span>

                </div>
                <div class="total_price total_span"><span>주문금액 </span><strong><?php echo number_format($sell_price); ?></strong></div>

            </li>

        <?php
            $tot_point      += $point;
            $tot_sell_price += $sell_price;
        } // for 끝

        if ($i == 0) {
            //echo '<li class="empty_li">장바구니에 담긴 상품이 없습니다.</li>';
            alert('장바구니가 비어 있습니다.', G5_SUBSCRIPTION_URL . '/cart.php');
        } else {
            // 배송비 계산
            $send_cost = get_subscription_sendcost($s_cart_id);
        }

        // 복합과세처리
        if ($default['de_tax_flag_use']) {
            $comm_tax_mny = round(($tot_tax_mny + $send_cost) / 1.1);
            $comm_vat_mny = ($tot_tax_mny + $send_cost) - $comm_tax_mny;
        }
        ?>
    </ul>

    <?php if ($goods_count) $goods .= ' 외 ' . $goods_count . '건'; ?>


    <!-- 주문상품 합계 시작 { -->
    <div class="sod_ta_wr">
        <dl id="m_sod_bsk_tot">
            <dt class="sod_bsk_sell">주문</dt>
            <dd class="sod_bsk_sell"><strong><?php echo number_format($tot_sell_price); ?> 원</strong></dd>
            <?php if ($it_cp_count > 0) { ?>
                <dt class="sod_bsk_coupon">쿠폰</dt>
                <dd class="sod_bsk_coupon"><strong id="ct_tot_coupon">0 원</strong></dd>
            <?php } ?>
            <dt class="sod_bsk_dvr">배송비</dt>
            <dd class="sod_bsk_dvr"><strong><?php echo number_format($send_cost); ?> 원</strong></dd>

            <dt class="sod_bsk_point">포인트</dt>
            <dd class="sod_bsk_point"><strong><?php echo number_format($tot_point); ?> 점</strong></dd>
            <dt class="sod_bsk_cnt">총계</dt>
            <dd class="sod_bsk_cnt">
                <?php $tot_price = $tot_sell_price + $send_cost; // 총계 = 주문상품금액합계 + 배송비 
                ?>
                <strong id="ct_tot_price"><?php echo number_format($tot_price); ?></strong> 원
            </dd>
        </dl>
    </div>

    <!-- } 주문상품 합계 끝 -->

    <?php
    $content = ob_get_contents();
    ob_end_clean();

    // 결제대행사별 코드 include (결제등록 필드)
    require_once(G5_MSUBSCRIPTION_PATH . '/' . get_subs_option('su_pg_service') . '/orderform.1.php');
    ?>
</div>

<div id="sod_frm" class="sod_frm_mobile">
    <form name="forderform" method="post" action="<?php echo get_text($order_action_url); ?>" autocomplete="off">
        <input type="hidden" name="od_price" value="<?php echo (int) $tot_sell_price; ?>">
        <input type="hidden" name="org_od_price" value="<?php echo (int) $tot_sell_price; ?>">
        <input type="hidden" name="od_send_cost" value="<?php echo (int) $send_cost; ?>">
        <input type="hidden" name="od_send_cost2" value="0">
        <input type="hidden" name="item_coupon" value="0">
        <input type="hidden" name="od_coupon" value="0">
        <input type="hidden" name="od_send_coupon" value="0">

        <?php

        echo $content;
        ?>

        <section id="sod_frm_orderer">
            <h2>주문하시는 분</h2>

            <div class="odf_list">
                <ul>
                    <li>
                        <label for="od_name">이름<strong class="sound_only"> 필수</strong></label>
                        <input type="text" name="od_name" value="<?php echo isset($member['mb_name']) ? get_text($member['mb_name']) : ''; ?>" id="od_name" required class="frm_input required" maxlength="20">
                    </li>

                    <?php if (!$is_member) { // 비회원이면 
                    ?>
                        <li>
                            <label for="od_pwd">비밀번호<strong class="sound_only"> 필수</strong></label>

                            <input type="password" name="od_pwd" id="od_pwd" required class="frm_input required" maxlength="20">
                            영,숫자 3~20자 (주문서 조회시 필요)

                        </li>
                    <?php } ?>

                    <li>
                        <label for="od_tel">전화번호<strong class="sound_only"> 필수</strong></label>
                        <input type="text" name="od_tel" value="<?php echo get_text($member['mb_tel']); ?>" id="od_tel" required class="frm_input required" maxlength="20">
                    </li>
                    <li>
                        <label for="od_hp">핸드폰</label>
                        <input type="text" name="od_hp" value="<?php echo get_text($member['mb_hp']); ?>" id="od_hp" class="frm_input" maxlength="20">
                    </li>
                    <li>
                        <strong>주소</strong>

                        <span class="add_num"><label for="od_zip" class="sound_only">우편번호<strong class="sound_only"> 필수</strong></label>
                            <input type="text" name="od_zip" value="<?php echo $member['mb_zip1'] . $member['mb_zip2']; ?>" id="od_zip" required class="frm_input required" size="5" maxlength="6">
                            <button type="button" class="btn_frmline btn_addsch" onclick="win_zip('forderform', 'od_zip', 'od_addr1', 'od_addr2', 'od_addr3', 'od_addr_jibeon');">주소검색</button></span>
                        <label for="od_addr1" class="sound_only">기본주소<strong class="sound_only"> 필수</strong></label>
                        <input type="text" name="od_addr1" value="<?php echo get_text($member['mb_addr1']) ?>" id="od_addr1" required class="frm_input frm_address required">
                        <label for="od_addr2" class="sound_only">상세주소</label>
                        <input type="text" name="od_addr2" value="<?php echo get_text($member['mb_addr2']) ?>" id="od_addr2" class="frm_input frm_address">
                        <label for="od_addr3" class="sound_only">참고항목</label>
                        <input type="text" name="od_addr3" value="<?php echo get_text($member['mb_addr3']) ?>" id="od_addr3" class="frm_input frm_address" readonly="readonly">
                        <input type="hidden" name="od_addr_jibeon" value="<?php echo get_text($member['mb_addr_jibeon']); ?>"><br>

                    </li>
                    <li>
                        <label for="od_email">E-mail<strong class="sound_only"> 필수</strong></label>
                        <input type="email" name="od_email" value="<?php echo $member['mb_email']; ?>" id="od_email" required class="frm_input required" maxlength="100">
                    </li>

                    <?php if ($default['de_hope_date_use']) { // 배송희망일 사용 
                    ?>
                        <li>
                            <label for="od_hope_date">희망배송일</label>
                            <!-- <select name="od_hope_date" id="od_hope_date">
                        <option value="">선택하십시오.</option>
                        <?php
                        for ($i = 0; $i < 7; $i++) {
                            $sdate = date("Y-m-d", time() + 86400 * ($default['de_hope_date_after'] + $i));
                            echo '<option value="' . $sdate . '">' . $sdate . ' (' . get_yoil($sdate) . ')</option>' . PHP_EOL;
                        }
                        ?>
                        </select> -->
                            <input type="text" name="od_hope_date" value="" id="od_hope_date" required class="frm_input required" size="11" maxlength="10" readonly> 이후로 배송 바랍니다.

                        </li>
                    <?php } ?>
                </ul>
            </div>
        </section>

        <section id="sod_frm_taker">
            <h2>받으시는 분</h2>

            <div class="odf_list">
                <ul>
                    <?php
                    $addr_list = '';
                    if ($is_member) {
                        // 배송지 이력
                        $sep = chr(30);

                        // 주문자와 동일
                        $addr_list .= '<input type="radio" name="ad_sel_addr" value="same" id="ad_sel_addr_same">' . PHP_EOL;
                        $addr_list .= '<label for="ad_sel_addr_same">주문자와 동일</label>' . PHP_EOL;

                        // 기본배송지
                        $sql = " select *
                                from {$g5['g5_shop_order_address_table']}
                                where mb_id = '{$member['mb_id']}'
                                  and ad_default = '1' ";
                        $row = sql_fetch($sql);
                        if (isset($row['ad_id']) && $row['ad_id']) {
                            $val1 = $row['ad_name'] . $sep . $row['ad_tel'] . $sep . $row['ad_hp'] . $sep . $row['ad_zip1'] . $sep . $row['ad_zip2'] . $sep . $row['ad_addr1'] . $sep . $row['ad_addr2'] . $sep . $row['ad_addr3'] . $sep . $row['ad_jibeon'] . $sep . $row['ad_subject'];
                            $addr_list .= '<br><input type="radio" name="ad_sel_addr" value="' . get_text($val1) . '" id="ad_sel_addr_def">' . PHP_EOL;
                            $addr_list .= '<label for="ad_sel_addr_def">기본배송지</label>' . PHP_EOL;
                        }

                        // 최근배송지
                        $sql = " select *
                                from {$g5['g5_shop_order_address_table']}
                                where mb_id = '{$member['mb_id']}'
                                  and ad_default = '0'
                                order by ad_id desc
                                limit 1 ";
                        $result = sql_query($sql);
                        for ($i = 0; $row = sql_fetch_array($result); $i++) {
                            $val1 = $row['ad_name'] . $sep . $row['ad_tel'] . $sep . $row['ad_hp'] . $sep . $row['ad_zip1'] . $sep . $row['ad_zip2'] . $sep . $row['ad_addr1'] . $sep . $row['ad_addr2'] . $sep . $row['ad_addr3'] . $sep . $row['ad_jibeon'] . $sep . $row['ad_subject'];
                            $val2 = '<label for="ad_sel_addr_' . ($i + 1) . '">최근배송지(' . ($row['ad_subject'] ? get_text($row['ad_subject']) : get_text($row['ad_name'])) . ')</label>';
                            $addr_list .= '<br><input type="radio" name="ad_sel_addr" value="' . get_text($val1) . '" id="ad_sel_addr_' . ($i + 1) . '"> ' . PHP_EOL . $val2 . PHP_EOL;
                        }

                        $addr_list .= '<br><input type="radio" name="ad_sel_addr" value="new" id="od_sel_addr_new">' . PHP_EOL;
                        $addr_list .= '<label for="od_sel_addr_new">신규배송지</label>' . PHP_EOL;

                        $addr_list .= '<a href="' . G5_SHOP_URL . '/orderaddress.php" id="order_address">배송지목록</a>';
                    } else {
                        // 주문자와 동일
                        $addr_list .= '<input type="checkbox" name="ad_sel_addr" value="same" id="ad_sel_addr_same">' . PHP_EOL;
                        $addr_list .= '<label for="ad_sel_addr_same">주문자와 동일</label>' . PHP_EOL;
                    }
                    ?>
                    <li class="dlv_slt">
                        <strong>배송지선택</strong>
                        <div><?php echo $addr_list; ?></div>
                    </li>
                    <?php if ($is_member) { ?>
                        <li>
                            <label for="ad_subject">배송지명</label>
                            <input type="text" name="ad_subject" id="ad_subject" class="frm_input" maxlength="20">
                            <input type="checkbox" name="ad_default" id="ad_default" value="1">
                            <label for="ad_default" class="ad_default">기본배송지로 설정</label>

                        </li>
                    <?php
                    }
                    ?>
                    <li>
                        <label for="od_b_name">이름<strong class="sound_only"> 필수</strong></label>
                        <input type="text" name="od_b_name" id="od_b_name" required class="frm_input required" maxlength="20">
                    </li>
                    <li>
                        <label for="od_b_tel">전화번호<strong class="sound_only"> 필수</strong></label>
                        <input type="text" name="od_b_tel" id="od_b_tel" required class="frm_input required" maxlength="20">
                    </li>
                    <li>
                        <label for="od_b_hp">핸드폰</label>
                        <input type="text" name="od_b_hp" id="od_b_hp" class="frm_input" maxlength="20">
                    </li>
                    <li>
                        <strong>주소</strong>
                        <label for="od_b_zip" class="sound_only">우편번호<strong class="sound_only"> 필수</strong></label>
                        <span class="add_num"><input type="text" name="od_b_zip" id="od_b_zip" required class="frm_input required" size="5" maxlength="6">
                            <button type="button" class="btn_frmline  btn_addsch" onclick="win_zip('forderform', 'od_b_zip', 'od_b_addr1', 'od_b_addr2', 'od_b_addr3', 'od_b_addr_jibeon');">주소 검색</button></span>
                        <label for="od_b_addr1" class="sound_only">기본주소<strong class="sound_only"> 필수</strong></label>
                        <input type="text" name="od_b_addr1" id="od_b_addr1" required class="frm_input frm_address required">
                        <label for="od_b_addr2" class="sound_only">상세주소</label>
                        <input type="text" name="od_b_addr2" id="od_b_addr2" class="frm_input frm_address">
                        <label for="od_b_addr3" class="sound_only">참고항목</label>
                        <input type="text" name="od_b_addr3" id="od_b_addr3" class="frm_input frm_address" readonly="readonly">
                        <input type="hidden" name="od_b_addr_jibeon" value="">
                    </li>
                    <li>
                        <label for="od_memo">전하실 말씀</label>
                        <textarea name="od_memo" id="od_memo"></textarea>
                    </li>
                </ul>
            </div>
        </section>

        <?php run_event('subscription_add_form_html'); ?>

        <section id="sod_frm_subscription_input" class="subscription_mobile">
            <h2>정기구독정보 입력</h2>

            <?php // 정기결제 공통폼 불러오기
            include_once(G5_SUBSCRIPTION_PATH . '/subscription_order_modal.php');
            ?>
        </section>


        <?php
        $oc_cnt = $sc_cnt = 0;
        if ($is_member) {
            // 주문쿠폰
            $sql = " select cp_id
                    from {$g5['g5_shop_coupon_table']}
                    where mb_id IN ( '{$member['mb_id']}', '전체회원' )
                      and cp_method = '2'
                      and cp_start <= '" . G5_TIME_YMD . "'
                      and cp_end >= '" . G5_TIME_YMD . "'
                      and cp_minimum <= '$tot_sell_price' ";
            $res = sql_query($sql);

            for ($k = 0; $cp = sql_fetch_array($res); $k++) {
                if (is_used_coupon($member['mb_id'], $cp['cp_id']))
                    continue;

                $oc_cnt++;
            }

            if ($send_cost > 0) {
                // 배송비쿠폰
                $sql = " select cp_id
                        from {$g5['g5_shop_coupon_table']}
                        where mb_id IN ( '{$member['mb_id']}', '전체회원' )
                          and cp_method = '3'
                          and cp_start <= '" . G5_TIME_YMD . "'
                          and cp_end >= '" . G5_TIME_YMD . "'
                          and cp_minimum <= '$tot_sell_price' ";
                $res = sql_query($sql);

                for ($k = 0; $cp = sql_fetch_array($res); $k++) {
                    if (is_used_coupon($member['mb_id'], $cp['cp_id']))
                        continue;

                    $sc_cnt++;
                }
            }
        }
        ?>

        <section id="sod_frm_pay">
            <h2>결제정보 입력</h2>


            <div class="odf_tbl">
                <table>
                    <tbody>
                        <?php if ($oc_cnt > 0) { ?>
                            <tr>
                                <th scope="row">주문할인쿠폰</th>
                                <td>
                                    <input type="hidden" name="od_cp_id" value="">
                                    <button type="button" id="od_coupon_btn" class="cp_btn1">쿠폰적용</button>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">주문할인금액</th>
                                <td><span id="od_cp_price">0</span>원</td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <th>총 주문금액</th>
                            <td><span id="od_tot_price"><?php echo number_format($tot_price); ?></span>원</td>
                        </tr>
                        <tr>
                            <th>추가배송비</th>
                            <td><span id="od_send_cost2">0</span>원 (지역에 따라 추가되는 도선료 등의 배송비입니다.)</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="m_sod_frm_paysel">
                <ul>
                    <?php

                    $mcards = array();

                    $sql = "SELECT ci_id, MAX(ci_id) AS max_id, od_id, od_card_name, card_mask_number, od_test 
                        FROM {$g5['g5_subscription_mb_cardinfo_table']} 
                        WHERE card_billkey != '' 
                        AND mb_id = '" . $member['mb_id'] . "' 
                        AND pg_service = '" . get_subs_option('su_pg_service') . "' 
                        AND pg_apikey = '" . get_subscription_pg_apikey() . "' 
                        GROUP BY od_card_name, card_mask_number 
                        ORDER BY max_id DESC 
                        LIMIT 30";

                    $result = sql_query($sql);

                    for ($i = 0; $row = sql_fetch_array($result); $i++) {
                        $mcards[] = $row;
                    }

                    if ($mcards) {
                        echo '<input type="hidden" id="od_select_card_number" name="od_select_card_number" value="" >';

                        $j = 0;

                        foreach ($mcards as $card) {
                            echo '<li>';
                            echo '<input type="radio" id="od_subscription_card_' . $i . '" class="od_subscription_ids" name="od_settle_case" value="' . $card['max_id'] . '"> <label for="od_subscription_card_' . $i . '" class="lb_icon card_icon subscription_card"><span>' . subscription_pg_cardname($card['od_card_name']) . ' ' . $card['card_mask_number'] . '</span></label>';
                            echo '</li>' . PHP_EOL;
                            $j++;
                        }
                    }

                    ?>
                    <li><input type="radio" id="od_settle_card" name="od_settle_case" value="신용카드" '.$checked.'> <label for="od_settle_card" class="lb_icon card_icon">새 신용카드 등록</label></li>
                </ul>
            </div>
        </section>

        <?php
        // 결제대행사별 코드 include (결제대행사 정보 필드 및 주문버튼)
        require_once(G5_MSUBSCRIPTION_PATH . '/' . get_subs_option('su_pg_service') . '/orderform.2.php');

        ?>

        <div id="show_progress" style="display:none;">
            <img src="<?php echo G5_MOBILE_URL; ?>/shop/img/loading.gif" alt="">
            <span>주문완료 중입니다. 잠시만 기다려 주십시오.</span>
        </div>

    </form>

</div>

<?php
require_once(G5_MSUBSCRIPTION_PATH . '/' . get_subs_option('su_pg_service') . '/orderform.4.php');
?>
<script>
    var zipcode = "";

    jQuery(function($) {
        var $cp_btn_el;
        var $cp_row_el;

        $(".cp_btn").click(function() {
            $cp_btn_el = $(this);
            $cp_row_el = $(this).closest("li");
            $("#cp_frm").remove();
            var it_id = $cp_btn_el.closest("li").find("input[name^=it_id]").val();

            $.post(
                "./orderitemcoupon.php", {
                    it_id: it_id,
                    sw_direct: "<?php echo $sw_direct; ?>"
                },
                function(data) {
                    $cp_btn_el.after(data);
                }
            );
        });

        $(document).on("click", ".cp_apply", function() {
            var $el = $(this).closest("tr");
            var cp_id = $el.find("input[name='f_cp_id[]']").val();
            var price = $el.find("input[name='f_cp_prc[]']").val();
            var subj = $el.find("input[name='f_cp_subj[]']").val();
            var sell_price;

            if (parseInt(price) == 0) {
                if (!confirm(subj + "쿠폰의 할인 금액은 " + price + "원입니다.\n쿠폰을 적용하시겠습니까?")) {
                    return false;
                }
            }

            // 이미 사용한 쿠폰이 있는지
            var cp_dup = false;
            var cp_dup_idx;
            var $cp_dup_el;
            $("input[name^=cp_id]").each(function(index) {
                var id = $(this).val();

                if (id == cp_id) {
                    cp_dup_idx = index;
                    cp_dup = true;
                    $cp_dup_el = $(this).closest("li");;

                    return false;
                }
            });

            if (cp_dup) {
                var it_name = $("input[name='it_name[" + cp_dup_idx + "]']").val();
                if (!confirm(subj + "쿠폰은 " + it_name + "에 사용되었습니다.\n" + it_name + "의 쿠폰을 취소한 후 적용하시겠습니까?")) {
                    return false;
                } else {
                    coupon_cancel($cp_dup_el);
                    $("#cp_frm").remove();
                    $cp_dup_el.find(".cp_btn").text("쿠폰적용").removeClass("cp_mod").focus();
                    $cp_dup_el.find(".cp_cancel").remove();
                }
            }

            var $s_el = $cp_row_el.find(".total_price strong");;
            sell_price = parseInt($cp_row_el.find("input[name^=it_price]").val());
            sell_price = sell_price - parseInt(price);
            if (sell_price < 0) {
                alert("쿠폰할인금액이 상품 주문금액보다 크므로 쿠폰을 적용할 수 없습니다.");
                return false;
            }
            $s_el.text(number_format(String(sell_price)));
            $cp_row_el.find("input[name^=cp_id]").val(cp_id);
            $cp_row_el.find("input[name^=cp_price]").val(price);

            calculate_total_price();
            $("#cp_frm").remove();
            $cp_btn_el.text("변경").addClass("cp_mod").focus();
            if (!$cp_row_el.find(".cp_cancel").length)
                $cp_btn_el.after("<button type=\"button\" class=\"cp_cancel\">취소</button>");
        });

        $(document).on("click", "#cp_close", function() {
            $("#cp_frm").remove();
            $cp_btn_el.focus();
        });

        $(document).on("click", ".cp_cancel", function() {
            coupon_cancel($(this).closest("li"));
            calculate_total_price();
            $("#cp_frm").remove();
            $(this).closest("li").find(".cp_btn").text("쿠폰적용").removeClass("cp_mod").focus();
            $(this).remove();
        });

        $("#od_coupon_btn").click(function() {
            if ($("#od_coupon_frm").parent(".od_coupon_wrap").length) {
                $("#od_coupon_frm").parent(".od_coupon_wrap").remove();
            }
            $("#od_coupon_frm").remove();
            var $this = $(this);
            var price = parseInt($("input[name=org_od_price]").val()) - parseInt($("input[name=item_coupon]").val());
            if (price <= 0) {
                alert('상품금액이 0원이므로 쿠폰을 사용할 수 없습니다.');
                return false;
            }
            $.post(
                "./ordercoupon.php", {
                    price: price
                },
                function(data) {
                    $this.after(data);
                }
            );
        });

        $(document).on("click", ".od_cp_apply", function() {
            var $el = $(this).closest("tr");
            var cp_id = $el.find("input[name='o_cp_id[]']").val();
            var price = parseInt($el.find("input[name='o_cp_prc[]']").val());
            var subj = $el.find("input[name='o_cp_subj[]']").val();
            var send_cost = $("input[name=od_send_cost]").val();
            var item_coupon = parseInt($("input[name=item_coupon]").val());
            var od_price = parseInt($("input[name=org_od_price]").val()) - item_coupon;

            if (price == 0) {
                if (!confirm(subj + "쿠폰의 할인 금액은 " + price + "원입니다.\n쿠폰을 적용하시겠습니까?")) {
                    return false;
                }
            }

            if (od_price - price <= 0) {
                alert("쿠폰할인금액이 주문금액보다 크므로 쿠폰을 적용할 수 없습니다.");
                return false;
            }

            $("input[name=sc_cp_id]").val("");
            $("#sc_coupon_btn").text("쿠폰적용");
            $("#sc_coupon_cancel").remove();

            $("input[name=od_price]").val(od_price - price);
            $("input[name=od_cp_id]").val(cp_id);
            $("input[name=od_coupon]").val(price);
            $("input[name=od_send_coupon]").val(0);
            $("#od_cp_price").text(number_format(String(price)));
            $("#sc_cp_price").text(0);
            calculate_order_price();
            if ($("#od_coupon_frm").parent(".od_coupon_wrap").length) {
                $("#od_coupon_frm").parent(".od_coupon_wrap").remove();
            }
            $("#od_coupon_frm").remove();
            $("#od_coupon_btn").text("변경").focus();
            if (!$("#od_coupon_cancel").length)
                $("#od_coupon_btn").after("<button type=\"button\" id=\"od_coupon_cancel\" class=\"cp_cancel cp_cancel1\">취소</button>");
        });

        $(document).on("click", "#od_coupon_close", function() {
            if ($("#od_coupon_frm").parent(".od_coupon_wrap").length) {
                $("#od_coupon_frm").parent(".od_coupon_wrap").remove();
            }
            $("#od_coupon_frm").remove();
            $("#od_coupon_btn").focus();
        });

        $(document).on("click", "#od_coupon_cancel", function() {
            var org_price = $("input[name=org_od_price]").val();
            var item_coupon = parseInt($("input[name=item_coupon]").val());
            $("input[name=od_price]").val(org_price - item_coupon);
            $("input[name=sc_cp_id]").val("");
            $("input[name=od_coupon]").val(0);
            $("input[name=od_send_coupon]").val(0);
            $("#od_cp_price").text(0);
            $("#sc_cp_price").text(0);
            calculate_order_price();
            if ($("#od_coupon_frm").parent(".od_coupon_wrap").length) {
                $("#od_coupon_frm").parent(".od_coupon_wrap").remove();
            }
            $("#od_coupon_frm").remove();
            $("#od_coupon_btn").text("쿠폰적용").focus();
            $(this).remove();
            $("#sc_coupon_btn").text("쿠폰적용");
            $("#sc_coupon_cancel").remove();
        });

        $("#sc_coupon_btn").click(function() {
            $("#sc_coupon_frm").remove();
            var $this = $(this);
            var price = parseInt($("input[name=od_price]").val());
            var send_cost = parseInt($("input[name=od_send_cost]").val());
            $.post(
                "./ordersendcostcoupon.php", {
                    price: price,
                    send_cost: send_cost
                },
                function(data) {
                    $this.after(data);
                }
            );
        });

        $(document).on("click", ".sc_cp_apply", function() {
            var $el = $(this).closest("tr");
            var cp_id = $el.find("input[name='s_cp_id[]']").val();
            var price = parseInt($el.find("input[name='s_cp_prc[]']").val());
            var subj = $el.find("input[name='s_cp_subj[]']").val();
            var send_cost = parseInt($("input[name=od_send_cost]").val());

            if (parseInt(price) == 0) {
                if (!confirm(subj + "쿠폰의 할인 금액은 " + price + "원입니다.\n쿠폰을 적용하시겠습니까?")) {
                    return false;
                }
            }

            $("input[name=sc_cp_id]").val(cp_id);
            $("input[name=od_send_coupon]").val(price);
            $("#sc_cp_price").text(number_format(String(price)));
            calculate_order_price();
            $("#sc_coupon_frm").remove();
            $("#sc_coupon_btn").text("변경").focus();
            if (!$("#sc_coupon_cancel").length)
                $("#sc_coupon_btn").after("<button type=\"button\" id=\"sc_coupon_cancel\" class=\"cp_cancel1\">취소</button>");
        });

        $(document).on("click", "#sc_coupon_close", function() {
            $("#sc_coupon_frm").remove();
            $("#sc_coupon_btn").focus();
        });

        $(document).on("click", "#sc_coupon_cancel", function() {
            $("input[name=od_send_coupon]").val(0);
            $("#sc_cp_price").text(0);
            calculate_order_price();
            $("#sc_coupon_frm").remove();
            $("#sc_coupon_btn").text("쿠폰적용").focus();
            $(this).remove();
        });

        $("#od_b_addr2").focus(function() {
            var zip = $("#od_b_zip").val().replace(/[^0-9]/g, "");
            if (zip == "")
                return false;

            var code = String(zip);

            if (zipcode == code)
                return false;

            zipcode = code;
            calculate_sendcost(code);
        });

        $("#od_settle_bank").on("click", function() {
            $("[name=od_deposit_name]").val($("[name=od_name]").val());
            $("#settle_bank").show();
            $("#show_req_btn").css("display", "none");
            $("#show_pay_btn").css("display", "inline");
        });

        $("#od_settle_iche,#od_settle_card,#od_settle_vbank,#od_settle_hp,#od_settle_easy_pay,#od_settle_kakaopay,#od_settle_samsungpay,#od_settle_nhnkcp_payco,#od_settle_nhnkcp_naverpay,#od_settle_nhnkcp_kakaopay,#od_settle_inicislpay,#od_settle_inicis_kakaopay").bind("click", function() {
            $("#settle_bank").hide();
            $("#show_req_btn").css("display", "inline");
            $("#show_pay_btn").css("display", "none");
        });

        // 배송지선택
        $("input[name=ad_sel_addr]").on("click", function() {
            var addr = $(this).val().split(String.fromCharCode(30));

            if (addr[0] == "same") {
                gumae2baesong();
            } else {
                if (addr[0] == "new") {
                    for (i = 0; i < 10; i++) {
                        addr[i] = "";
                    }
                }

                var f = document.forderform;
                f.od_b_name.value = addr[0];
                f.od_b_tel.value = addr[1];
                f.od_b_hp.value = addr[2];
                f.od_b_zip.value = addr[3] + addr[4];
                f.od_b_addr1.value = addr[5];
                f.od_b_addr2.value = addr[6];
                f.od_b_addr3.value = addr[7];
                f.od_b_addr_jibeon.value = addr[8];
                f.ad_subject.value = addr[9];

                var zip1 = addr[3].replace(/[^0-9]/g, "");
                var zip2 = addr[4].replace(/[^0-9]/g, "");

                var code = String(zip1) + String(zip2);

                if (zipcode != code) {
                    calculate_sendcost(code);
                }
            }
        });

        // 배송지목록
        $("#order_address").on("click", function() {
            var url = this.href;
            window.open(url, "win_address", "left=100,top=100,width=650,height=500,scrollbars=1");
            return false;
        });

        // 결제수단 선택 (기존의 카드를 선택시)
        $(document).on("click", ".subscription_card", function() {
            var od_id_number = $(this).prev("input[name='od_settle_case']").val();

            if (od_id_number) {
                $("#od_select_card_number").val(od_id_number);
            }
        });
    });

    function coupon_cancel($el) {
        var $dup_sell_el = $el.find(".total_price strong");
        var $dup_price_el = $el.find("input[name^=cp_price]");
        var org_sell_price = $el.find("input[name^=it_price]").val();

        $dup_sell_el.text(number_format(String(org_sell_price)));
        $dup_price_el.val(0);
        $el.find("input[name^=cp_id]").val("");
    }

    function calculate_total_price() {
        var $it_prc = $("input[name^=it_price]");
        var $cp_prc = $("input[name^=cp_price]");
        var tot_sell_price = sell_price = tot_cp_price = 0;
        var it_price, cp_price, it_notax;
        var tot_mny = comm_tax_mny = comm_vat_mny = comm_free_mny = tax_mny = vat_mny = 0;
        var send_cost = parseInt($("input[name=od_send_cost]").val());

        $it_prc.each(function(index) {
            it_price = parseInt($(this).val());
            cp_price = parseInt($cp_prc.eq(index).val());
            sell_price += it_price;
            tot_cp_price += cp_price;
        });

        tot_sell_price = sell_price - tot_cp_price + send_cost;

        $("#ct_tot_coupon").text(number_format(String(tot_cp_price)) + " 원");
        $("#ct_tot_price").text(number_format(String(tot_sell_price)));

        $("input[name=good_mny]").val(tot_sell_price);
        $("input[name=od_price]").val(sell_price - tot_cp_price);
        $("input[name=item_coupon]").val(tot_cp_price);
        $("input[name=od_coupon]").val(0);
        $("input[name=od_send_coupon]").val(0);
        <?php if ($oc_cnt > 0) { ?>
            $("input[name=od_cp_id]").val("");
            $("#od_cp_price").text(0);
            if ($("#od_coupon_cancel").length) {
                $("#od_coupon_btn").text("쿠폰적용");
                $("#od_coupon_cancel").remove();
            }
        <?php } ?>
        <?php if ($sc_cnt > 0) { ?>
            $("input[name=sc_cp_id]").val("");
            $("#sc_cp_price").text(0);
            if ($("#sc_coupon_cancel").length) {
                $("#sc_coupon_btn").text("쿠폰적용");
                $("#sc_coupon_cancel").remove();
            }
        <?php } ?>
        $("input[name=od_temp_point]").val(0);
        <?php if ($temp_point > 0 && $is_member) { ?>
            calculate_temp_point();
        <?php } ?>
        calculate_order_price();
    }

    function calculate_order_price() {
        var sell_price = parseInt($("input[name=od_price]").val());
        var send_cost = parseInt($("input[name=od_send_cost]").val());
        var send_cost2 = parseInt($("input[name=od_send_cost2]").val());
        var send_coupon = parseInt($("input[name=od_send_coupon]").val());
        var tot_price = sell_price + send_cost + send_cost2 - send_coupon;

        $("form[name=pay_form] input[name=good_mny]").val(tot_price);
        $("#od_tot_price").text(number_format(String(tot_price)));
        <?php if ($temp_point > 0 && $is_member) { ?>
            calculate_temp_point();
        <?php } ?>
    }

    function calculate_temp_point() {
        var sell_price = parseInt($("input[name=od_price]").val());
        var mb_point = parseInt(<?php echo $member['mb_point']; ?>);
        var max_point = parseInt(<?php echo $default['de_settle_max_point']; ?>);
        var point_unit = parseInt(<?php echo $default['de_settle_point_unit']; ?>);
        var temp_point = max_point;

        if (temp_point > sell_price)
            temp_point = sell_price;

        if (temp_point > mb_point)
            temp_point = mb_point;

        temp_point = parseInt(temp_point / point_unit) * point_unit;

        $("#use_max_point").text(number_format(String(temp_point)) + "점");
        $("input[name=max_temp_point]").val(temp_point);
    }

    function calculate_sendcost(code) {
        $.post(
            "./ordersendcost.php", {
                zipcode: code
            },
            function(data) {
                $("input[name=od_send_cost2]").val(data);
                $("#od_send_cost2").text(number_format(String(data)));

                zipcode = code;

                calculate_order_price();
            }
        );
    }

    function calculate_tax() {
        var $it_prc = $("input[name^=it_price]");
        var $cp_prc = $("input[name^=cp_price]");
        var sell_price = tot_cp_price = 0;
        var it_price, cp_price, it_notax;
        var tot_mny = comm_free_mny = tax_mny = vat_mny = 0;
        var send_cost = parseInt($("input[name=od_send_cost]").val());
        var send_cost2 = parseInt($("input[name=od_send_cost2]").val());
        var od_coupon = parseInt($("input[name=od_coupon]").val());
        var send_coupon = parseInt($("input[name=od_send_coupon]").val());
        var temp_point = 0;

        $it_prc.each(function(index) {
            it_price = parseInt($(this).val());
            cp_price = parseInt($cp_prc.eq(index).val());
            sell_price += it_price;
            tot_cp_price += cp_price;
            it_notax = $("input[name^=it_notax]").eq(index).val();
            if (it_notax == "1") {
                comm_free_mny += (it_price - cp_price);
            } else {
                tot_mny += (it_price - cp_price);
            }
        });

        if ($("input[name=od_temp_point]").length)
            temp_point = parseInt($("input[name=od_temp_point]").val()) || 0;

        tot_mny += (send_cost + send_cost2 - od_coupon - send_coupon - temp_point);
        if (tot_mny < 0) {
            comm_free_mny = comm_free_mny + tot_mny;
            tot_mny = 0;
        }

        tax_mny = Math.round(tot_mny / 1.1);
        vat_mny = tot_mny - tax_mny;
        $("input[name=comm_tax_mny]").val(tax_mny);
        $("input[name=comm_vat_mny]").val(vat_mny);
        $("input[name=comm_free_mny]").val(comm_free_mny);
    }

    /* 결제방법에 따른 처리 후 결제등록요청 실행 */
    var settle_method = "";
    var temp_point = 0;

    function pay_approval() {
        // 정기결제 입력정보 체크
        // 재고체크
        var stock_msg = subscription_order_stock_check();
        if (stock_msg != "") {
            alert(stock_msg);
            return false;
        }

        var f = document.pay_form;
        var pf = document.forderform;

        // 필드체크
        if (!orderfield_check(pf))
            return false;

        // 금액체크
        if (!payment_check(pf))
            return false;

        // 정기결제 체크
        if (!subscription_check(pf))
            return false;

        // pg 결제 금액에서 포인트 금액 차감
        var od_price = parseInt(pf.od_price.value);
        var send_cost = parseInt(pf.od_send_cost.value);
        var send_cost2 = parseInt(pf.od_send_cost2.value);
        var send_coupon = parseInt(pf.od_send_coupon.value);
        var tot_price = od_price + send_cost + send_cost2 - send_coupon - temp_point;
        f.good_mny.value = tot_price;

        var form_order_method = '';
        var is_subscription_card_checked = $(".od_subscription_ids").is(':checked');

        if (jQuery(pf).triggerHandler("mobile_subscriptionform_sumbit_" + form_order_method) !== false) {
            <?php if (get_subs_option('su_pg_service') == 'kcp') { ?>
                f.buyr_name.value = pf.od_name.value;
                f.buyr_mail.value = pf.od_email.value;
                f.buyr_tel1.value = pf.od_tel.value;
                f.buyr_tel2.value = pf.od_hp.value;

                f.settle_method.value = settle_method;

                if (typeof f.ActionResult !== "undefined") f.ActionResult.value = "";
                if (typeof f.pay_method !== "undefined") f.pay_method.value = "";

            <?php } else if (get_subs_option('su_pg_service') == 'tosspayments') { ?>
                switch (settle_method) {

                    case "신용카드":
                        f.gopaymethod.value = "Card";
                        break;
                    default:
                        f.gopaymethod.value = is_subscription_card_checked ? "exist_card" : "오류";

                }
            <?php } else if (get_subs_option('su_pg_service') == 'inicis') { ?>
                
                switch (settle_method) {

                    case "신용카드":
                        f.gopaymethod.value = "Card";
                        break;
                    default:
                        f.gopaymethod.value = is_subscription_card_checked ? "exist_card" : "오류";

                }
                
                <?php if (get_subs_option('su_tax_flag_use')) { ?>
                    f.P_TAX.value = pf.comm_vat_mny.value;
                    f.P_TAXFREE = pf.comm_free_mny.value;
                <?php } ?>
                
            <?php } else if (get_subs_option('su_pg_service') == 'nicepay') { ?>
                switch (settle_method) {

                    case "신용카드":
                        f.PayMethod.value = "CARD";
                        break;
                    default:
                        f.PayMethod.value = is_subscription_card_checked ? "exist_card" : "오류";
                        break;

                }
            <?php } ?>

            // 주문 정보 임시저장
            var order_data = $(pf).serialize();
            var save_result = "";
            $.ajax({
                type: "POST",
                data: order_data,
                url: g5_url + "/subscription/ajax.orderdatasave.php",
                cache: false,
                async: false,
                success: function(data) {
                    save_result = data;
                }
            });

            if (save_result) {
                alert(save_result);
                return false;
            }

            <?php if (get_subs_option('su_pg_service') == 'kcp') { ?>
                
                if (is_subscription_card_checked) {
                    pf.submit();
                    return false;
                }
                
                f.submit();
                
                return false;
            <?php } ?>
                
            <?php if (get_subs_option('su_pg_service') == 'tosspayments') { ?>

                if (f.gopaymethod.value == "exist_card") {
                    pf.submit();
                    return false;
                }

                requestBillingAuth({
                    od_id: "<?php echo $od_id; ?>",
                    price: tot_price,
                    customerEmail: pf.od_name.value,
                    customerName: pf.od_email.value
                });

                return false;

            <?php } ?>
            
            <?php if (get_subs_option('su_pg_service') == 'inicis') { ?>
                
                if (f.gopaymethod.value == "exist_card") {
                    pf.submit();
                    return false;
                }
                
                f.action = "https://inilite.inicis.com/inibill/inibill_card.jsp";

                f.target = "_self";
                
                f.submit();
                
                return false;
            <?php } ?>
                
            <?php if (get_subs_option('su_pg_service') == 'nicepay') { ?>
                if (f.PayMethod.value == "무통장" || f.PayMethod.value == "exist_card") {
                    pf.submit();
                    return false;
                }

                // 새 신용카드 등록인 경우
                nicepay_modal_open();
                return false;
            <?php } ?>

            pf.submit();
        }

        return false;
    }

    function subscription_check(f) {
        if (!jQuery("#od_hope_date").val() && $.fn.datepicker) {
            jQuery("#od_hope_date").val($.datepicker.formatDate('yy-mm-dd', $("#od_hope_date_print").datepicker("getDate")));
        }

        <?php if (get_subs_option('su_chk_user_delivery')) {    // 배송주기를 사용자가 입력이 가능한경우 
        ?>
            var od_subscription_select_val = jQuery("#od_subscription_select_data :selected").val() || jQuery("input[name='od_subscription_select_data']:checked").val() || jQuery("input[name='od_subscription_select_data']").val();
        <?php } else { ?>
            var od_subscription_select_val = jQuery("#od_subscription_select_data :selected").val() || jQuery("input[name='od_subscription_select_data']:checked").val();

        <?php } ?>

        if (!od_subscription_select_val) {
            alert("<?php echo subscription_item_delivery_title(); ?>를 선택해주세요");
            jQuery("#od_subscription_select_data").focus();

            return false;
        }

        var od_subscription_select_number_val = jQuery("input[name='od_subscription_select_number']:selected").val() || jQuery("select[name='od_subscription_select_number']").val() || jQuery("input[name='od_subscription_select_number']:checked").val();

        if (!od_subscription_select_number_val) {
            alert("<?php echo subscription_item_select_title(); ?>를 선택해주세요");

            return false;
        }

        if (!settle_method) {
            alert("결제수단을 선택하십시오.");
            return false;
        }

        var od_price = parseInt(f.od_price.value);
        var send_cost = parseInt(f.od_send_cost.value);
        var send_cost2 = parseInt(f.od_send_cost2.value);
        var send_coupon = parseInt(f.od_send_coupon.value);

        var max_point = 0;
        if (typeof(f.max_temp_point) != "undefined")
            max_point = parseInt(f.max_temp_point.value);

        var temp_point = 0;

        var tot_price = od_price + send_cost + send_cost2 - send_coupon - temp_point;

        var is_subscription_card_checked = $(".od_subscription_ids").is(':checked');

        var is_settle_card = $("#od_settle_card").is(':checked') || is_subscription_card_checked;

        if (is_settle_card) {
            if (tot_price < 1000) {
                alert("신용카드는 1000원 이상 결제가 가능합니다.");
                return false;
            }
        }

        return true;
    }

    function forderform_check(f) {
        // 무통장만 여기에처 처리한다.
        // 재고체크
        var stock_msg = subscription_order_stock_check();
        if (stock_msg != "") {
            alert(stock_msg);
            return false;
        }

        var f = f || document.forderform;

        // 필드체크
        if (!orderfield_check(f))
            return false;

        // 금액체크
        if (!payment_check(f))
            return false;

        if (!jQuery("#od_hope_date").val()) {
            jQuery("#od_hope_date").val($.datepicker.formatDate('yy-mm-dd', $("#od_hope_date_print").datepicker("getDate")));
        }

        <?php if (get_subs_option('su_chk_user_delivery')) {    // 배송주기를 사용자가 입력이 가능한경우 
        ?>
            var od_subscription_select_val = jQuery("#od_subscription_select_data :selected").val() || jQuery("input[name='od_subscription_select_data']:checked").val() || jQuery("input[name='od_subscription_select_data']").val();
        <?php } else { ?>
            var od_subscription_select_val = jQuery("#od_subscription_select_data :selected").val() || jQuery("input[name='od_subscription_select_data']:checked").val();

        <?php } ?>

        if (!od_subscription_select_val) {
            alert("<?php echo subscription_item_delivery_title(); ?>를 선택해주세요");
            jQuery("#od_subscription_select_data").focus();

            return false;
        }

        var od_subscription_select_number_val = jQuery("input[name='od_subscription_select_number']:selected").val() || jQuery("input[name='od_subscription_select_number']:checked").val();

        if (!od_subscription_select_number_val) {
            alert("<?php echo subscription_item_select_title(); ?>를 선택해주세요");

            return false;
        }

        if (!settle_method) {
            alert("결제수단을 선택하십시오.");
            return false;
        }

        var od_price = parseInt(f.od_price.value);
        var send_cost = parseInt(f.od_send_cost.value);
        var send_cost2 = parseInt(f.od_send_cost2.value);
        var send_coupon = parseInt(f.od_send_coupon.value);

        var max_point = 0;
        if (typeof(f.max_temp_point) != "undefined")
            max_point = parseInt(f.max_temp_point.value);

        var temp_point = 0;

        var tot_price = od_price + send_cost + send_cost2 - send_coupon - temp_point;

        var is_subscription_card_checked = $(".od_subscription_ids").is(':checked');

        var is_settle_card = $("#od_settle_card").is(':checked') || is_subscription_card_checked;

        if (is_settle_card) {
            if (tot_price < 1000) {
                alert("신용카드는 1000원 이상 결제가 가능합니다.");
                return false;
            }
        }

        <?php if (get_subs_option('su_pg_service') == 'inicis') { ?>
            if (f.action != form_action_url) {
                f.action = form_action_url;
                f.removeAttribute("target");
                f.removeAttribute("accept-charset");
            }
        <?php } ?>

        var form_order_method = '';

        if (jQuery(f).triggerHandler("subscriptionform_sumbit_" + form_order_method) !== false) {

            <?php if (get_subs_option('su_pg_service') == 'kcp') { ?>
                switch (settle_method) {
                    case "신용카드":
                        f.pay_method.value = "AUTH:CARD";
                        break;
                    default:
                        f.pay_method.value = "exist_card";
                }
            <?php } elseif (get_subs_option('su_pg_service') == 'inicis') { ?>
                switch (settle_method) {

                    case "신용카드":
                        f.gopaymethod.value = "Card";
                        f.acceptmethod.value = f.acceptmethod.value.replace(":useescrow", "");
                        break;
                    default:
                        f.gopaymethod.value = is_subscription_card_checked ? "exist_card" : "오류";
                        f.acceptmethod.value = f.acceptmethod.value.replace(":useescrow", "");

                }
            <?php } elseif (get_subs_option('su_pg_service') == 'tosspayments') { ?>
                switch (settle_method) {

                    case "신용카드":
                        f.gopaymethod.value = "Card";
                        break;
                    default:
                        f.gopaymethod.value = is_subscription_card_checked ? "exist_card" : "오류";

                }
            <?php } elseif (get_subs_option('su_pg_service') == 'nicepay') { ?>
                switch (settle_method) {

                    case "신용카드":
                        f.PayMethod.value = "CARD";
                        break;
                    default:
                        f.PayMethod.value = is_subscription_card_checked ? "exist_card" : "오류";
                        break;
                }
            <?php } ?>

            // 주문정보 임시저장
            var order_data = $(f).serialize();
            var save_result = "";
            $.ajax({
                type: "POST",
                data: order_data,
                url: g5_url + "/subscription/ajax.orderdatasave.php",
                cache: false,
                async: false,
                success: function(data) {
                    save_result = data;
                }
            });

            if (save_result) {
                alert(save_result);
                return false;
            }

            // 결제정보설정
            <?php if (get_subs_option('su_pg_service') == 'kcp') { ?>
                f.buyr_name.value = f.od_name.value;

                if (f.pay_method.value != "무통장") {
                    jsf__pay(f);
                } else {
                    f.submit();
                }
            <?php } ?>
            <?php if (get_subs_option('su_pg_service') == 'tosspayments') { ?>

                if (f.gopaymethod.value == "exist_card") {
                    f.submit();
                    return false;
                }

                requestBillingAuth({
                    od_id: "<?php echo $od_id; ?>",
                    price: tot_price,
                    customerEmail: f.od_name.value,
                    customerName: f.od_email.value
                });

            <?php } ?>
            <?php if (get_subs_option('su_pg_service') == 'inicis') { ?>
                f.price.value = f.good_mny.value;
                f.buyername.value = f.od_name.value;
                f.buyeremail.value = f.od_email.value;
                f.buyertel.value = f.od_hp.value ? f.od_hp.value : f.od_tel.value;

                if (f.gopaymethod.value == "exist_card") {
                    f.submit();
                    return false;
                }

                if (!make_signature(f))
                    return false;

                paybtn(f);

            <?php } ?>
            <?php if (get_subs_option('su_pg_service') == 'nicepay') { ?>

                if (f.PayMethod.value == "exist_card") {
                    f.submit();
                    return false;
                }

                // 새 신용카드 등록인 경우
                nicepay_modal_open();
            <?php } ?>
        }

        return false;

        document.getElementById("display_pay_button").style.display = "none";
        document.getElementById("show_progress").style.display = "block";

        setTimeout(function() {
            f.submit();
        }, 300);
    }

    // 주문폼 필드체크
    function orderfield_check(f) {
        errmsg = "";
        errfld = "";
        var deffld = "";

        check_field(f.od_name, "주문하시는 분 이름을 입력하십시오.");
        if (typeof(f.od_pwd) != 'undefined') {
            clear_field(f.od_pwd);
            if ((f.od_pwd.value.length < 3) || (f.od_pwd.value.search(/([^A-Za-z0-9]+)/) != -1))
                error_field(f.od_pwd, "회원이 아니신 경우 주문서 조회시 필요한 비밀번호를 3자리 이상 입력해 주십시오.");
        }
        check_field(f.od_tel, "주문하시는 분 전화번호를 입력하십시오.");
        check_field(f.od_addr1, "주소검색을 이용하여 주문하시는 분 주소를 입력하십시오.");
        //check_field(f.od_addr2, " 주문하시는 분의 상세주소를 입력하십시오.");
        check_field(f.od_zip, "");

        clear_field(f.od_email);
        if (f.od_email.value == '' || f.od_email.value.search(/(\S+)@(\S+)\.(\S+)/) == -1)
            error_field(f.od_email, "E-mail을 바르게 입력해 주십시오.");

        if (typeof(f.od_hope_date) != "undefined") {
            clear_field(f.od_hope_date);
            if (!f.od_hope_date.value)
                error_field(f.od_hope_date, "희망배송일을 선택하여 주십시오.");
        }

        check_field(f.od_b_name, "받으시는 분 이름을 입력하십시오.");
        check_field(f.od_b_tel, "받으시는 분 전화번호를 입력하십시오.");
        check_field(f.od_b_addr1, "주소검색을 이용하여 받으시는 분 주소를 입력하십시오.");
        //check_field(f.od_b_addr2, "받으시는 분의 상세주소를 입력하십시오.");
        check_field(f.od_b_zip, "");

        var od_settle_bank = document.getElementById("od_settle_bank");
        if (od_settle_bank) {
            if (od_settle_bank.checked) {
                check_field(f.od_bank_account, "계좌번호를 선택하세요.");
                check_field(f.od_deposit_name, "입금자명을 입력하세요.");
            }
        }

        // 배송비를 받지 않거나 더 받는 경우 아래식에 + 또는 - 로 대입
        f.od_send_cost.value = parseInt(f.od_send_cost.value);

        if (errmsg) {
            alert(errmsg);
            errfld.focus();
            return false;
        }

        var settle_case = document.getElementsByName("od_settle_case");
        var settle_check = false;
        for (i = 0; i < settle_case.length; i++) {
            if (settle_case[i].checked) {
                settle_check = true;
                settle_method = settle_case[i].value;
                break;
            }
        }
        if (!settle_check) {
            alert("결제방식을 선택하십시오.");
            return false;
        }

        return true;
    }

    // 결제체크
    function payment_check(f) {
        var max_point = 0;
        var od_price = parseInt(f.od_price.value);
        var send_cost = parseInt(f.od_send_cost.value);
        var send_cost2 = parseInt(f.od_send_cost2.value);
        var send_coupon = parseInt(f.od_send_coupon.value);
        temp_point = 0;

        if (typeof(f.max_temp_point) != "undefined")
            var max_point = parseInt(f.max_temp_point.value);

        if (typeof(f.od_temp_point) != "undefined") {
            if (f.od_temp_point.value) {
                var point_unit = parseInt(<?php echo $default['de_settle_point_unit']; ?>);
                temp_point = parseInt(f.od_temp_point.value) || 0;

                if (temp_point < 0) {
                    alert("포인트를 0 이상 입력하세요.");
                    f.od_temp_point.select();
                    return false;
                }

                if (temp_point > od_price) {
                    alert("상품 주문금액(배송비 제외) 보다 많이 포인트결제할 수 없습니다.");
                    f.od_temp_point.select();
                    return false;
                }

                if (temp_point > <?php echo (int)$member['mb_point']; ?>) {
                    alert("회원님의 포인트보다 많이 결제할 수 없습니다.");
                    f.od_temp_point.select();
                    return false;
                }

                if (temp_point > max_point) {
                    alert(max_point + "점 이상 결제할 수 없습니다.");
                    f.od_temp_point.select();
                    return false;
                }

                if (parseInt(parseInt(temp_point / point_unit) * point_unit) != temp_point) {
                    alert("포인트를 " + String(point_unit) + "점 단위로 입력하세요.");
                    f.od_temp_point.select();
                    return false;
                }
            }
        }

        var tot_price = od_price + send_cost + send_cost2 - send_coupon - temp_point;

        console.log(tot_price);

        if (document.getElementById("od_settle_card")) {
            if (document.getElementById("od_settle_card").checked) {
                if (tot_price < 1000) {
                    alert("신용카드는 1000원 이상 결제가 가능합니다.");
                    return false;
                }
            }
        }

        <?php if ($default['de_tax_flag_use']) { ?>
            calculate_tax();
        <?php } ?>

        return true;
    }

    // 구매자 정보와 동일합니다.
    function gumae2baesong() {
        var f = document.forderform;

        f.od_b_name.value = f.od_name.value;
        f.od_b_tel.value = f.od_tel.value;
        f.od_b_hp.value = f.od_hp.value;
        f.od_b_zip.value = f.od_zip.value;
        f.od_b_addr1.value = f.od_addr1.value;
        f.od_b_addr2.value = f.od_addr2.value;
        f.od_b_addr3.value = f.od_addr3.value;
        f.od_b_addr_jibeon.value = f.od_addr_jibeon.value;

        calculate_sendcost(String(f.od_b_zip.value));
    }

    <?php if ($default['de_hope_date_use']) { ?>
        $(function() {
            $("#od_hope_date").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: "yy-mm-dd",
                showButtonPanel: true,
                yearRange: "c-99:c+99",
                minDate: "+<?php echo (int)$default['de_hope_date_after']; ?>d;",
                maxDate: "+<?php echo (int)$default['de_hope_date_after'] + 6; ?>d;"
            });
        });
    <?php } ?>
</script>