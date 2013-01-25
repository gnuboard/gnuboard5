<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

unset($list);

$ttotal_amount = 0;
$ttotal_point = 0;

//==============================================================================
// 메일보내기
//------------------------------------------------------------------------------
// Loop 배열 자료를 만들고
$sql = " select b.it_sell_email,
                a.it_id, 
                b.it_name,
                b.it_origin,
                a.it_opt1,
                a.it_opt2,
                a.it_opt3,
                a.it_opt4,
                a.it_opt5,
                a.it_opt6,
                a.ct_qty,
                a.ct_amount,
                a.ct_point
           from $g4[yc4_cart_table] a, $g4[yc4_item_table] b
          where a.on_uid = '$tmp_on_uid' 
            and a.it_id = b.it_id ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $list[$i][g_dir]         = $g4[url];
    $list[$i][it_id]         = $row[it_id];
    $list[$i][it_simg]       = get_it_image("$row[it_id]_s", $default[de_simg_width], $default[de_simg_height]);
    $list[$i][it_name]       = $row[it_name];
    $list[$i][it_origin]     = $row[it_origin];
    $list[$i][it_opt]        = print_item_options($row[it_id], $row[it_opt1], $row[it_opt2], $row[it_opt3], $row[it_opt4], $row[it_opt5], $row[it_opt6]);
    $list[$i][ct_qty]        = $row[ct_qty];
    $list[$i][ct_amount]     = $row[ct_amount];
    $list[$i][stotal_amount] = $row[ct_amount] * $row[ct_qty];
    $list[$i][stotal_point]  = $row[ct_point] * $row[ct_qty];

    $ttotal_amount += $list[$i][stotal_amount];
    $ttotal_point  += $list[$i][stotal_point];
}
//------------------------------------------------------------------------------

// 배송비가 있다면 총계에 더한다
if ($od_send_cost)
    $ttotal_amount += $od_send_cost;
?>