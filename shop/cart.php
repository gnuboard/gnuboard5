<?php
include_once('./_common.php');
$naverpay_button_js = '';
include_once(G5_SHOP_PATH.'/settle_naverpay.inc.php');

// 보관기간이 지난 상품 삭제
cart_item_clean();

$sw_direct = isset($_REQUEST['sw_direct']) ? (int) $_REQUEST['sw_direct'] : 0;

// cart id 설정
set_cart_id($sw_direct);

$s_cart_id = get_session('ss_cart_id');
// 선택필드 초기화
$sql = " update {$g5['g5_shop_cart_table']} set ct_select = '0' where od_id = '$s_cart_id' ";
sql_query($sql);

$cart_action_url = G5_SHOP_URL.'/cartupdate.php';

if(function_exists('before_check_cart_price')) {
    before_check_cart_price($s_cart_id, true, true, true);
}

if (G5_IS_MOBILE) {
    include_once(G5_MSHOP_PATH.'/cart.php');
    return;
}

// 테마에 cart.php 있으면 include
if(defined('G5_THEME_SHOP_PATH')) {
    $theme_cart_file = G5_THEME_SHOP_PATH.'/cart.php';
    if(is_file($theme_cart_file)) {
        include_once($theme_cart_file);
        return;
        unset($theme_cart_file);
    }
}

$g5['title'] = '장바구니';
include_once('./_head.php');
?>

<!-- 장바구니 시작 { -->
<script src="<?php echo G5_JS_URL; ?>/shop.js?ver=<?php echo G5_JS_VER; ?>"></script>
<script src="<?php echo G5_JS_URL; ?>/shop.override.js?ver=<?php echo G5_JS_VER; ?>"></script>

<div id="sod_bsk" class="od_prd_list">

    <form name="frmcartlist" id="sod_bsk_list" class="2017_renewal_itemform" method="post" action="<?php echo $cart_action_url; ?>">
    <div class="tbl_head03 tbl_wrap">
        <table>
        <thead>
        <tr>
            <th scope="col" class="chk_box">
                <input type="checkbox" name="ct_all" value="1" id="ct_all" checked="checked" class="selec_chk">
                <label for="ct_all"><span></span><b class="sound_only">상품 전체</b></label>
            </th>
            <th scope="col">상품명</th>
            <th scope="col">총수량</th>
            <th scope="col">판매가</th>
            <th scope="col">포인트</th>
            <th scope="col">배송비</th>
            <th scope="col">소계</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $tot_point = 0;
        $tot_sell_price = 0;
        $send_cost = 0;

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
                        b.ca_id3
                   from {$g5['g5_shop_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
                  where a.od_id = '$s_cart_id' ";
        $sql .= " group by a.it_id ";
        $sql .= " order by a.ct_id ";
        $result = sql_query($sql);

        $it_send_cost = 0;

        for ($i=0; $row=sql_fetch_array($result); $i++)
        {
            // 합계금액 계산
            $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
                            SUM(ct_point * ct_qty) as point,
                            SUM(ct_qty) as qty
                        from {$g5['g5_shop_cart_table']}
                        where it_id = '{$row['it_id']}'
                          and od_id = '$s_cart_id' ";
            $sum = sql_fetch($sql);

            if ($i==0) { // 계속쇼핑
                $continue_ca_id = $row['ca_id'];
            }

            $a1 = '<a href="'.shop_item_url($row['it_id']).'" class="prd_name"><b>';
            $a2 = '</b></a>';
            $image = get_it_image($row['it_id'], 80, 80);

            $it_name = $a1 . stripslashes($row['it_name']) . $a2;
            $it_options = print_item_options($row['it_id'], $s_cart_id);
            if($it_options) {
                $mod_options = '<div class="sod_option_btn"><button type="button" class="mod_options">선택사항수정</button></div>';
                $it_name .= '<div class="sod_opt">'.$it_options.'</div>';
            }

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
                $sendcost = get_item_sendcost($row['it_id'], $sum['price'], $sum['qty'], $s_cart_id);

                if($sendcost == 0)
                    $ct_send_cost = '무료';
            }

            $point      = $sum['point'];
            $sell_price = $sum['price'];
        ?>

        <tr>
            <td class="td_chk chk_box">
                <input type="checkbox" name="ct_chk[<?php echo $i; ?>]" value="1" id="ct_chk_<?php echo $i; ?>" checked="checked" class="selec_chk">
            	<label for="ct_chk_<?php echo $i; ?>"><span></span><b class="sound_only">상품</b></label>
            </td> 
            
            <td class="td_prd">
                <div class="sod_img"><a href="<?php echo shop_item_url($row['it_id']); ?>"><?php echo $image; ?></a></div>
                <div class="sod_name">
                    <input type="hidden" name="it_id[<?php echo $i; ?>]" value="<?php echo $row['it_id']; ?>">
                    <input type="hidden" name="it_name[<?php echo $i; ?>]" value="<?php echo get_text($row['it_name']); ?>">
                    <?php echo $it_name.$mod_options; ?>
                </div>
            </td>
            <td class="td_num"><?php echo number_format($sum['qty']); ?></td>
            <td class="td_numbig"><?php echo number_format($row['ct_price']); ?></td>
            <td class="td_numbig"><?php echo number_format($point); ?></td>
            <td class="td_dvr"><?php echo $ct_send_cost; ?></td>
            <td class="td_numbig text_right"><span id="sell_price_<?php echo $i; ?>" class="total_prc"><?php echo number_format($sell_price); ?></span></td>
        </tr>

        <?php
            $tot_point      += $point;
            $tot_sell_price += $sell_price;
        } // for 끝

        if ($i == 0) {
            echo '<tr><td colspan="7" class="empty_table">장바구니에 담긴 상품이 없습니다.</td></tr>';
        } else {
            // 배송비 계산
            $send_cost = get_sendcost($s_cart_id, 0);
        }
        ?>
        </tbody>
        </table>
        <div class="btn_cart_del">
            <button type="button" onclick="return form_check('seldelete');">선택삭제</button>
            <button type="button" onclick="return form_check('alldelete');">비우기</button>
        </div>
    </div>

    <?php
    $tot_price = $tot_sell_price + $send_cost; // 총계 = 주문상품금액합계 + 배송비
    if ($tot_price > 0 || $send_cost > 0) {
    ?>
    <div id="sod_bsk_tot">
        <ul>
            <li class="sod_bsk_dvr">
                <span>배송비</span>
                <strong><?php echo number_format($send_cost); ?></strong> 원
            </li>

            <li class="sod_bsk_pt">
                <span>포인트</span>
                <strong><?php echo number_format($tot_point); ?></strong> 점
            </li>

            <li class="sod_bsk_cnt">
                <span>총계 가격</span>
                <strong><?php echo number_format($tot_price); ?></strong> 원 
            </li>
        </ul>
    </div>
    <?php } ?>

    <div id="sod_bsk_act">
        <?php if ($i == 0) { ?>
        <a href="<?php echo G5_SHOP_URL; ?>/" class="btn01">쇼핑 계속하기</a>
        <?php } else { ?>
        <input type="hidden" name="url" value="./orderform.php">
        <input type="hidden" name="records" value="<?php echo $i; ?>">
        <input type="hidden" name="act" value="">
        <a href="<?php echo shop_category_url($continue_ca_id); ?>" class="btn01">쇼핑 계속하기</a>
        <button type="button" onclick="return form_check('buy');" class="btn_submit">주문하기</button>

        <?php if ($naverpay_button_js) { ?>
        <div class="cart-naverpay"><?php echo $naverpay_request_js.$naverpay_button_js; ?></div>
        <?php } ?>
        <?php } ?>
    </div>
    </form>
</div>

<script>
$(function() {
    var close_btn_idx;

    // 선택사항수정
    $(".mod_options").click(function() {
        var it_id = $(this).closest("tr").find("input[name^=it_id]").val();
        var $this = $(this);
        close_btn_idx = $(".mod_options").index($(this));

        $.post(
            "./cartoption.php",
            { it_id: it_id },
            function(data) {
                $("#mod_option_frm").remove();
                $this.after("<div id=\"mod_option_frm\"></div><div class=\"mod_option_bg\"></div>");
                $("#mod_option_frm").html(data);
                price_calculate();
            }
        );
    });

    // 모두선택
    $("input[name=ct_all]").click(function() {
        if($(this).is(":checked"))
            $("input[name^=ct_chk]").attr("checked", true);
        else
            $("input[name^=ct_chk]").attr("checked", false);
    });

    // 옵션수정 닫기
    $(document).on("click", "#mod_option_close", function() {
        $("#mod_option_frm, .mod_option_bg").remove();
        $(".mod_options").eq(close_btn_idx).focus();
    });
    $("#win_mask").click(function () {
        $("#mod_option_frm").remove();
        $(".mod_options").eq(close_btn_idx).focus();
    });

});

function fsubmit_check(f) {
    if($("input[name^=ct_chk]:checked").length < 1) {
        alert("구매하실 상품을 하나이상 선택해 주십시오.");
        return false;
    }

    return true;
}

function form_check(act) {
    var f = document.frmcartlist;
    var cnt = f.records.value;

    if (act == "buy")
    {
        if($("input[name^=ct_chk]:checked").length < 1) {
            alert("주문하실 상품을 하나이상 선택해 주십시오.");
            return false;
        }

        f.act.value = act;
        f.submit();
    }
    else if (act == "alldelete")
    {
        f.act.value = act;
        f.submit();
    }
    else if (act == "seldelete")
    {
        if($("input[name^=ct_chk]:checked").length < 1) {
            alert("삭제하실 상품을 하나이상 선택해 주십시오.");
            return false;
        }

        f.act.value = act;
        f.submit();
    }

    return true;
}
</script>
<!-- } 장바구니 끝 -->

<?php
include_once('./_tail.php');