<?php
$sub_menu = '400410';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

$od_id = isset($_REQUEST['od_id']) ? safe_replace_regex($_REQUEST['od_id'], 'od_id') : '';

$g5['title'] = "미완료주문 내역";
include_once(G5_ADMIN_PATH.'/admin.head.php');

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g5['g5_shop_order_data_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
    alert("해당 주문번호로 미완료 주문서가 존재하지 않습니다.");
}

// 주문정보
$data = unserialize(base64_decode($od['dt_data']));

$sql_common = " from {$g5['g5_shop_cart_table']} where od_id = '{$od['cart_id']}' and ct_status = '쇼핑' and ct_select = '1' ";

// 주문금액
$sql = " select SUM(IF(io_type = 1, io_price, (ct_price + io_price)) * ct_qty) as od_price, COUNT(distinct it_id) as cart_count $sql_common ";
$row = sql_fetch($sql);
$tot_ct_price = $row['od_price'];
$cart_count   = $row['cart_count'];
$tot_od_price = $tot_ct_price;

// 쿠폰금액
$tot_cp_price = 0;
if($od['mb_id']) {
    // 상품쿠폰
    $tot_it_cp_price = $tot_od_cp_price = 0;
    $it_cp_cnt = (isset($data['cp_id']) && is_array($data['cp_id'])) ? count($data['cp_id']) : 0;
    $arr_it_cp_prc = array();
    for($i=0; $i<$it_cp_cnt; $i++) {
        $cid = $data['cp_id'][$i];
        $it_id = $data['it_id'][$i];
        $sql = " select cp_id, cp_method, cp_target, cp_type, cp_price, cp_trunc, cp_minimum, cp_maximum
                    from {$g5['g5_shop_coupon_table']}
                    where cp_id = '$cid'
                      and mb_id IN ( '{$od['mb_id']}', '전체회원' )
                      and cp_method IN ( 0, 1 ) ";
        $cp = sql_fetch($sql);
        if(! (isset($cp['cp_id']) && $cp['cp_id']))
            continue;

        // 사용한 쿠폰인지
        if(is_used_coupon($od['mb_id'], $cp['cp_id']))
            continue;

        // 분류할인인지
        if($cp['cp_method']) {
            $sql2 = " select it_id, ca_id, ca_id2, ca_id3
                        from {$g5['g5_shop_item_table']}
                        where it_id = '$it_id' ";
            $row2 = sql_fetch($sql2);

            if(!$row2['it_id'])
                continue;

            if($row2['ca_id'] != $cp['cp_target'] && $row2['ca_id2'] != $cp['cp_target'] && $row2['ca_id3'] != $cp['cp_target'])
                continue;
        } else {
            if($cp['cp_target'] != $it_id)
                continue;
        }

        // 상품금액
        $sql = " select SUM( IF(io_type = '1', io_price * ct_qty, (ct_price + io_price) * ct_qty)) as sum_price $sql_common and it_id = '$it_id' ";
        $ct = sql_fetch($sql);
        $item_price = $ct['sum_price'];

        if($cp['cp_minimum'] > $item_price)
            continue;

        $dc = 0;
        if($cp['cp_type']) {
            $dc = floor(($item_price * ($cp['cp_price'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];
        } else {
            $dc = $cp['cp_price'];
        }

        if($cp['cp_maximum'] && $dc > $cp['cp_maximum'])
            $dc = $cp['cp_maximum'];

        if($item_price < $dc)
            continue;

        $tot_it_cp_price += $dc;
        $arr_it_cp_prc[$it_id] = $dc;
    }

    $tot_od_price -= $tot_it_cp_price;

    // 주문쿠폰
    if(isset($data['od_cp_id']) && $data['od_cp_id']) {
        $sql = " select cp_id, cp_type, cp_price, cp_trunc, cp_minimum, cp_maximum
                    from {$g5['g5_shop_coupon_table']}
                    where cp_id = '{$data['od_cp_id']}'
                      and mb_id IN ( '{$od['mb_id']}', '전체회원' )
                      and cp_method = '2' ";
        $cp = sql_fetch($sql);

        // 사용한 쿠폰인지
        $cp_used = is_used_coupon($od['mb_id'], $cp['cp_id']);

        $dc = 0;
        if(!$cp_used && $cp['cp_id'] && ($cp['cp_minimum'] <= $tot_od_price)) {
            if($cp['cp_type']) {
                $dc = floor(($tot_od_price * ($cp['cp_price'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];
            } else {
                $dc = $cp['cp_price'];
            }

            if($cp['cp_maximum'] && $dc > $cp['cp_maximum'])
                $dc = $cp['cp_maximum'];

            $tot_od_cp_price = $dc;
            $tot_od_price -= $tot_od_cp_price;
        }
    }

    $tot_cp_price = $tot_it_cp_price + $tot_od_cp_price;
}

// 배송비
$od_send_cost = get_sendcost($od['cart_id']);

$tot_sc_cp_price = 0;
if($od['mb_id'] && $od_send_cost > 0) {
    // 배송쿠폰
    if($data['sc_cp_id']) {
        $sql = " select cp_id, cp_type, cp_price, cp_trunc, cp_minimum, cp_maximum
                    from {$g5['g5_shop_coupon_table']}
                    where cp_id = '{$data['sc_cp_id']}'
                      and mb_id IN ( '{$od['mb_id']}', '전체회원' )
                      and cp_method = '3' ";
        $cp = sql_fetch($sql);

        // 사용한 쿠폰인지
        $cp_used = is_used_coupon($od['mb_id'], $cp['cp_id']);

        $dc = 0;
        if(!$cp_used && $cp['cp_id'] && ($cp['cp_minimum'] <= $tot_od_price)) {
            if($cp['cp_type']) {
                $dc = floor(($send_cost * ($cp['cp_price'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];
            } else {
                $dc = $cp['cp_price'];
            }

            if($cp['cp_maximum'] && $dc > $cp['cp_maximum'])
                $dc = $cp['cp_maximum'];

            if($dc > $send_cost)
                $dc = $send_cost;

            $tot_sc_cp_price = $dc;
        }
    }
}

// 추가배송비
$od_send_cost2 = isset($data['od_send_cost2']) ? (int) $data['od_send_cost2'] : 0;

// 포인트
$od_temp_point = isset($data['od_temp_point']) ? (int) $data['od_temp_point'] : 0;

$order_price   = $tot_od_price + $od_send_cost + $od_send_cost2 - $tot_sc_cp_price - $od_temp_point;

// 상품목록
$sql = " select it_id, it_name, ct_notax, ct_send_cost, it_sc_type $sql_common group by it_id order by ct_id ";
$result = sql_query($sql);

$pg_anchor = '<ul class="anchor">
<li><a href="#anc_sodr_list">주문상품 목록</a></li>
<li><a href="#anc_sodr_orderer">주문하신 분</a></li>
<li><a href="#anc_sodr_taker">받으시는 분</a></li>
</ul>';
?>

<section id="anc_sodr_list">
    <h2 class="h2_frm">주문상품 목록</h2>
    <?php echo $pg_anchor; ?>
    <div class="local_desc02 local_desc">
        <p>
            주문일시 <strong><?php echo substr($od['dt_time'],0,16); ?> (<?php echo get_yoil($od['dt_time']); ?>)</strong>
            |
            주문합계 <strong><?php echo number_format($order_price); ?></strong>원
        </p>
    </div>

    <div class="tbl_head01 tbl_wrap">
        <table>
        <caption>주문 상품 목록</caption>
        <thead>
        <tr>
            <th scope="col">상품명</th>
            <th scope="col">옵션항목</th>
            <th scope="col">상태</th>
            <th scope="col">수량</th>
            <th scope="col">판매가</th>
            <th scope="col">소계</th>
            <th scope="col">쿠폰</th>
            <th scope="col">포인트</th>
            <th scope="col">배송비</th>
            <th scope="col">포인트반영</th>
            <th scope="col">재고반영</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for($i=0; $row=sql_fetch_array($result); $i++) {
            // 상품이미지
            $image = get_it_image($row['it_id'], 50, 50);

            // 상품의 옵션정보
            $sql = " select ct_id, it_id, ct_price, ct_point, ct_qty, ct_option, ct_status, cp_price, ct_stock_use, ct_point_use, ct_send_cost, io_type, io_price $sql_common and it_id = '{$row['it_id']}' order by io_type asc, ct_id asc ";
            $res = sql_query($sql);
            $rowspan = sql_num_rows($res);

            // 합계금액 계산
            $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price, SUM(ct_qty) as qty $sql_common and it_id = '{$row['it_id']}' ";
            $sum = sql_fetch($sql);

            // 배송비
            switch($row['ct_send_cost'])
            {
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
            if($row['it_sc_type'] == 2) {
                $sendcost = get_item_sendcost($row['it_id'], $sum['price'], $sum['qty'], $od['cart_id']);

                if($sendcost == 0)
                    $ct_send_cost = '무료';
            }

            for($k=0; $opt=sql_fetch_array($res); $k++) {
                if($opt['io_type'])
                    $opt_price = $opt['io_price'];
                else
                    $opt_price = $opt['ct_price'] + $opt['io_price'];

                // 소계
                $ct_price['stotal'] = $opt_price * $opt['ct_qty'];
                $ct_point['stotal'] = $opt['ct_point'] * $opt['ct_qty'];

                if($k == 0)
                    $opt_cp_price = isset($arr_it_cp_prc[$row['it_id']]) ? (int) $arr_it_cp_prc[$row['it_id']] : 0;
                else
                    $opt_cp_price = 0;
            ?>
            <tr>
                <?php if($k == 0) { ?>
                <td rowspan="<?php echo $rowspan; ?>">
                    <?php echo $image; ?> <?php echo stripslashes($row['it_name']); ?>
                    <?php if(isset($od['od_tax_flag']) && $od['od_tax_flag'] && $row['ct_notax']) echo '[비과세상품]'; ?>
                </td>
                <?php } ?>
                <td><?php echo $opt['ct_option']; ?></td>
                <td class="td_mngsmall"><?php echo $opt['ct_status']; ?></td>
                <td class="td_num"><?php echo number_format($opt['ct_qty']); ?></td>
                <td class="td_num"><?php echo number_format($opt_price); ?></td>
                <td class="td_num"><?php echo number_format($ct_price['stotal']); ?></td>
                <td class="td_num"><?php echo number_format($opt_cp_price); ?></td>
                <td class="td_num"><?php echo number_format($ct_point['stotal']); ?></td>
                <td class="td_sendcost_by"><?php echo $ct_send_cost; ?></td>
                <td class="td_mngsmall"><?php echo get_yn($opt['ct_point_use']); ?></td>
                <td class="td_mngsmall"><?php echo get_yn($opt['ct_stock_use']); ?></td>
            </tr>
            <?php
            }
            ?>
        <?php
        }
        ?>
        </tbody>
        </table>
    </div>
</section>

<section id="anc_sodr_pay">
    <h2 class="h2_frm">주문결제 내역</h2>
    <?php echo $pg_anchor; ?>

    <?php
    // 주문금액 = 상품구입금액 + 배송비 + 추가배송비
    $amount['order'] = $tot_ct_price + $od_send_cost + $od_send_cost2;

    // 입금액
    $amount['receipt'] = $od_temp_point;

    // 쿠폰금액
    $amount['coupon'] = $tot_cp_price + $tot_sc_cp_price;

    // 취소금액
    $amount['cancel'] = 0;

    // 미수금 = 주문금액 - 취소금액 - 입금금액 - 쿠폰금액
    $amount['misu'] = $amount['order'] - $amount['receipt'] - $amount['coupon'];

    // 결제방법
    $s_receipt_way = $data['od_settle_case'];

    if($data['od_settle_case'] == '간편결제') {
        switch($od['dt_pg']) {
            case 'lg':
                $s_receipt_way = 'PAYNOW';
                break;
            case 'inicis':
                $s_receipt_way = 'KPAY';
                break;
            case 'kcp':
                $s_receipt_way = 'PAYCO';
                break;
            default:
                $s_receipt_way = $data['od_settle_case'];
                break;
        }
    }

    if ($od_temp_point > 0)
        $s_receipt_way .= "+포인트";
    ?>

    <div class="tbl_head01 tbl_wrap">
        <form name="frmorderform" method="post" action="./inorderformupdate.php" onsubmit="return form_submit(this);">
        <input type="hidden" name="od_id" value="<?php echo $od_id; ?>">
        <input type="hidden" name="sst" value="<?php echo $sst; ?>">
        <input type="hidden" name="sod" value="<?php echo $sod; ?>">
        <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
        <input type="hidden" name="stx" value="<?php echo $stx; ?>">
        <input type="hidden" name="page" value="<?php echo $page; ?>">

        <strong class="sodr_nonpay">미수금 <?php echo display_price($amount['misu']); ?></strong>

        <table>
        <caption>주문결제 내역</caption>
        <thead>
        <tr>
            <th scope="col">주문번호</th>
            <th scope="col">결제방법</th>
            <th scope="col">주문총액</th>
            <th scope="col">배송비</th>
            <th scope="col">포인트결제</th>
            <th scope="col">총결제액</th>
            <th scope="col">쿠폰</th>
            <th scope="col">주문취소</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?php echo $od['od_id']; ?></td>
            <td class="td_paybybig"><?php echo $s_receipt_way; ?></td>
            <td class="td_numbig td_numsum"><?php echo display_price($amount['order']); ?></td>
            <td class="td_numbig"><?php echo display_price($od_send_cost + $od_send_cost2); ?></td>
            <td class="td_numbig"><?php echo display_point($od_temp_point); ?></td>
            <td class="td_numbig td_numincome"><?php echo number_format($amount['receipt']); ?>원</td>
            <td class="td_numbig td_numcoupon"><?php echo display_price($amount['coupon']); ?></td>
            <td class="td_numbig td_numcancel"><?php echo number_format($amount['cancel']); ?>원</td>
        </tr>
        </tbody>
        </table>

        <div class="btn_confirm01 btn_confirm">
            <input type="submit" value="주문 복구" class="btn_submit">
        </div>
        </form>
    </div>
</section>

<section>

    <?php
    // 이니시스를 사용하고 있다면
    if( $default['de_pg_service'] === 'inicis' && empty($default['de_card_test']) ){
        $sql = " select * from {$g5['g5_shop_inicis_log_table']} where P_TID <> '' and P_TYPE in ('CARD', 'ISP', 'BANK') and P_MID <> '' and P_STATUS = '00' and oid = '".$od['od_id']."' ";
        $results = sql_query($sql);

        $tmps = array();

        while( $tmp=sql_fetch_array($results) ){

            $sql = " select od_id from {$g5['g5_shop_order_table']} where od_id = '".$tmp['oid']."' and od_tno = '".$tmp['P_TID']."' ";
            $exist_od = sql_fetch($sql);

            if( $exist_od['od_id'] ) continue;

            $sql = " select pp_id from {$g5['g5_shop_personalpay_table']} where pp_id = '".$tmp['oid']."' and pp_tno = '".$tmp['P_TID']."' ";
            $exist_od = sql_fetch($sql);

            if( $exist_od['od_id'] ) continue;

            $tmps[] = $tmp;
        }

        if( $tmps ) {
    ?>
    <h2 class="h2_frm">이니시스 결제 로그</h2>
    <div class="local_desc01 local_desc">
        <p>실결제로 결제된 경우 반드시 이니시스 상점 관리자에서 해당 결제건을 확인 후에 주문을 처리해 주세요.</p>
    </div>
    <div class="tbl_head01 tbl_wrap">
        <table>
        <caption>이니시스 결제 로그</caption>
        <tbody>
        <?php foreach( $tmps as $tmp ){
            if( empty($tmp) ) continue;
        ?>
        <tr>
            <th>주문번호</th>
            <td><?php echo $tmp['oid']; ?></td>
        </tr>
        <tr>
            <th>결제 TID</th>
            <td><?php echo $tmp['P_TID']; ?></td>
        </tr>
        <tr>
            <th>결제 MID</th>
            <td><?php echo $tmp['P_MID']; ?><?php echo in_array( strtolower($tmp['P_MID']), array('iniescrow0', 'inipaytest') ) ? ' ( 테스트결제 )' : ''; ?></td>
        </tr>
        <tr>
            <th>결제 시간</th>
            <td><?php echo date('Y-m-d H:i:s', strtotime(substr($tmp['P_AUTH_DT'], 0, 14))); ?></td>
        </tr>
        <tr>
            <th>결제 수단</th>
            <td><?php echo $tmp['P_TYPE'].' '.$tmp['P_FN_NM']; ?></td>
        </tr>
        <tr>
            <th>결제된 금액</th>
            <td><?php echo $tmp['P_AMT'] ? number_format($tmp['P_AMT']) : 0; ?></td>
        </tr>
        <?php }     //end foreach ?>
        </tbody>
        </table>
    </div>
    <?php
        }   //end if tmps
    }     //end if inicis
    ?>

    <h2 class="h2_frm">주문자/배송지 정보</h2>
    <?php echo $pg_anchor; ?>

    <div class="compare_wrap">

        <section id="anc_sodr_orderer" class="compare_left">
            <h3>주문하신 분</h3>

            <div class="tbl_frm01">
                <table>
                <caption>주문자/배송지 정보</caption>
                <colgroup>
                    <col class="grid_4">
                    <col>
                </colgroup>
                <tbody>
                <tr>
                    <th scope="row"><span class="sound_only">주문하신 분 </span>이름</th>
                    <td><?php echo get_text($data['od_name']); ?></td>
                </tr>
                <tr>
                    <th scope="row"><span class="sound_only">주문하신 분 </span>전화번호</th>
                    <td><?php echo get_text($data['od_tel']); ?></td>
                </tr>
                <tr>
                    <th scope="row"><span class="sound_only">주문하신 분 </span>핸드폰</th>
                    <td><?php echo get_text($data['od_hp']); ?></td>
                </tr>
                <tr>
                    <th scope="row"><span class="sound_only">주문하시는 분 </span>주소</th>
                    <td>
                        <span><?php echo $data['od_zip']; ?></span>
                        <span><?php echo get_text($data['od_addr1']); ?></span>
                        <span><?php echo get_text($data['od_addr2']); ?></span>
                        <span><?php echo get_text($data['od_addr3']); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><span class="sound_only">주문하신 분 </span>E-mail</th>
                    <td><?php echo get_text($data['od_email']); ?></td>
                </tr>
                </tbody>
                </table>
            </div>
        </section>

        <section id="anc_sodr_taker" class="compare_right">
            <h3>받으시는 분</h3>

            <div class="tbl_frm01">
                <table>
                <caption>받으시는 분 정보</caption>
                <colgroup>
                    <col class="grid_4">
                    <col>
                </colgroup>
                <tbody>
                <tr>
                    <th scope="row"><span class="sound_only">받으시는 분 </span>이름</th>
                    <td><?php echo get_text($data['od_b_name']); ?></td>
                </tr>
                <tr>
                    <th scope="row"><span class="sound_only">받으시는 분 </span>전화번호</th>
                    <td><?php echo get_text($data['od_b_tel']); ?></td>
                </tr>
                <tr>
                    <th scope="row"><span class="sound_only">받으시는 분 </span>핸드폰</th>
                    <td><?php echo get_text($data['od_b_hp']); ?></td>
                </tr>
                <tr>
                    <th scope="row"><span class="sound_only">받으시는 분 </span>주소</th>
                    <td>
                        <span><?php echo $data['od_b_zip']; ?></span>
                        <span><?php echo get_text($data['od_b_addr1']); ?></span>
                        <span><?php echo get_text($data['od_b_addr2']); ?></span>
                        <span><?php echo get_text($data['od_b_addr3']); ?></span>
                    </td>
                </tr>

                <?php if ($default['de_hope_date_use']) { ?>
                <tr>
                    <th scope="row">희망배송일</th>
                    <td><?php echo $data['od_hope_date']; ?> (<?php echo get_yoil($data['od_hope_date']); ?>)</td>
                </tr>
                <?php } ?>
                <tr>
                    <th scope="row">전달 메세지</th>
                    <td><?php if ($data['od_memo']) echo get_text($data['od_memo'], 1);else echo "없음";?></td>
                </tr>
                </tbody>
                </table>
            </div>
        </section>
    </div>
</section>

<script>
function form_submit(f)
{
    if (!confirm("현재 미완료 주문을 입금완료 주문건으로 복구하시겠습니까?")) {
        return false;
    }

    return true;
}

function del_confirm()
{
    if(confirm("주문서를 삭제하시겠습니까?")) {
        return true;
    } else {
        return false;
    }
}
</script>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');