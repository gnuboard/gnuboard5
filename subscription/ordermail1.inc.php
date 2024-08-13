<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

unset($list);

$ttotal_price = 0;
$ttotal_point = 0;

//==============================================================================
// 메일보내기
//------------------------------------------------------------------------------
// Loop 배열 자료를 만들고
$sql = " select a.it_id,
                a.it_name,
                a.ct_qty,
                a.ct_price,
                a.ct_point,
                b.it_sell_email,
                b.it_origin
           from {$g5['g5_shop_cart_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
          where a.od_id = '$od_id'
            and a.ct_select = '1'
          group by a.it_id
          order by a.ct_id asc ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    // 합계금액 계산
    $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
                    SUM(ct_point * ct_qty) as point,
                    SUM(ct_qty) as qty
                from {$g5['g5_shop_cart_table']}
                where it_id = '{$row['it_id']}'
                  and od_id = '$od_id'
                  and ct_select = '1' ";
    $sum = sql_fetch($sql);

    // 옵션정보
    $sql2 = " select ct_option, ct_qty, io_price
                from {$g5['g5_shop_cart_table']}
                where it_id = '{$row['it_id']}' and od_id = '$od_id' and ct_select = '1'
                order by io_type asc, ct_id asc ";
    $result2 = sql_query($sql2);

    $options = '';
    $options_ul = ' style="margin:0;padding:0"'; // ul style
    $options_li = ' style="padding:5px 0;list-style:none"'; // li style
    for($k=0; $row2=sql_fetch_array($result2); $k++) {
        if($k == 0)
            $options .= '<ul'.$options_ul.'>'.PHP_EOL;
        $price_plus = '';
        if($row2['io_price'] >= 0)
            $price_plus = '+';
        $options .= '<li'.$options_li.'>'.$row2['ct_option'].' ('.$price_plus.display_price($row2['io_price']).') '.$row2['ct_qty'].'개</li>'.PHP_EOL;
    }

    if($k > 0)
        $options .= '</ul>';

    $list[$i]['g_dir']         = G5_URL;
    $list[$i]['it_id']         = $row['it_id'];
    $list[$i]['it_simg']       = get_it_image($row['it_id'], 70, 70);
    $list[$i]['it_name']       = $row['it_name'];
    $list[$i]['it_origin']     = $row['it_origin'];
    $list[$i]['it_opt']        = $options;
    $list[$i]['ct_price']      = $row['ct_price'];
    $list[$i]['stotal_price']  = $sum['price'];
    $list[$i]['stotal_point']  = $sum['point'];

    $ttotal_price  += $list[$i]['stotal_price'];
    $ttotal_point  += $list[$i]['stotal_point'];
}
//------------------------------------------------------------------------------

// 배송비가 있다면 총계에 더한다
if ($od_send_cost)
    $ttotal_price += $od_send_cost;

// 추가배송비가 있다면 총계에 더한다
if ($od_send_cost2)
    $ttotal_price += $od_send_cost2;