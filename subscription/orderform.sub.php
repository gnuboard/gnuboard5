<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (!get_subs_option('su_pg_service')) {
    alert('관리자에서 정기결제 PG사를 설정해 주세요.', G5_SHOP_URL);
}

require_once G5_SUBSCRIPTION_PATH . '/settle_' . get_subs_option('su_pg_service') . '.inc.php';

// 결제대행사별 코드 include (스크립트 등)
require_once G5_SUBSCRIPTION_PATH . '/' . get_subs_option('su_pg_service') . '/orderform.1.php';
?>

<?php if (get_subs_option('su_card_test')) { ?>
    <div class="is-od-test">이 정기구독은 테스트로 진행됩니다.</div>
<?php } ?>

<form name="forderform" id="forderform" method="post" action="<?php echo $order_action_url; ?>" autocomplete="off">
    <div id="sod_frm" class="sod_frm_pc">
        <!-- 주문상품 확인 시작 { -->
        <div class="tbl_head03 tbl_wrap od_prd_list">
            <table id="sod_list">
                <thead>
                    <tr>
                        <th scope="col">상품명</th>
                        <th scope="col">총수량</th>
                        <th scope="col">판매가</th>
                        <th scope="col">소계</th>
                        <th scope="col">포인트</th>
                        <th scope="col">배송비</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $tot_point = 0;
                    $tot_sell_price = 0;

                    $goods = $goods_it_id = '';
                    $goods_count = -1;

                    $ct_subscription_number = '';   // 정기결제 관련
                    $ct_firstshipment_date = '';    // 정기결제 관련
                    $ct_date_format = '';           // 정기결제 관련

                    // $s_cart_id 로 현재 장바구니 자료 쿼리
                    $sql = " select a.ct_id,
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
                                    b.it_notax
                               from {$g5['g5_subscription_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
                              where a.od_id = '$s_cart_id'
                                and a.ct_select = '1' ";
                    $sql .= " group by a.it_id ";
                    $sql .= " order by a.ct_id ";

                    $result = sql_query($sql);

                    $good_info = '';
                    $it_send_cost = 0;
                    $it_cp_count = 0;

                    $comm_tax_mny = 0; // 과세금액
                    $comm_vat_mny = 0; // 부가세
                    $comm_free_mny = 0; // 면세금액
                    $tot_tax_mny = 0;

                    for ($i = 0; $row = sql_fetch_array($result); ++$i) {

                        $cp_button = '';

                        // 합계금액 계산
                        $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
                            SUM(ct_point * ct_qty) as point,
                            SUM(ct_qty) as qty
                        from {$g5['g5_subscription_cart_table']}
                        where it_id = '{$row['it_id']}'
                          and od_id = '$s_cart_id' ";
                        $sum = sql_fetch($sql);

                        if (!$goods) {
                            // $goods = addslashes($row[it_name]);
                            // $goods = sanitize_input($row[it_name]);
                            $goods = preg_replace("/\'|\"|\||\,|\&|\;/", '', $row['it_name']);
                            $goods_it_id = $row['it_id'];
                        }
                        ++$goods_count;

                        $image = get_it_image($row['it_id'], 80, 80);

                        $it_name = '<b>' . stripslashes($row['it_name']) . '</b>';
                        $it_options = subscription_print_item_options($row['it_id'], $s_cart_id);
                        if ($it_options) {
                            $it_name .= '<div class="sod_opt">' . $it_options . '</div>';
                        }

                        $ct_subscription_number = isset($row['ct_subscription_number']) ? $row['ct_subscription_number'] : 0;   // 정기결제 관련
                        $ct_firstshipment_date = isset($row['ct_firstshipment_date']) ? $row['ct_firstshipment_date'] : 0;    // 정기결제 관련
                        $ct_date_format = isset($row['ct_date_format']) ? $row['ct_date_format'] : 0;           // 정기결제 관련

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
                    ?>

                        <tr>

                            <td class="td_prd">
                                <div class="sod_img"><?php echo $image; ?></div>
                                <div class="sod_name">
                                    <input type="hidden" name="it_id[<?php echo $i; ?>]" value="<?php echo sanitize_input($row['it_id']); ?>">
                                    <input type="hidden" name="it_name[<?php echo $i; ?>]" value="<?php echo sanitize_input($row['it_name']); ?>">
                                    <input type="hidden" name="it_price[<?php echo $i; ?>]" value="<?php echo sanitize_input($sell_price); ?>">
                                    <input type="hidden" name="cp_id[<?php echo $i; ?>]" value="">
                                    <input type="hidden" name="cp_price[<?php echo $i; ?>]" value="0">
                                    <?php echo $it_name; ?>
                                    <?php echo $cp_button; ?>

                                </div>
                            </td>
                            <td class="td_num"><?php echo number_format($sum['qty']); ?></td>
                            <td class="td_numbig  text_right"><?php echo number_format($row['ct_price']); ?></td>
                            <td class="td_numbig  text_right"><span class="total_price"><?php echo number_format($sell_price); ?></span></td>
                            <td class="td_numbig  text_right"><?php echo number_format($point); ?></td>
                            <td class="td_dvr"><?php echo $ct_send_cost; ?></td>
                        </tr>

                    <?php
                        $tot_point += $point;
                        $tot_sell_price += $sell_price;
                    } // for 끝

                    if ($i == 0) {
                        // echo '<tr><td colspan="7" class="empty_table">장바구니에 담긴 상품이 없습니다.</td></tr>';
                        alert('장바구니가 비어 있습니다.', G5_SUBSCRIPTION_URL . '/cart.php');
                    } else {
                        // 배송비 계산
                        $send_cost = get_subscription_sendcost($s_cart_id);
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <?php if ($goods_count) {
            $goods .= ' 외 ' . $goods_count . '건';
        } ?>
        <!-- } 주문상품 확인 끝 -->

        <div class="sod_left">
            <input type="hidden" name="od_price" value="<?php echo (int) $tot_sell_price; ?>">
            <input type="hidden" name="org_od_price" value="<?php echo (int) $tot_sell_price; ?>">
            <input type="hidden" name="od_send_cost" value="<?php echo (int) $send_cost; ?>">
            <input type="hidden" name="od_send_cost2" value="0">
            <input type="hidden" name="item_coupon" value="0">
            <input type="hidden" name="od_coupon" value="0">
            <input type="hidden" name="od_send_coupon" value="0">
            <input type="hidden" name="od_goods_name" value="<?php echo sanitize_input($goods); ?>">

            <?php
            // 결제대행사별 코드 include (결제대행사 정보 필드)
            require_once G5_SUBSCRIPTION_PATH . '/' . get_subs_option('su_pg_service') . '/orderform.2.php';
            ?>

            <!-- 주문하시는 분 입력 시작 { -->
            <section id="sod_frm_orderer">
                <h2>주문하시는 분</h2>

                <div class="tbl_frm01 tbl_wrap">
                    <table>
                        <tbody>
                            <tr>
                                <th scope="row"><label for="od_name">이름<strong class="sound_only"> 필수</strong></label></th>
                                <td><input type="text" name="od_name" value="<?php echo isset($member['mb_name']) ? sanitize_input($member['mb_name']) : ''; ?>" id="od_name" required class="frm_input required" maxlength="20"></td>
                            </tr>

                            <?php if (!$is_member) { // 비회원이면
                            ?>
                                <tr>
                                    <th scope="row"><label for="od_pwd">비밀번호</label></th>
                                    <td>
                                        <span class="frm_info">영,숫자 3~20자 (주문서 조회시 필요)</span>
                                        <input type="password" name="od_pwd" id="od_pwd" required class="frm_input required" maxlength="20">
                                    </td>
                                </tr>
                            <?php } ?>

                            <tr>
                                <th scope="row"><label for="od_tel">연락처<strong class="sound_only"> 필수</strong></label></th>
                                <td><input type="text" name="od_tel" value="<?php echo sanitize_input($member['mb_tel']); ?>" id="od_tel" required class="frm_input required" maxlength="20">
                                    <input type="hidden" name="od_hp" value="<?php echo sanitize_input($member['mb_hp']); ?>" id="od_hp" maxlength="20">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">주소</th>
                                <td>
                                    <label for="od_zip" class="sound_only">우편번호<strong class="sound_only"> 필수</strong></label>
                                    <input type="text" name="od_zip" value="<?php echo $member['mb_zip1'] . $member['mb_zip2']; ?>" id="od_zip" required class="frm_input required" size="8" maxlength="6" placeholder="우편번호">
                                    <button type="button" class="btn_address" onclick="win_zip('forderform', 'od_zip', 'od_addr1', 'od_addr2', 'od_addr3', 'od_addr_jibeon');">주소 검색</button><br>
                                    <input type="text" name="od_addr1" value="<?php echo sanitize_input($member['mb_addr1']); ?>" id="od_addr1" required class="frm_input frm_address required" size="60" placeholder="기본주소">
                                    <label for="od_addr1" class="sound_only">기본주소<strong class="sound_only"> 필수</strong></label><br>
                                    <input type="text" name="od_addr2" value="<?php echo sanitize_input($member['mb_addr2']); ?>" id="od_addr2" class="frm_input frm_address" size="60" placeholder="상세주소">
                                    <label for="od_addr2" class="sound_only">상세주소</label>
                                    <br>
                                    <input type="text" name="od_addr3" value="<?php echo sanitize_input($member['mb_addr3']); ?>" id="od_addr3" class="frm_input frm_address" size="60" readonly="readonly" placeholder="참고항목">
                                    <label for="od_addr3" class="sound_only">참고항목</label><br>
                                    <input type="hidden" name="od_addr_jibeon" value="<?php echo sanitize_input($member['mb_addr_jibeon']); ?>">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="od_email">E-mail<strong class="sound_only"> 필수</strong></label></th>
                                <td><input type="text" name="od_email" value="<?php echo $member['mb_email']; ?>" id="od_email" required class="frm_input required" size="35" maxlength="100"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
            <!-- } 주문하시는 분 입력 끝 -->

            <!-- 받으시는 분 입력 시작 { -->
            <section id="sod_frm_taker">
                <h2>받으시는 분</h2>

                <div class="tbl_frm01 tbl_wrap">
                    <table>
                        <tbody>
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
                                    $addr_list .= '<input type="radio" name="ad_sel_addr" value="' . sanitize_input($val1) . '" id="ad_sel_addr_def">' . PHP_EOL;
                                    $addr_list .= '<label for="ad_sel_addr_def">기본배송지</label>' . PHP_EOL;
                                }

                                // 최근배송지
                                $sql = "SELECT * 
                                        FROM {$g5['g5_shop_order_address_table']} 
                                        WHERE mb_id = '" . $member['mb_id'] . "' 
                                        AND ad_default = '0' 
                                        ORDER BY ad_id DESC 
                                        LIMIT 1";
                                $results = sql_query($sql);

                                $recent_deliverys = sql_result_array($results);

                                $i = 0;
                                foreach ($recent_deliverys as $row) {

                                    if (empty($row)) continue;

                                    $val1 = $row['ad_name'] . $sep . $row['ad_tel'] . $sep . $row['ad_hp'] . $sep . $row['ad_zip1'] . $sep . $row['ad_zip2'] . $sep . $row['ad_addr1'] . $sep . $row['ad_addr2'] . $sep . $row['ad_addr3'] . $sep . $row['ad_jibeon'] . $sep . $row['ad_subject'];
                                    $val2 = '<label for="ad_sel_addr_' . ($i + 1) . '">최근배송지(' . ($row['ad_subject'] ? sanitize_input($row['ad_subject']) : sanitize_input($row['ad_name'])) . ')</label>';
                                    $addr_list .= '<input type="radio" name="ad_sel_addr" value="' . sanitize_input($val1) . '" id="ad_sel_addr_' . ($i + 1) . '"> ' . PHP_EOL . $val2 . PHP_EOL;
                                    $i++;
                                }

                                $addr_list .= '<input type="radio" name="ad_sel_addr" value="new" id="od_sel_addr_new">' . PHP_EOL;
                                $addr_list .= '<label for="od_sel_addr_new">신규배송지</label>' . PHP_EOL;

                                $addr_list .= '<a href="' . G5_SUBSCRIPTION_URL . '/orderaddress.php" id="order_address" class="btn_frmline">배송지목록</a>';
                            } else {
                                // 주문자와 동일
                                $addr_list .= '<input type="checkbox" name="ad_sel_addr" value="same" id="ad_sel_addr_same">' . PHP_EOL;
                                $addr_list .= '<label for="ad_sel_addr_same">주문자와 동일</label>' . PHP_EOL;
                            }
                            ?>
                            <tr>
                                <th scope="row">배송지선택</th>
                                <td>
                                    <div class="order_choice_place">
                                        <?php echo $addr_list; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php if ($is_member) { ?>
                                <tr>
                                    <th scope="row"><label for="ad_subject">배송지명</label></th>
                                    <td>
                                        <input type="text" name="ad_subject" id="ad_subject" class="frm_input" maxlength="20">
                                        <input type="checkbox" name="ad_default" id="ad_default" value="1">
                                        <label for="ad_default">기본배송지로 설정</label>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <th scope="row"><label for="od_b_name">이름<strong class="sound_only"> 필수</strong></label></th>
                                <td><input type="text" name="od_b_name" id="od_b_name" required class="frm_input required" maxlength="20"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="od_b_tel">연락처<strong class="sound_only"> 필수</strong></label></th>
                                <td>
                                    <input type="text" name="od_b_tel" id="od_b_tel" required class="frm_input required" maxlength="20">
                                    <input type="hidden" name="od_b_hp" id="od_b_hp" maxlength="20">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">주소</th>
                                <td id="sod_frm_addr">
                                    <label for="od_b_zip" class="sound_only">우편번호<strong class="sound_only"> 필수</strong></label>
                                    <input type="text" name="od_b_zip" id="od_b_zip" required class="frm_input required" size="8" maxlength="6" placeholder="우편번호">
                                    <button type="button" class="btn_address" onclick="win_zip('forderform', 'od_b_zip', 'od_b_addr1', 'od_b_addr2', 'od_b_addr3', 'od_b_addr_jibeon');">주소 검색</button><br>
                                    <input type="text" name="od_b_addr1" id="od_b_addr1" required class="frm_input frm_address required" size="60" placeholder="기본주소">
                                    <label for="od_b_addr1" class="sound_only">기본주소<strong> 필수</strong></label><br>
                                    <input type="text" name="od_b_addr2" id="od_b_addr2" class="frm_input frm_address" size="60" placeholder="상세주소">
                                    <label for="od_b_addr2" class="sound_only">상세주소</label>
                                    <br>
                                    <input type="text" name="od_b_addr3" id="od_b_addr3" readonly="readonly" class="frm_input frm_address" size="60" placeholder="참고항목">
                                    <label for="od_b_addr3" class="sound_only">참고항목</label><br>
                                    <input type="hidden" name="od_b_addr_jibeon" value="">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="od_memo">전하실말씀</label></th>
                                <td><textarea name="od_memo" id="od_memo"></textarea></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
            <!-- } 받으시는 분 입력 끝 -->
            <?php run_event('subscription_add_form_html'); ?>

            <section id="sod_frm_subscription_input">
                <h2>정기구독정보 입력</h2>

                <?php // 정기결제 공통폼 불러오기
                include_once(G5_SUBSCRIPTION_PATH . '/subscription_order_modal.php');
                ?>
            </section>
        </div>

        <div class="sod_right">
            <!-- 주문상품 합계 시작 { -->
            <div id="sod_bsk_tot">
                <ul>
                    <li class="is-order-subscription sod_bsk_sell">
                        <span>주문</span>
                        <strong><?php echo number_format($tot_sell_price); ?></strong>원
                    </li>
                    <li class="sod_bsk_dvr">
                        <span>배송비</span>
                        <strong><?php echo number_format($send_cost); ?></strong>원
                    </li>
                    <li class="sod_bsk_point">
                        <span>포인트</span>
                        <strong><?php echo number_format($tot_point); ?></strong>점
                    </li>
                    <li class="sod_bsk_cnt">
                        <span>총계</span>
                        <?php $tot_price = $tot_sell_price + $send_cost; // 총계 = 주문상품금액합계 + 배송비
                        ?>
                        <strong id="ct_tot_price"><?php echo number_format($tot_price); ?></strong>원
                    </li>

                </ul>
            </div>
            <!-- } 주문상품 합계 끝 -->


            <!-- 결제정보 입력 시작 { -->
            <?php
            $oc_cnt = $sc_cnt = 0;
            if ($is_member) {
                // 주문쿠폰
                $sql = "SELECT cp_id 
                    FROM {$g5['g5_shop_coupon_table']} 
                    WHERE mb_id IN ('" . $member['mb_id'] . "', '전체회원') 
                    AND cp_method = '2' 
                    AND cp_start <= '" . G5_TIME_YMD . "' 
                    AND cp_end >= '" . G5_TIME_YMD . "' 
                    AND cp_minimum <= '" . $tot_sell_price . "'";
                $results = sql_query($sql);
                $member_coupons = sql_result_array($results);

                foreach ($member_coupons as $cp) {
                    if (is_used_coupon($member['mb_id'], $cp['cp_id'])) {
                        continue;
                    }

                    ++$oc_cnt;
                }

                if ($send_cost > 0) {
                    // 배송비쿠폰
                    $sql = "SELECT cp_id 
                            FROM {$g5['g5_shop_coupon_table']} 
                            WHERE mb_id IN ('" . $member['mb_id'] . "', '전체회원') 
                            AND cp_method = '3' 
                            AND cp_start <= '" . G5_TIME_YMD . "' 
                            AND cp_end >= '" . G5_TIME_YMD . "' 
                            AND cp_minimum <= '" . $tot_sell_price . "'";
                    $results = sql_query($sql);
                    $delivery_coupons = sql_result_array($results);

                    foreach ($delivery_coupons as $cp) {
                        if (is_used_coupon($member['mb_id'], $cp['cp_id'])) {
                            continue;
                        }

                        ++$sc_cnt;
                    }
                }
            }
            ?>

            <section id="sod_frm_pay">
                <h2>결제정보</h2>

                <div class="pay_tbl">
                    <table>
                        <tbody>
                            <?php if ($oc_cnt > 0) { ?>
                                <tr>
                                    <th scope="row">주문할인</th>
                                    <td>
                                        <strong id="od_cp_price">0</strong>원
                                        <input type="hidden" name="od_cp_id" value="">
                                        <button type="button" id="od_coupon_btn" class="btn_frmline">쿠폰적용</button>
                                    </td>
                                </tr>
                            <?php } ?>
                            <?php if ($sc_cnt > 0) { ?>
                                <tr>
                                    <th scope="row">배송비할인</th>
                                    <td>
                                        <strong id="sc_cp_price">0</strong>원
                                        <input type="hidden" name="sc_cp_id" value="">
                                        <button type="button" id="sc_coupon_btn" class="btn_frmline">쿠폰적용</button>
                                    </td>
                                </tr>
                            <?php } ?>

                            <tr>
                                <th>추가배송비</th>
                                <td><strong id="od_send_cost2">0</strong>원<br>(지역에 따라 추가되는 도선료 등의 배송비입니다.)</td>
                            </tr>

                        </tbody>
                    </table>
                </div>
                <div id="od_tot_price">
                    <span>총 주문금액</span>
                    <strong class="print_price"><?php echo number_format($tot_price); ?></strong>원
                </div>
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
                ?>
                <div id="od_pay_sl" class="is_subscription_pays">
                    <div class="od_pay_buttons_el">
                        <h3>결제수단 선택</h3>
                        <?php
                        $multi_settle = 0;
                        $checked = '';

                        $escrow_title = '';

                        echo '<fieldset id="sod_frm_paysel">';
                        echo '<legend>결제방법 선택</legend>';

                        // 신용카드 사용
                        if (get_subs_option('su_card_use')) {
                            ++$multi_settle;

                            if ($mcards) {

                                echo '<input type="hidden" id="od_select_card_number" name="od_select_card_number" value="" >';

                                $j = 0;

                                foreach ($mcards as $card) {
                                    echo '<input type="radio" id="od_subscription_card_' . $j . '" class="od_subscription_ids" name="od_settle_case" value="' . $card['max_id'] . '"> <label for="od_subscription_card_' . $j . '" class="lb_icon card_icon subscription_card"><span>' . subscription_pg_cardname($card['od_card_name']) . '<br>' . $card['card_mask_number'] . '</span></label>' . PHP_EOL;
                                    $j++;
                                }
                            }
                            echo '<input type="radio" id="od_settle_card" name="od_settle_case" value="신용카드" ' . $checked . '> <label for="od_settle_card" class="lb_icon card_icon">새 신용카드 등록</label>' . PHP_EOL;
                            $checked = '';
                        }

                        $temp_point = 0;

                        echo '</fieldset>';

                        if ($multi_settle == 0) {
                            echo '<p>결제할 방법이 없습니다.<br>운영자에게 알려주시면 감사하겠습니다.</p>';
                        }
                        ?>
                    </div>
            </section>
            <!-- } 결제 정보 입력 끝 -->

            <?php
            // 결제대행사별 코드 include (주문버튼)
            require_once G5_SUBSCRIPTION_PATH . '/' . get_subs_option('su_pg_service') . '/orderform.3.php';
            ?>
        </div>

    </div>
</form>
<script>
    var zipcode = "";
    var form_action_url = "<?php echo $order_action_url; ?>";

    jQuery(function($) {
        var $cp_btn_el;
        var $cp_row_el;

        $(".cp_btn").click(function() {
            $cp_btn_el = $(this);
            $cp_row_el = $(this).closest("tr");
            $("#cp_frm").remove();
            var it_id = $cp_btn_el.closest("tr").find("input[name^=it_id]").val();

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
            window.open(url, "win_address", "left=100,top=100,width=800,height=600,scrollbars=1");
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
        var $dup_sell_el = $el.find(".total_price");
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

        $("#ct_tot_coupon").text(number_format(String(tot_cp_price)));
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

        $("input[name=good_mny]").val(tot_price);
        $("#od_tot_price .print_price").text(number_format(String(tot_price)));
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
            temp_point = parseInt($("input[name=od_temp_point]").val());

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

    function forderform_check(f) {
        // 재고체크
        var stock_msg = subscription_order_stock_check();
        if (stock_msg != "") {
            alert(stock_msg);
            return false;
        }

        // alert($.datepicker.formatDate('dd MM, yy', $("#od_hope_date_print").datepicker( "getDate" )));

        // alert($.datepicker.formatDate('yy-mm-dd', $("#od_hope_date_print").datepicker( "getDate" )));

        // return;

        if (!jQuery("#od_hope_date").val() && $.fn.datepicker) {
            jQuery("#od_hope_date").val($.datepicker.formatDate('yy-mm-dd', $("#od_hope_date_print").datepicker("getDate")));
        }

        errmsg = "";
        errfld = "";
        var deffld = "";

        check_field(f.od_name, "주문하시는 분 이름을 입력하십시오.");
        if (typeof(f.od_pwd) != 'undefined') {
            clear_field(f.od_pwd);
            if ((f.od_pwd.value.length < 3) || (f.od_pwd.value.search(/([^A-Za-z0-9]+)/) != -1))
                error_field(f.od_pwd, "회원이 아니신 경우 주문서 조회시 필요한 비밀번호를 3자리 이상 입력해 주십시오.");
        }
        check_field(f.od_tel, "주문하시는 분 연락처를 입력하십시오.");
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
        check_field(f.od_b_tel, "받으시는 분 연락처를 입력하십시오.");
        check_field(f.od_b_addr1, "주소검색을 이용하여 받으시는 분 주소를 입력하십시오.");
        //check_field(f.od_b_addr2, "받으시는 분의 상세주소를 입력하십시오.");
        check_field(f.od_b_zip, "");

        // 배송비를 받지 않거나 더 받는 경우 아래식에 + 또는 - 로 대입
        f.od_send_cost.value = parseInt(f.od_send_cost.value);

        if (errmsg) {
            alert(errmsg);
            errfld.focus();
            return false;
        }

        // var od_subscription_select_val = jQuery("#od_subscription_select_data :selected").val() || jQuery("input[name='od_subscription_select_data']:checked").val() || jQuery("input[name='od_subscription_select_data']").val();

        <?php if (get_subs_option('su_chk_user_delivery')) {    // 배송주기를 사용자가 입력이 가능한경우 
        ?>

            if (!jQuery("#od_subscription_select_day").val()) {
                //alert("<?php echo subscription_item_delivery_title(); ?>를 선택해주세요");
                //jQuery("#od_subscription_select_day").focus();
            }

            var od_subscription_select_val = jQuery("#od_subscription_select_data :selected").val() || jQuery("input[name='od_subscription_select_data']:checked").val() || jQuery("input[name='od_subscription_select_data']").val();

        <?php } else { ?>

            var od_subscription_select_val = jQuery("#od_subscription_select_data :selected").val() || jQuery("input[name='od_subscription_select_data']:checked").val();

        <?php } ?>

        if (!od_subscription_select_val) {
            alert("<?php echo subscription_item_delivery_title(); ?>를 선택해주세요");
            jQuery("#od_subscription_select_data").focus();

            return false;
        }

        //var od_subscription_select_number_val = jQuery("input[name='od_subscription_select_number']:selected").val() || jQuery("input[name='od_subscription_select_number']:checked").val() || jQuery("input[name='od_subscription_select_number']").val();

        var od_subscription_select_number_val = jQuery("input[name='od_subscription_select_number']:selected").val() || jQuery("select[name='od_subscription_select_number']").val() || jQuery("input[name='od_subscription_select_number']:checked").val();

        if (!od_subscription_select_number_val) {
            alert("<?php echo subscription_item_select_title(); ?>를 선택해주세요");

            return false;
        }

        var settle_case = document.getElementsByName("od_settle_case");
        var settle_check = false;
        var settle_method = "";

        for (i = 0; i < settle_case.length; i++) {
            if (settle_case[i].checked) {
                settle_check = true;
                settle_method = settle_case[i].value;
                break;
            }
        }
        if (!settle_check) {
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
                        f.pay_method.value = is_subscription_card_checked ? "exist_card" : "무통장";
                }
            <?php } elseif (get_subs_option('su_pg_service') == 'inicis') { ?>
                switch (settle_method) {

                    case "신용카드":
                        f.gopaymethod.value = "Card";
                        f.acceptmethod.value = f.acceptmethod.value.replace(":useescrow", "");
                        break;
                    default:
                        f.gopaymethod.value = is_subscription_card_checked ? "exist_card" : "무통장";
                        f.acceptmethod.value = f.acceptmethod.value.replace(":useescrow", "");

                }
            <?php } elseif (get_subs_option('su_pg_service') == 'tosspayments') { ?>
                switch (settle_method) {

                    case "신용카드":
                        f.gopaymethod.value = "Card";
                        break;
                    default:
                        f.gopaymethod.value = is_subscription_card_checked ? "exist_card" : "무통장";

                }
            <?php } elseif (get_subs_option('su_pg_service') == 'nicepay') { ?>
                switch (settle_method) {

                    case "신용카드":
                        f.PayMethod.value = "CARD";
                        break;
                    default:
                        f.PayMethod.value = is_subscription_card_checked ? "exist_card" : "무통장";
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

                if (f.pay_method.value == "무통장" || f.pay_method.value == "exist_card") {
                    f.submit();
                } else {
                    jsf__pay(f);

                }
            <?php } ?>
            <?php if (get_subs_option('su_pg_service') == 'tosspayments') { ?>

                if (f.gopaymethod.value == "무통장" || f.gopaymethod.value == "exist_card") {
                    f.submit();
                    return false;
                }

                requestBillingAuth({
                    customerEmail: f.od_name.value,
                    customerName: f.od_email.value
                });

            <?php } ?>
            <?php if (get_subs_option('su_pg_service') == 'inicis') { ?>
                f.price.value = f.good_mny.value;
                f.buyername.value = f.od_name.value;
                f.buyeremail.value = f.od_email.value;
                f.buyertel.value = f.od_hp.value ? f.od_hp.value : f.od_tel.value;

                if (f.gopaymethod.value == "무통장" || f.gopaymethod.value == "exist_card") {
                    f.submit();
                    return false;
                }

                if (!make_signature(f))
                    return false;

                // console.log(f);
                // return false;

                paybtn(f);

            <?php } ?>
            <?php if (get_subs_option('su_pg_service') == 'nicepay') { ?>

                /*
                console.log(f);

                var cardNo = f.cardNo.value,
                    expMonth = f.expMonth.value,
                    expYear = f.expYear.value,
                    idNo = f.idNo.value,
                    cardPw = f.cardPw.value;

                if (!(validateCreditCard(cardNo))) {
                    alert("카드번호 형식이 아닙니다. 16자리의 숫자로 입력해 주세요.");
                    f.cardNo.focus();
                    return false;
                }

                if (!(validateCardExpiry(expMonth + "/" + expYear))) {
                    alert("카드유효기간 검증에 실패했습니다. 다시 확인해 주세요.");
                    f.expMonth.focus();
                    return false;
                }

                if (!/^[0-9]+$/.test(cardPw)) {
                    alert("카드 비밀번호 2자리 숫자로만 입력해 주세요.");
                    f.cardPw.focus();
                    return false;
                }

                f.submit();
                */

                /*
                if (f.gopaymethod.value == "무통장" || f.gopaymethod.value == "exist_card") {
                    f.submit();
                    return false;
                }
                */

                if (f.PayMethod.value == "무통장" || f.PayMethod.value == "exist_card") {
                    f.submit();
                    return false;
                }

                // 새 신용카드 등록인 경우
                nicepay_modal_open();
            <?php } ?>
        }

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
</script>