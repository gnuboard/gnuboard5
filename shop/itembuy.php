<?php
include_once('./_common.php');

$it_id = $_GET['it_id'];
$io_id = $_GET['opt'];

// 상품정보
$sql = " select * from {$g4['shop_item_table']} where it_id = '$it_id' ";
$it = sql_fetch($sql);

// 상품옵션체크
$sql = " select count(*) as cnt from {$g4['shop_item_option_table']} where it_id = '$it_id' and io_type = '0' and io_use = '1' ";
$cnt = sql_fetch($sql);

if(($io_id && !$cnt['cnt']) || (!$io_id && $cnt['cnt']))
    alert('상품의 옵션정보가 변경됐습니다.\\n상품페이지에서 다시 주문해 주십시오.', G4_SHOP_URL.'/item.php?it_id='.$it_id);

// 옵션정보
if($io_id && $it['it_option_subject']) {
    $sql = " select * from {$g4['shop_item_option_table']} where it_id = '$it_id' and io_id = '$io_id' ";
    $opt = sql_fetch($sql);

    $subj = explode(',', $it['it_option_subject']);
    $arr_opt = explode(chr(30), $io_id);

    if(count($subj) != count($arr_opt))
        alert('상품의 옵션정보가 올바르지 않습니다.\\n상품페이지에서 다시 주문해 주십시오.', G4_SHOP_URL.'/item.php?it_id='.$it_id);

    $io_value = '';
    $sep = '';
    for($n=0; $n<count($subj); $n++) {
        $io_value .= $sep.$subj[$n].':'.$arr_opt[$n];
        $sep = ' / ';
    }
} else {
    $io_value = $it['it_name'];
}

$tot_prc = $it['it_price'] + $opt['io_price'];

// 배송비결제
$ct_send_cost = 0;
if($it['it_sc_method'] == 1)
    $ct_send_cost = 1;

$_POST['it_id'] = $it['it_id'];
$_POST['it_name'] = $it['it_name'];
$_POST['it_price'] = $it['it_price'];
$_POST['it_point'] = get_item_point($it);
$_POST['total_price'] = $tot_prc;
$_POST['io_id'][0] = $opt['io_id'];
$_POST['io_type'][0] = 0;
$_POST['io_price'][0] = $opt['io_price'];
$_POST['ct_qty'][0] = 1;
$_POST['io_value'][0] = $io_value;
$_POST['ct_send_cost'] = $ct_send_cost;

include_once(G4_SHOP_PATH.'/cartupdate.php');
?>