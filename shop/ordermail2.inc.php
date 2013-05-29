<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

include_once(G4_LIB_PATH.'/mailer.lib.php');

$admin = get_admin('super');

//------------------------------------------------------------------------------
// 운영자에게 메일보내기
//------------------------------------------------------------------------------
$subject = $config['cf_title'].' - 주문 알림 메일 ('.$od_name.')';
ob_start();
include G4_SHOP_PATH.'/mail/orderupdate1.mail.php';
$content = ob_get_contents();
ob_end_clean();

mailer($od_name, $od_email, $admin['mb_email'], $subject, $content, 1);
//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// 주문자에게 메일보내기
//------------------------------------------------------------------------------
$subject = $config['cf_title'].' - 주문 내역 안내 메일';
ob_start();
include G4_SHOP_PATH.'/mail/orderupdate2.mail.php';
$content = ob_get_contents();
ob_end_clean();

mailer($config['cf_title'], $admin['mb_email'], $od_email, $subject, $content, 1);
//------------------------------------------------------------------------------


//------------------------------------------------------------------------------
// 판매자에게 메일 보내기 (상품별로 보낸다.)
//------------------------------------------------------------------------------

$sql = " select b.it_sell_email,
                a.it_id,
                a.it_name
           from {$g4['shop_cart_table']} a left join {$g4['shop_item_table']} b on ( a.it_id = b.it_id )
          where a.uq_id = '$tmp_uq_id'
            and a.ct_num = '0'
            and b.it_sell_email <> '' ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    unset($list);

    // 옵션정보
    $sql2 = " select ct_option, ct_qty
                from {$g4['shop_cart_table']}
                where it_id = '{$row['it_id']}' and uq_id = '$tmp_uq_id'
                order by io_type asc, ct_num asc, ct_id asc ";
    $result2 = sql_query($sql2);

    $options = '';
    $options_ul = ' style="margin:0;padding:0"'; // ul style
    $options_li = ' style="padding:5px 0;list-style:none"'; // li style
    for($k=0; $row2=sql_fetch_array($result2); $k++) {
        if($k == 0)
            $options .= '<ul'.$options_ul.'>'.PHP_EOL;
        $options .= '<li'.$options_li.'>'.$row2['ct_option'].' '.$row2['ct_qty'].'개</li>'.PHP_EOL;
    }

    if($k > 0)
        $options .= '</ul>';

    $list['it_id']   = $row['it_id'];
    $list['it_simg'] = get_it_image($row['it_id'], $default['de_simg_width'], $default['de_simg_height']);
    $list['it_name'] = $row['it_name'];
    $list['it_opt']  = $options;

    $subject = $config['cf_title'].' - 주문 알림 메일 (주문자 '.$od_name.'님)';
    ob_start();
    include G4_SHOP_PATH.'/mail/orderupdate3.mail.php';
    $content = ob_get_contents();
    ob_end_clean();

    mailer($config['cf_title'], $admin['mb_email'],  $row['it_sell_email'], $subject, $content, 1);
}
//==============================================================================
?>