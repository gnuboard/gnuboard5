<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if (isset($list)) {
    unset($list);
}

$ttotal_price = 0;
$ttotal_point = 0;

//==============================================================================
// 메일보내기
//------------------------------------------------------------------------------
// Loop 배열 자료를 만들고
$sql = " select a.it_id,
                a.it_name,
                a.pb_qty,
                a.pb_price,
                a.pb_point,
                b.it_sell_email,
                b.it_origin
           from {$g5['g5_subscription_pay_basket_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
          where a.pay_id = '$pay_id' and a.od_id = '$od_id' 
            and a.pb_select = '1'
          group by a.it_id
          order by a.pay_id asc ";
$result = sql_query($sql);

for ($i = 0; $row = sql_fetch_array($result); $i++) {
    // 합계금액 계산
    $sql = " select SUM(IF(io_type = 1, (io_price * pb_qty), ((pb_price + io_price) * pb_qty))) as price,
                    SUM(pb_point * pb_qty) as point,
                    SUM(pb_qty) as qty
                from {$g5['g5_subscription_pay_basket_table']}
                where it_id = '{$row['it_id']}'
                  and od_id = '$od_id' and pay_id = '$pay_id'
                  and pb_select = '1' ";
    $sum = sql_fetch($sql);

    // 옵션정보
    $sql = "SELECT pb_option, pb_qty, io_price 
            FROM {$g5['g5_subscription_pay_basket_table']} 
            WHERE it_id = '" . $row['it_id'] . "' 
            AND od_id = '$od_id' and pay_id = '$pay_id'
            AND pb_select = '1' 
            ORDER BY io_type ASC, pay_id ASC";
    $result2 = sql_query($sql);

    $options = '';
    $options_ul = ' style="margin:0;padding:0"'; // ul style
    $options_li = ' style="padding:5px 0;list-style:none"'; // li style
    for ($k = 0; $row2 = sql_fetch_array($result2); $k++) {
        if ($k == 0)
            $options .= '<ul' . $options_ul . '>' . PHP_EOL;
        $price_plus = '';
        if ($row2['io_price'] >= 0)
            $price_plus = '+';
        $options .= '<li' . $options_li . '>' . $row2['pb_option'] . ' (' . $price_plus . display_price($row2['io_price']) . ') ' . $row2['pb_qty'] . '개</li>' . PHP_EOL;
    }

    if ($k > 0)
        $options .= '</ul>';

    $list[$i]['it_id']         = $row['it_id'];
    $list[$i]['it_simg']       = get_it_image($row['it_id'], 70, 70);
    $list[$i]['it_name']       = $row['it_name'];
    $list[$i]['it_origin']     = $row['it_origin'];
    $list[$i]['it_opt']        = $options;
    $list[$i]['pb_price']      = $row['pb_price'];
    $list[$i]['stotal_price']  = $sum['price'];
    $list[$i]['stotal_point']  = $sum['point'];

    $ttotal_price  += $list[$i]['stotal_price'];
    $ttotal_point  += $list[$i]['stotal_point'];
}
//------------------------------------------------------------------------------

// 배송비가 있다면 총계에 더한다
if ($py_send_cost)
    $ttotal_price += (int) $py_send_cost;

// 추가배송비가 있다면 총계에 더한다
if ($py_send_cost2)
    $ttotal_price += (int) $py_send_cost2;
