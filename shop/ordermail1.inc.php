<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

unset($list);

$ttotal_amount = 0;
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
           from {$g4['shop_cart_table']} a left join {$g4['shop_item_table']} b on ( a.it_id = b.it_id )
          where a.uq_id = '$tmp_uq_id'
            and a.ct_num = '0' ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    // 합계금액 계산
    $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
                    SUM(ct_point * ct_qty) as point,
                    SUM(ct_qty) as qty
                from {$g4['shop_cart_table']}
                where it_id = '{$row['it_id']}'
                  and uq_id = '$tmp_uq_id' ";
    $sum = sql_fetch($sql);

    // 옵션정보
    $sql2 = " select ct_option, ct_qty
                from {$g4['shop_cart_table']}
                where it_id = '{$row['it_id']}' and uq_id = '$tmp_uq_id'
                order by io_type asc, ct_num asc, ct_id asc ";
    $result2 = sql_query($sql2);

    $options = '';
    for($k=0; $row2=sql_fetch_array($result2); $k++) {
        if($k == 0)
            $options .= '<ul>'.PHP_EOL;
        $options .= '<li>'.$row2['ct_option'].' '.$row2['ct_qty'].'개</li>'.PHP_EOL;
    }

    if($k > 0)
        $options .= '</ul>';

    $list[$i]['g_dir']         = G4_URL;
    $list[$i]['it_id']         = $row['it_id'];
    $list[$i]['it_simg']       = get_it_image($row['it_id'], $default['de_simg_width'], $default['de_simg_height']);
    $list[$i]['it_name']       = $row['it_name'];
    $list[$i]['it_origin']     = $row['it_origin'];
    $list[$i]['it_opt']        = $options;
    $list[$i]['ct_price']      = $row['ct_price'];
    $list[$i]['stotal_amount'] = $sum['price'];
    $list[$i]['stotal_point']  = $sum['point'];

    $ttotal_amount += $list[$i]['stotal_amount'];
    $ttotal_point  += $list[$i]['stotal_point'];
}
//------------------------------------------------------------------------------

// 배송비가 있다면 총계에 더한다
if ($od_send_cost)
    $ttotal_amount += $od_send_cost;
?>