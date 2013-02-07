<?php
include_once('./_common.php');

if(!$is_member) {
    alert_close('회원 로그인 후 이용해 주세요.');
}

// 상품정보
$sql = " select it_id, it_nocoupon, ca_id, ca_id2, ca_id3, it_notax
            from {$g4['shop_item_table']}
            where it_id = '$it_id' ";
$it = sql_fetch($sql);

if(!$it['it_id']) {
    alert_close('상품정보가 존재하지 않습니다.');
}

if($it['it_nocoupon']) {
    alert_close('쿠폰 사용이 제한된 상품입니다.');
}

// 카테고리쿠폰설정체크
$no = '';
for($i=0; $i<3; $i++) {
    if($i > 0) {
        $no = $i + 1;
    }

    $ca_id = $it["ca_id$no"];
    $sql = " select ca_nocoupon from {$g4['shop_category_table']} where ca_id = '$ca_id' ";
    $row = sql_fetch($sql);

    if($row['ca_nocoupon']) {
        alert_close('쿠폰사용이 제한된 카테고리에 속한 상품입니다.');
    }
}

// 장바구니정보
$uq_id = get_session('ss_uniqid');

if($sw_direct != 1)
    $sw_direct = 0;

$sql_common = " from ( select * from {$g4['shop_cart_table']}
                          where uq_id = '$uq_id'
                            and it_id = '$it_id'
                            and ct_direct = '$sw_direct'
                          order by ct_id asc ) as a ";

$sql = " select a.ct_id, a.ct_send_cost_pay,
            SUM((a.ct_amount + a.it_amount) * a.ct_qty) as item_amount,
            SUM(a.ct_qty) as item_qty
            $sql_common
            group by a.it_id ";
$ct = sql_fetch($sql);

// 상품총금액
if($ct['item_amount']) {
    $item_amount = $ct['item_amount'];
} else {
    alert_close('상품의 주문금액이 0원입니다.');
}

// 총주문금액
$sql = " select SUM((ct_amount + it_amount) * ct_qty) as total_amount
            from {$g4['shop_cart_table']}
            where uq_id = '$uq_id'
              and ct_direct = '$sw_direct' ";
$row = sql_fetch($sql);

if($row['total_amount']) {
    $total_amount = $row['total_amount'];
} else {
    alert_close('주문하신 상품이 없습니다.');
}

// 배송비 계산
if ($default['de_send_cost_case'] == "없음" || $default['de_send_cost_case'] == "착불")
    $send_cost = 0;
else if($default['de_send_cost_case'] == "상한") {
    // 배송비 상한 : 여러단계의 배송비 적용 가능
    $send_cost_limit = explode(";", $default['de_send_cost_limit']);
    $send_cost_list  = explode(";", $default['de_send_cost_list']);
    $send_cost = 0;
    for ($k=0; $k<count($send_cost_limit); $k++) {
        // 총판매금액이 배송비 상한가 보다 작다면
        if ($total_amount < $send_cost_limit[$k]) {
            $send_cost = $send_cost_list[$k];
            break;
        }
    }
} else if($default['de_send_cost_case'] == "개별배송") {
    $send_cost = 0;

    $sql = " select a.ct_id,
                    a.it_id,
                    a.ct_send_cost_pay,
                    SUM((a.ct_amount + a.it_amount) * a.ct_qty) as sum_amount,
                    SUM(a.ct_qty) as sum_qty,
                    b.it_sc_type,
                    b.it_sc_basic,
                    b.it_sc_condition
               from ( select * from {$g4['shop_cart_table']} where uq_id = '$uq_id' and ct_direct = '$sw_direct' order by ct_id asc ) as a,
                    {$g4['shop_item_table']} b
              where a.it_id  = b.it_id
                group by a.it_id
                order by a.ct_id ";
    $result = sql_query($sql);

    for($i=0; $row=sql_fetch_array($result); $i++) {
        if($row['ct_send_cost_pay'] == "착불") {
            $send_cost += 0;
        } else {
            if($row['it_sc_type'] == 1) { // 조건부무료
                if($row['sum_amount'] >= $row['it_sc_condition']) {
                    $send_cost += 0;
                } else {
                    $send_cost += $row['it_sc_basic'];
                }
            } else if($row['it_sc_type'] == 2) { // 유료
                $send_cost += $row['it_sc_basic'];
            } else if($row['it_sc_type'] == 3) { // 수량별부과
                $qty = ceil($row['sum_qty'] / $row['it_sc_condition']);
                $send_cost += ($row['it_sc_basic'] * $qty);
            } else {
                $send_cost += 0;
            }
        }
    }
}

// 쿠폰정보
$sql = " select *
            from {$g4['shop_coupon_table']}
            where cp_use = '1'
              and cp_type = '0'
              and cp_start <= '{$g4['time_ymd']}'
              and cp_end >= '{$g4['time_ymd']}'
              and ( it_id  = '{$it['it_id']}' or cp_target = '2' )
              and mb_id in ( '{$member['mb_id']}', '전체회원' )
              and ca_id in ( '{$it['ca_id']}', '{$it['ca_id2']}', '{$it['ca_id3']}', '전체카테고리' )
            order by cp_no asc ";
$result = sql_query($sql);

if(!mysql_num_rows($result)) {
    alert_close('쿠폰 정보가 존재하지 않습니다.');
}

$coupon_list = '<li><input type="radio" name="cp_id[]" value="" checked="checked" />적용안함</li>';

$cnt = 0;
for($i=0; $row=sql_fetch_array($result); $i++) {
    // 정액할인쿠폰에서 할인금액이 상품주문금액보다 크다면
    if(!$row['cp_method'] && $row['cp_amount'] > $item_amount) {
        continue;
    }

    // 쿠폰사용내역체크
    $sql = " select ch_no
                from {$g4['shop_coupon_history_table']}
                where cp_id = '{$row['cp_id']}'
                  and it_id = '$it_id'
                  and mb_id = '{$member['mb_id']}'
                  and uq_id <> '$uq_id' ";
    $ch = sql_fetch($sql);
    if($ch['ch_no']) { // 이미 사용한 쿠폰
        continue;
    } else {
        $cnt++;
    }

    $cp_limit = $row['cp_limit'];
    if($row['cp_id'] == $coupon) {
        $checked = ' checked="checked"';
    } else {
        $checked = '';
    }
    $coupon_list .= '<li>'."\n";
    $coupon_list .= '<input type="hidden" name="cp_method[]" value="'.$row['cp_method'].'" />'."\n";
    $coupon_list .= '<input type="hidden" name="cp_maximum[]" value="'.$row['cp_maximum'].'" />'."\n";
    $coupon_list .= '<input type="hidden" name="cp_trunc[]" value="'.$row['cp_trunc'].'" />'."\n";
    $coupon_list .= '<input type="hidden" name="cp_amount[]" value="'.$row['cp_amount'].'" />'."\n";
    $coupon_list .= '<input type="radio" name="cp_id[]" value="'.$row['cp_id'].'"'.$checked.' />'."\n".$row['cp_subject']."\n";
    $coupon_list .= '</li>'."\n";
}

if(!$cnt) {
    alert_close('사용할 수 있는 쿠폰이 없습니다.');
}

$g4['title'] = '쿠폰적용';
include_once ($g4['path'].'/head.sub.php');
?>

<style type="text/css">
<!--
#container { width: 540px; margin: 0 auto; }
form { display: inline; }
ul { margin: 0; padding: 0; list-style: none; }
li { height: 20px; }
-->
</style>

<table cellpadding="0" cellspacing="0" border="0">
<tr>
    <td height="40" align="center">쿠폰선택</td>
    <td colspan="2">
        <ul>
            <? echo $coupon_list; ?>
        </ul>
    </td>
</tr>
<tr>
    <td width="180" height="20" align="center">상품금액</td>
    <td width="180" align="center">할인금액</td>
    <td width="180" align="center">최종결제금액</td>
</tr>
<tr>
    <td height="30" align="center"><?php echo number_format($item_amount); ?>원</td>
    <td align="center"><span id="dc_amount">0</span>원</td>
    <td align="center"><span id="res_amount"><?php echo number_format($item_amount); ?></span>원</td>
</tr>
<tr>
    <td colspan="3" height="50" align="right"><button type="button" id="couponapply">적용</button></td>
</tr>
</table>

<script>
$(function() {
    var $opener = window.opener;
    var result = dc = 0;
    var total_amount = parseInt(<? echo $total_amount; ?>);
    var send_cost = parseInt(<? echo $send_cost; ?>);
    var area_send_cost = parseInt($opener.$("input[name=od_send_cost_area]").val());
    var item_amount = parseInt(<? echo $item_amount; ?>);
    var item_dc_amount = 0;
    var notax = <? echo $it['it_notax']; ?>;

    // 쿠폰할인총금액
    var total_dc_amount = 0;
    var val;
    $opener.$("input[name^=ch_amount]").each(function(index) {
        val = $(this).val();

        if(val == "") {
            val = 0;
        } else {
            val = parseInt(val);
        }

        total_dc_amount += val;

        // 쿠폰적용 상품의 기존 쿠폰적용 금액
        if(index == parseInt(<? echo $idx; ?>)) {
            item_dc_amount = val;
        }
    });

    // 쿠폰할인금액적용
    $("#dc_amount").text(number_format(String(item_dc_amount)));
    $("#res_amount").text(number_format(String(item_amount - item_dc_amount)));

    $("input[name^=cp_id]").click(function() {
        var $li = $(this).closest("li");
        var val = $(this).val();

        if(val != "") {
            var cp_method = parseInt($li.find("input[name^=cp_method]").val());
            var cp_amount = parseInt($li.find("input[name^=cp_amount]").val());
            var cp_maximum = parseInt($li.find("input[name^=cp_maximum]").val());
            var cp_trunc = parseInt($li.find("input[name^=cp_trunc]").val());

            // 할인금액
            if(cp_method == 1) {
                dc = Math.floor(((item_amount * (cp_amount / 100)) / cp_trunc)) * cp_trunc;
                if(dc > cp_maximum) { // 최대할인금액보다 크다면
                    dc = cp_maximum;
                }
            } else {
                dc = cp_amount;
            }
        } else {
            dc = 0;
        }

        // 할인금액이 상품금액보다 크다면
        if(dc > item_amount) {
            alert("할인금액이 주문금액보다 크기때문에 사용할 수 없는 쿠폰입니다.");
            $("input[name^=cp_id]:checked").attr("checked", true);
            return false;
        }

        result = item_amount - dc;

        $("#dc_amount").text(number_format(String(dc)));
        $("#res_amount").text(number_format(String(result)));
    });

    $("#couponapply").click(function() {
        var tot_amount = total_amount - (total_dc_amount - item_dc_amount) + send_cost + area_send_cost - dc;
        var cp_id = $("input[name^=cp_id]:checked").val();
        if(cp_id == "") {
            result = item_amount;
            tot_amount = total_amount - (total_dc_amount - item_dc_amount) + send_cost + area_send_cost;
        }

        $opener.$("input[name^=cp_id]").eq(<? echo $idx; ?>).val(cp_id);
        $opener.$("input[name^=ch_amount]").eq(<? echo $idx; ?>).val(dc);
        $opener.$(".sell_amount").eq(<? echo $idx; ?>).text(number_format(String(result)));
        $opener.$("#tot_amount b").text(number_format(String(tot_amount)));
        $opener.$("input[name^=od_cp_id]").eq(<? echo $idx; ?>).val(cp_id);
        $opener.$("input[name^=od_ch_amount]").eq(<? echo $idx; ?>).val(dc);
        $opener.$("input[name=good_mny]").val(tot_amount);

        // 결제, 배송비할인 쿠폰 초기화
        $opener.$("span#send_cost").text(number_format(String(send_cost)));
        $opener.$("input[name=od_coupon]").val("");
        $opener.$("input[name=od_coupon_amount]").val(0);
        $opener.$("input[name=od_send_coupon]").val("");
        $opener.$("input[name=od_send_coupon_amount]").val(0);
        $opener.$("select[name=s_cp_id]").val("");
        $opener.$("select[name=o_cp_id]").val("");

        <? if($default['de_compound_tax_use']) { ?>
        // 과세, 면세금액
        var tax_mny = vat_mny = free_mny = 0;
        if(notax == 1) { // 면세
            free_mny = result;
        } else { // 과세
            tax_mny = Math.round(result / 1.1);
            vat_mny = result - tax_mny;
        }

        $opener.$("input[name^=tax_mny]").eq(<? echo $idx; ?>).val(tax_mny);
        $opener.$("input[name^=vat_mny]").eq(<? echo $idx; ?>).val(vat_mny);
        $opener.$("input[name^=free_mny]").eq(<? echo $idx; ?>).val(free_mny);

        // 과세, 면세 금액 합
        var $t_el = $opener.$("input[name^=tax_mny]");
        var $v_el = $opener.$("input[name^=vat_mny]");
        var $f_el = $opener.$("input[name^=free_mny]");
        var comm_tax = comm_vat = comm_free = 0;

        $t_el.each(function(index) {
            var t_val = parseInt($(this).val());
            var v_val = parseInt($v_el.eq(index).val());
            var f_val = parseInt($f_el.eq(index).val());

            comm_tax += t_val;
            comm_vat += v_val;
            comm_free += f_val;
        });

        // 배송비과세계산
        if(send_cost > 0) {
            var s_tax = Math.round(send_cost / 1.1);
            var s_vat = send_cost - s_tax;

            comm_tax += s_tax;
            comm_vat += s_vat;
        }

        // 추가배송비계산
        if(area_send_cost > 0) {
            var as_tax = Math.round(area_send_cost / 1.1);
            var as_vat = area_send_cost - as_tax;

            comm_tax += as_tax;
            comm_vat += as_vat;
        }

        $opener.$("input[name=comm_tax_mny]").val(comm_tax);
        $opener.$("input[name=comm_vat_mny]").val(comm_vat);
        $opener.$("input[name=comm_free_mny]").val(comm_free);
        <? } ?>

        self.close();
    });
});
</script>

<?php
include_once($g4['path'] . '/tail.sub.php');
?>