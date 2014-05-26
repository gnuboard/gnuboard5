<?php
include_once('./_common.php');

$g5['title'] = '주문번호 '.$od_id.' 현금영수증 발행';
include_once(G5_PATH.'/head.sub.php');

$od = sql_fetch(" select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");
if (!$od)
    die('<p id="scash_empty">주문서가 존재하지 않습니다.</p>');

$goods = get_goods($od['od_id']);
$goods_name = $goods['full_name'];
//if ($goods[count] > 1) $goods_name .= ' 외 '.$goods[count].'건';

$trad_time = date("YmdHis");

$amt_tot = (int)($od['od_receipt_price'] - $od['od_refund_price']);
$amt_sup = (int)round(($amt_tot * 10) / 11);
$amt_svc = 0;
$amt_tax = (int)($amt_tot - $amt_sup);

// 신청폼
if($od['od_pg'])
    $dir = $od['od_pg'];
else
    $dir = $default['de_pg_service'];

include_once(G5_SHOP_PATH.'/'.$dir.'/taxsave_form.php');

include_once(G5_PATH.'/tail.sub.php');
?>
