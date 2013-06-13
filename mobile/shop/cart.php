<?php
include_once('./_common.php');

$g4['title'] = '장바구니';
include_once(G4_MSHOP_PATH.'/_head.php');
?>

<script src="<?php echo G4_JS_URL; ?>/shop.js"></script>

<div id="sod_bsk">

    <form name="frmcartlist" id="sod_bsk_list" method="post" action="<?php echo $cart_action_url; ?>">
    <table class="basic_tbl">
    <thead>
    <tr>
        <th scope="col">상품이미지</th>
        <th scope="col">상품명</th>
        <th scope="col">총수량</th>
        <th scope="col">판매가</th>
        <th scope="col">소계</th>
        <th scope="col">포인트</th>
        <th scope="col"><input type="checkbox" name="ct_all" value="1" checked="checked"></th>
    </tr>
    </thead>
    <tbody>
    <?php
    $tot_point = 0;
    $tot_sell_amount = 0;

    // $s_uq_id 로 현재 장바구니 자료 쿼리
    $sql = " select a.ct_id,
                    a.it_id,
                    a.it_name,
                    a.ct_price,
                    a.ct_point,
                    a.ct_qty,
                    a.ct_status,
                    a.ct_send_cost,
                    b.ca_id
               from {$g4['shop_cart_table']} a left join {$g4['shop_item_table']} b on ( a.it_id = b.it_id )
              where a.uq_id = '$s_uq_id'
                and a.ct_num = '0' ";
    if($default['de_cart_keep_term']) {
        $ctime = date('Y-m-d H:i:s', G4_SERVER_TIME - ($default['de_cart_keep_term'] * 86400));
        $sql .= " and a.ct_time > '$ctime' ";
    }
    $sql .= " order by a.ct_id ";
    $result = sql_query($sql);

    $it_send_cost = 0;

    for ($i=0; $row=mysql_fetch_array($result); $i++)
    {
        // 합계금액 계산
        $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
                        SUM(ct_point * ct_qty) as point,
                        SUM(ct_qty) as qty
                    from {$g4['shop_cart_table']}
                    where it_id = '{$row['it_id']}'
                      and uq_id = '$s_uq_id' ";
        $sum = sql_fetch($sql);

        if ($i==0) { // 계속쇼핑
            $continue_ca_id = $row['ca_id'];
        }

        $a1 = '<a href="./item.php?it_id='.$row['it_id'].'"><b>';
        $a2 = '</b></a>';
        $image = get_it_image($row['it_id'], 70, 70);

        $it_name = $a1 . stripslashes($row['it_name']) . $a2;
        $it_options = print_item_options($row['it_id'], $s_uq_id);
        if($it_options) {
            $mod_options = '<div class="sod_option_btn"><button type="button" class="mod_options">선택사항수정</button></div>';
            $it_name .= '<div class="sod_bsk_itopt">'.$it_options.'</div>';
        }

        // 개별배송비 계산
        if($default['de_send_cost_case'] == '개별' && !$row['ct_send_cost']) {
            $it_send_cost += get_item_sendcost($row['it_id'], $sum['price'], $sum['qty']);
        }

        $point       = $sum['point'];
        $sell_amount = $sum['price'];
    ?>

    <tr>
        <td class="sod_bsk_img"><?php echo $image; ?></td>
        <td>
            <input type="hidden" name="it_id[<?php echo $i; ?>]"    value="<?php echo $row['it_id']; ?>">
            <input type="hidden" name="it_name[<?php echo $i; ?>]"  value="<?php echo get_text($row['it_name']); ?>">
            <?php echo $it_name.$mod_options; ?>
        </td>

        <td class="td_num"><?php echo number_format($sum['qty']); ?></td>
        <td class="td_bignum"><?php echo number_format($row['ct_price']); ?></td>
        <td class="td_bignum"><?php echo number_format($sell_amount); ?></td>
        <td class="td_num"><?php echo number_format($sum['point']); ?></td>
        <td class="td_smallmng"><input type="checkbox" name="ct_chk[<?php echo $i; ?>]" value="1" checked="checked"></td>
    </tr>

    <?php
        $tot_point       += $point;
        $tot_sell_amount += $sell_amount;
    } // for 끝

    if ($i == 0) {
        echo '<tr><td colspan="7" class="empty_table">장바구니에 담긴 상품이 없습니다.</td></tr>';
    } else {
        // 배송비 계산
        if ($default['de_send_cost_case'] == '없음')
            $send_cost = 0;
        else if($default['de_send_cost_case'] == '상한') {
            // 배송비 상한 : 여러단계의 배송비 적용 가능
            $send_cost_limit = explode(";", $default['de_send_cost_limit']);
            $send_cost_list  = explode(";", $default['de_send_cost_list']);
            $send_cost = 0;
            for ($k=0; $k<count($send_cost_limit); $k++) {
                // 총판매금액이 배송비 상한가 보다 작다면
                if ($tot_sell_amount < preg_replace('/[^0-9]/', '', $send_cost_limit[$k])) {
                    $send_cost = preg_replace('/[^0-9]/', '', $send_cost_list[$k]);
                    break;
                }
            }
        } else {
            $send_cost = $it_send_cost;
        }
    }
    ?>
    </tbody>
    </table>

    <?php
    $tot_amount = $tot_sell_amount + $send_cost; // 총계 = 주문상품금액합계 + 배송비
    if ($tot_amount > 0 || $send_cost > 0) {
    ?>
    <dl id="sod_bsk_tot">
        <?php if ($send_cost > 0) { // 배송비가 0 보다 크다면 (있다면) ?>
        <dt class="sod_bsk_dvr">배송비</dt>
        <dd class="sod_bsk_dvr"><strong><?php echo number_format($send_cost); ?> 원</strong></dd>
        <?php } ?>

        <?php if ($tot_amount > 0) { ?>
        <dt class="sod_bsk_cnt">총계</dt>
        <dd class="sod_bsk_cnt"><strong><?php echo number_format($tot_amount); ?> 원 <?php echo number_format($tot_point); ?> 점</strong></dd>
        <?php } ?>
    </dl>
    <?php } ?>

    <div id="sod_bsk_act">
        <?php if ($i == 0) { ?>
        <a href="<?php echo G4_SHOP_URL; ?>/" class="btn01">쇼핑 계속하기</a>
        <?php } else { ?>
        <input type="hidden" name="url" value="<?php echo G4_SHOP_URL; ?>/orderform.php">
        <input type="hidden" name="act" value="">
        <input type="hidden" name="records" value="<?php echo $i; ?>">
        <p>장바구니의 상품을 주문하시려면 <strong>주문하기</strong>를 클릭하세요. <strong>비우기</strong>는 장바구니의 상품을 모두 비웁니다.</p>
        <a href="<?php echo G4_SHOP_URL; ?>/list.php?ca_id=<?php echo $continue_ca_id; ?>" class="btn01">쇼핑 계속하기</a>
        <button type="button" onclick="return form_check('buy');" class="btn02">주문하기</button>
        <button type="button" onclick="return form_check('seldelete');" class="btn01">선택삭제</button>
        <button type="button" onclick="return form_check('alldelete');" class="btn01">비우기</button>
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

        winMask(); // 모달 윈도우 배경 출력

        $.post(
            "./cartoption.php",
            { it_id: it_id },
            function(data) {
                $("#mod_option_frm").remove();
                $this.after("<div id=\"mod_option_frm\"></div>");
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
    $("#mod_option_close").live("click", function() {
        $("#mod_option_frm").remove();
        $("#win_mask, .window").hide();
        $(".mod_options").eq(close_btn_idx).focus();
    });
    $("#win_mask").click(function () {
        $("#mod_option_frm").remove();
        $("#win_mask").hide();
        $(".mod_options").eq(close_btn_idx).focus();
    });

});

function form_check(act) {
    var f = document.frmcartlist;
    var cnt = f.records.value;

    if (act == "buy")
    {
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
        if($("input[name^=ct_chk]:checked").size() < 1) {
            alert("삭제하실 상품을 하나이상 선택해 주십시오.");
            return false;
        }

        f.act.value = act;
        f.submit();
    }

    return true;
}
</script>

<?php
include_once(G4_MSHOP_PATH.'/_tail.php');
?>