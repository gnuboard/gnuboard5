<?php
include_once('./_common.php');

$od_id = isset($_REQUEST['od_id']) ? safe_replace_regex($_REQUEST['od_id'], 'od_id') : '';
$tx = isset($_REQUEST['tx']) ? clean_xss_tags($_REQUEST['tx'], 1, 1) : '';

if (!$od_id){
    alert('주문번호가 누락되었습니다.');
}

$g5['title'] = '주문번호 '.$od_id.' 현금영수증 발행';
include_once(G5_PATH.'/head.sub.php');

if($tx == 'personalpay') {
    $od = sql_fetch(" select * from {$g5['g5_shop_personalpay_table']} where pp_id = '$od_id' ");
    if (!$od)
        die('<p id="scash_empty">개인결제 내역이 존재하지 않습니다.</p>');

    $goods_name = $od['pp_name'].'님 개인결제';
    $amt_tot = (int)$od['pp_receipt_price'];
    $dir = $od['pp_pg'];
    $od_name = $od['pp_name'];
    $od_email = get_text($od['pp_email']);
    $od_tel = get_text($od['pp_hp']);

    $amt_tot = (int)$od['pp_receipt_price'];
    $amt_sup = (int)round(($amt_tot * 10) / 11);
    $amt_svc = 0;
    $amt_tax = (int)($amt_tot - $amt_sup);
} else {
    $od = sql_fetch(" select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");
    if (!$od)
        die('<p id="scash_empty">주문서가 존재하지 않습니다.</p>');

	if ( ! $is_admin && ! (shop_is_taxsave($od)) ){
		die('해당 주문은 현금영수증을 발급할수 없습니다.');
	}

    $goods = get_goods($od['od_id']);
    $goods_name = $goods['full_name'];
    $amt_tot = (int)($od['od_receipt_price'] - $od['od_refund_price']);
    $dir = $od['od_pg'];
    $od_name = $od['od_name'];
    $od_email = get_text($od['od_email']);
    $od_tel = get_text($od['od_tel']);

    $amt_tot = (int)$od['od_tax_mny'] + (int)$od['od_vat_mny'] + (int)$od['od_free_mny'];
    $amt_sup = (int)$od['od_tax_mny'] + (int)$od['od_free_mny'];
    $amt_tax = (int)$od['od_vat_mny'];
    $amt_svc = 0;
}

$trad_time = date("YmdHis");

// 신청폼
if(!$dir)
    $dir = $default['de_pg_service'];

include_once(G5_SHOP_PATH.'/'.$dir.'/taxsave_form.php');

include_once(G5_PATH.'/tail.sub.php');