<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

include_once(G4_LIB_PATH.'/mailer.lib.php');

$admin = get_admin('super');

//------------------------------------------------------------------------------
// 운영자에게 메일보내기
//------------------------------------------------------------------------------
$subject = "{$config['cf_title']}에서 주문이 들어 왔습니다. ($od_name)";
ob_start();
include "./mail/orderupdate1.mail.php";
$content = ob_get_contents();
ob_end_clean();

mailer($od_name, $od_email, $admin['mb_email'], $subject, $content, 1);
//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// 주문자에게 메일보내기
//------------------------------------------------------------------------------
$subject = "{$config['cf_title']}에서 다음과 같이 주문하셨습니다.";
ob_start();
include "./mail/orderupdate2.mail.php";
$content = ob_get_contents();
ob_end_clean();

mailer($config['cf_title'], $admin['mb_email'], $od_email, $subject, $content, 1);
//------------------------------------------------------------------------------


//------------------------------------------------------------------------------
// 판매자에게 메일 보내기 (상품별로 보낸다.)
//------------------------------------------------------------------------------

$sql = " select b.it_sell_email,
                a.it_id,
                b.it_name,
                a.it_opt1,
                a.it_opt2,
                a.it_opt3,
                a.it_opt4,
                a.it_opt5,
                a.it_opt6,
                a.ct_qty
           from {$g4['shop_cart_table']} a, {$g4['shop_item_table']} b
          where a.uq_id = '$tmp_uq_id'
            and a.it_id = b.it_id
            and b.it_sell_email <> '' ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    unset($list);

    $list['it_id']   = $row['it_id'];
    $list['it_simg'] = get_it_image("{$row['it_id']}_s", $default['de_simg_width'], $default['de_simg_height']);
    $list['it_name'] = $row['it_name'];
    $list['it_opt']  = print_item_options($row['it_id'], $row['it_opt1'], $row['it_opt2'], $row['it_opt3'], $row['it_opt4'], $row['it_opt5'], $row['it_opt6']);
    $list['ct_qty']  = $row['ct_qty'];

    $subject = "{$config['cf_title']}에서 다음과 같이 주문서가 접수 되었습니다. (주문자 {$od_name}님)";
    ob_start();
    include "./mail/orderupdate3.mail.php";
    $content = ob_get_contents();
    ob_end_clean();

    mailer($config['cf_title'], $admin['mb_email'],  $row['it_sell_email'], $subject, $content, 1);
}
//==============================================================================
?>