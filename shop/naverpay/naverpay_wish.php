<?php
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/settle_naverpay.inc.php');
include_once(G5_LIB_PATH.'/naverpay.lib.php');

$count = (isset($_POST['it_id']) && is_array($_POST['it_id'])) ? count($_POST['it_id']) : 0;

if ($count < 1)
    alert_close('찜하실 상품을 선택하여 주십시오.');

$query = '';
$item  = '';

for($i=0; $i<$count; $i++) {
    $it_id = isset($_POST['it_id']) ? $_POST['it_id'][$i] : '';

    // 상품정보
    $it = get_shop_item($it_id, true);
    if(! (isset($it['it_id']) && $it['it_id']))
        alert_close('상품정보가 존재하지 않습니다.');

    $id          = urlencode($it['it_id']);
    $name        = urlencode($it['it_name']);
    $description = urlencode($it['it_basic']);
    $price       = get_price($it);
    $image       = urlencode(get_naverpay_item_image_url($it_id));
    $item_url    = urlencode(shop_item_url($it_id));

    $item .= '&ITEM_ID='.$id;
    if($it['ec_mall_pid'])
        $item .= '&EC_MALL_PID='.urlencode($it['ec_mall_pid']);
    $item .= '&ITEM_NAME='.$name;
    $item .= '&ITEM_DESC='.$description;
    $item .= '&ITEM_UPRICE='.$price;
    $item .= '&ITEM_IMAGE='.$image;
    $item .= '&ITEM_THUMB='.$image;
    $item .= '&ITEM_URL='.$item_url;
}

if($item) {
    $query .= 'SHOP_ID='.urlencode($default['de_naverpay_mid']);
    $query .= '&CERTI_KEY='.urlencode($default['de_naverpay_cert_key']);
    $query .= $item;
}

$nc_sock = @fsockopen($req_addr, $req_port, $errno, $errstr);
if ($nc_sock) {
    fwrite($nc_sock, $wish_req_url."\r\n" );
    fwrite($nc_sock, "Host: ".$req_host.":".$req_port."\r\n" );
    fwrite($nc_sock, "Content-type: application/x-www-form-urlencoded; charset=utf-8\r\n");
    fwrite($nc_sock, "Content-length: ".strlen($query)."\r\n");
    fwrite($nc_sock, "Accept: */*\r\n");
    fwrite($nc_sock, "\r\n");
    fwrite($nc_sock, $query."\r\n");
    fwrite($nc_sock, "\r\n");

    $headers = $bodys = '';

    // get header
    while(!feof($nc_sock)) {
        $header=fgets($nc_sock,4096);
        if($header=="\r\n") {
            break;
        } else {
            $headers .= $header;
        }
    }
    // get body
    while(!feof($nc_sock)) {
        $bodys.=fgets($nc_sock,4096);
    }

    fclose($nc_sock);

    $resultCode = substr($headers,9,3);

    if ($resultCode == 200) {
        // success
        $itemIds = trim($bodys);
        $itemIdList = explode(',',$itemIds);
    } else {
        // fail
        die($bodys);
    }
} else {
    echo "$errstr ($errno)<br>\n";
    exit(-1);
    //에러처리
}

$count = count($itemIdList);

if ($resultCode == 200) {
?>
<html>
<body>
<form name="frm" method="get" action="<?php echo $wishUrl; ?>">
<input type="hidden" name="SHOP_ID" value="<?php echo $default['de_naverpay_mid']; ?>">
<?php
for($i=0; $i<$count; $i++) {
?>
<input type="hidden" name="ITEM_ID" value="<?php echo $itemIdList[$i]; ?>">
<?php
}
?>
</form>
</body>

<script>
document.frm.target = "_top";
document.frm.submit();
</script>
</html>
<?php }