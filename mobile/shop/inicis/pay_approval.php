<?php
include_once('./_common.php');
include_once(G5_MSHOP_PATH.'/settle_inicis.inc.php');

// 세션 초기화
set_session('P_TID',  '');
set_session('P_AMT',  '');
set_session('P_HASH', '');

$oid  = trim($_REQUEST['P_NOTI']);

$sql = " select * from {$g5['g5_shop_order_data_table']} where od_id = '$oid' ";
$row = sql_fetch($sql);

$data = unserialize(base64_decode($row['dt_data']));

if(isset($data['pp_id']) && $data['pp_id']) {
    $order_action_url = G5_HTTPS_MSHOP_URL.'/personalpayformupdate.php';
    $page_return_url  = G5_SHOP_URL.'/personalpayform.php?pp_id='.$data['pp_id'];
} else {
    $order_action_url = G5_HTTPS_MSHOP_URL.'/orderformupdate.php';
    $page_return_url  = G5_SHOP_URL.'/orderform.php';
    if($_SESSION['ss_direct'])
        $page_return_url .= '?sw_direct=1';

    // 장바구니가 비어있는가?
    if (get_session('ss_direct'))
        $tmp_cart_id = get_session('ss_cart_direct');
    else
        $tmp_cart_id = get_session('ss_cart_id');

    if (get_cart_count($tmp_cart_id) == 0)// 장바구니에 담기
        alert('세션을 잃거나 다른 브라우저에서 데이터가 변경된 경우입니다. 장바구니 상태를 확인후에 다시 시도해 주세요.', G5_SHOP_URL.'/cart.php');

    $error = "";
    // 장바구니 상품 재고 검사
    $sql = " select it_id,
                    ct_qty,
                    it_name,
                    io_id,
                    io_type,
                    ct_option
               from {$g5['g5_shop_cart_table']}
              where od_id = '$tmp_cart_id'
                and ct_select = '1' ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        // 상품에 대한 현재고수량
        if($row['io_id']) {
            $it_stock_qty = (int)get_option_stock_qty($row['it_id'], $row['io_id'], $row['io_type']);
        } else {
            $it_stock_qty = (int)get_it_stock_qty($row['it_id']);
        }
        // 장바구니 수량이 재고수량보다 많다면 오류
        if ($row['ct_qty'] > $it_stock_qty)
            $error .= "{$row['ct_option']} 의 재고수량이 부족합니다. 현재고수량 : $it_stock_qty 개\\n\\n";
    }

    if($i == 0)
        alert('장바구니가 비어 있습니다.', G5_SHOP_URL.'/cart.php');

    if ($error != "")
    {
        $error .= "결제진행이 중단 되었습니다.";
        alert($error, G5_SHOP_URL.'/cart.php');
    }
}

if($_REQUEST['P_STATUS'] != '00') {
    alert('오류 : '.iconv_utf8($_REQUEST['P_RMESG1']).' 코드 : '.$_REQUEST['P_STATUS'], $page_return_url);
} else {
    $post_data = array(
        'P_MID' => $default['de_inicis_mid'],
        'P_TID' => $_REQUEST['P_TID']
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $_REQUEST['P_REQ_URL']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $return = curl_exec($ch);

    if(!$return)
        alert('KG이니시스와 통신 오류로 결제등록 요청을 완료하지 못했습니다.\\n결제등록 요청을 다시 시도해 주십시오.', $page_return_url);

    // 결과를 배열로 변환
    parse_str($return, $ret);
    $PAY = array_map('trim', $ret);
    $PAY = array_map('strip_tags', $PAY);
    $PAY = array_map('get_search_string', $PAY);

    if($PAY['P_STATUS'] != '00')
        alert('오류 : '.iconv_utf8($PAY['P_RMESG1']).' 코드 : '.$PAY['P_STATUS'], $page_return_url);

    // TID, AMT 를 세션으로 주문완료 페이지 전달
    $hash = md5($PAY['P_TID'].$PAY['P_MID'].$PAY['P_AMT']);
    set_session('P_TID',  $PAY['P_TID']);
    set_session('P_AMT',  $PAY['P_AMT']);
    set_session('P_HASH', $hash);
}

$g5['title'] = 'KG 이니시스 결제';
$g5['body_script'] = ' onload="setPAYResult();"';
include_once(G5_PATH.'/head.sub.php');

$exclude = array('res_cd', 'P_HASH', 'P_TYPE', 'P_AUTH_DT', 'P_AUTH_NO', 'P_HPP_CORP', 'P_APPL_NUM', 'P_VACT_NUM', 'P_VACT_NAME', 'P_VACT_BANK', 'P_CARD_ISSUER', 'P_UNAME');

echo '<form name="forderform" method="post" action="'.$order_action_url.'" autocomplete="off">'.PHP_EOL;

echo make_order_field($data, $exclude);

echo '<input type="hidden" name="res_cd"        value="'.$PAY['P_STATUS'].'">'.PHP_EOL;
echo '<input type="hidden" name="P_HASH"        value="'.$hash.'">'.PHP_EOL;
echo '<input type="hidden" name="P_TYPE"        value="'.$PAY['P_TYPE'].'">'.PHP_EOL;
echo '<input type="hidden" name="P_AUTH_DT"     value="'.$PAY['P_AUTH_DT'].'">'.PHP_EOL;
echo '<input type="hidden" name="P_AUTH_NO"     value="'.$PAY['P_AUTH_NO'].'">'.PHP_EOL;
echo '<input type="hidden" name="P_HPP_CORP"    value="'.$PAY['P_HPP_CORP'].'">'.PHP_EOL;
echo '<input type="hidden" name="P_APPL_NUM"    value="'.$PAY['P_APPL_NUM'].'">'.PHP_EOL;
echo '<input type="hidden" name="P_VACT_NUM"    value="'.$PAY['P_VACT_NUM'].'">'.PHP_EOL;
echo '<input type="hidden" name="P_VACT_NAME"   value="'.iconv_utf8($PAY['P_VACT_NAME']).'">'.PHP_EOL;
echo '<input type="hidden" name="P_VACT_BANK"   value="'.$BANK_CODE[$PAY['P_VACT_BANK_CODE']].'">'.PHP_EOL;
echo '<input type="hidden" name="P_CARD_ISSUER" value="'.$CARD_CODE[$PAY['P_CARD_ISSUER_CODE']].'">'.PHP_EOL;
echo '<input type="hidden" name="P_UNAME"       value="'.iconv_utf8($PAY['P_UNAME']).'">'.PHP_EOL;

echo '</form>'.PHP_EOL;
?>

<div id="show_progress">
    <span style="display:block; text-align:center;margin-top:120px"><img src="<?php echo G5_MOBILE_URL; ?>/shop/img/loading.gif" alt=""></span>
    <span style="display:block; text-align:center;margin-top:10px; font-size:14px">주문완료 중입니다. 잠시만 기다려 주십시오.</span>
</div>

<script type="text/javascript">
function setPAYResult() {
    setTimeout( function() {
        document.forderform.submit();
    }, 300);
}
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>