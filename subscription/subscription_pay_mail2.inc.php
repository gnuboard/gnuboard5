<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

include_once(G5_LIB_PATH.'/mailer.lib.php');

//------------------------------------------------------------------------------
// 운영자에게 메일보내기
//------------------------------------------------------------------------------
/*
$subject = $config['cf_title'].' - 정기구독 결제 알림 메일 ('.$od_name.')';
ob_start();
include G5_SUBSCRIPTION_PATH.'/mail/orderupdate1.mail.php';
$content = ob_get_contents();
ob_end_clean();

mailer($od_name, $od_email, $config['cf_admin_email'], $subject, $content, 1);
*/

//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// 주문자에게 메일보내기
//------------------------------------------------------------------------------
$subject = $config['cf_title'].' - 정기구독 결제 내역 안내';
ob_start();
include G5_SUBSCRIPTION_PATH.'/mail/subscription_pay_mail2.inc.php';
$content = ob_get_contents();
ob_end_clean();

mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $od_email, $subject, $content, 1);
//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// 판매자에게 메일 보내기 (상품별로 보낸다.)
//------------------------------------------------------------------------------

$list = array();
$sql = " select b.it_sell_email,
                a.it_id,
                a.it_name
           from {$g5['g5_subscription_pay_basket_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
          where a.od_id = '$od_id' and pay_id = '{$pay['pay_id']}'
            and a.pb_select = '1'
            and b.it_sell_email <> ''
          group by a.it_id ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    // 합계금액 계산
    $sql = " select SUM(IF(io_type = 1, (io_price * pb_qty), ((pb_price + io_price) * pb_qty))) as price,
                    SUM(pb_point * pb_qty) as point,
                    SUM(pb_qty) as qty
                from {$g5['g5_subscription_pay_basket_table']}
                where it_id = '{$row['it_id']}'
                  and od_id = '$od_id' and pay_id = '{$pay['pay_id']}'
                  and pb_select = '1' ";
    $sum = sql_fetch($sql);

    // 옵션정보
    $sql2 = " select pb_option, pb_qty, io_price
                from {$g5['g5_subscription_pay_basket_table']}
                where it_id = '{$row['it_id']}' and od_id = '$od_id' and pay_id = '{$pay['pay_id']}' and pb_select = '1'
                order by io_type asc, pb_id asc ";
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
        $options .= '<li'.$options_li.'>'.$row2['pb_option'].' ('.$price_plus.display_price($row2['io_price']).') '.$row2['pb_qty'].'개</li>'.PHP_EOL;
    }

    if($k > 0)
        $options .= '</ul>';

    $list[$i]['it_id']   = $row['it_id'];
    $list[$i]['it_simg'] = get_it_image($row['it_id'], 70, 70);
    $list[$i]['it_name'] = $row['it_name'];
    $list[$i]['it_opt']  = $options;
    $list[$i]['pb_price'] = $sum['price'];

    $subject = $config['cf_title'].' - 주문 알림 메일 (주문자 '.$py_name.'님)';
    ob_start();
    include G5_SUBSCRIPTION_PATH.'/mail/subscription_pay_mail3.mail.php';
    $content = ob_get_contents();
    ob_end_clean();

    mailer($config['cf_admin_email_name'], $config['cf_admin_email'],  $row['it_sell_email'], $subject, $content, 1);
}
//==============================================================================;