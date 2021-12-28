<?php
include_once("./_common.php");

$sql = "SELECT a.ca_id,
                a.ca_adult_use AS ca_adult,
                IF( SUBSTR(a.ca_id, 3) != \"\", (SELECT ca_adult_use FROM `g5_shop_category` WHERE ca_id = SUBSTR(a.ca_id, 3)), 0) AS ca_adult_parent1,
                IF( SUBSTR(a.ca_id, 5) != \"\", (SELECT ca_adult_use FROM `g5_shop_category` WHERE ca_id = SUBSTR(a.ca_id, 5)), 0) AS ca_adult_parent2,
                IF( SUBSTR(a.ca_id, 7) != \"\", (SELECT ca_adult_use FROM `g5_shop_category` WHERE ca_id = SUBSTR(a.ca_id, 7)), 0) AS ca_adult_parent3, 
                IF( SUBSTR(a.ca_id, 9) != \"\", (SELECT ca_adult_use FROM `g5_shop_category` WHERE ca_id = SUBSTR(a.ca_id, 9)), 0) AS ca_adult_parent4
                FROM `g5_shop_category` AS a";
$result = sql_query($sql);

$category_array = array();
for ($i = 0; $row = sql_fetch_array($result); $i++ ) {
    $category_array[$row['ca_id']] = array( $row['ca_adult'],               // 자기자신 성인인증판단
                                            $row['ca_adult_parent1'],       // 1depth 성인인증
                                            $row['ca_adult_parent2'],       // 2depth 성인인증 
                                            $row['ca_adult_parent3'],       // 3depth 성인인증
                                            $row['ca_adult_parent4']);      // 4depth 성인인증
}

$sql =" SELECT a.*, (SELECT MIN(`io_stock_qty`) FROM `g5_shop_item_option` WHERE `it_id` = a.`it_id` GROUP BY `it_id`) AS in_stock
        FROM `{$g5['g5_shop_item_table']}` as a
        where a.`it_use` = '1' and a.`it_soldout` = '0' and a.`it_tel_inq` = '0' and a.`it_price` > '0' order by a.`ca_id`";
$result = sql_query($sql);

$xml = new SimpleXMLElement("<rss/>");
$xml->addAttribute("xmlns:xmlns:g", "http://base.google.com/ns/1.0");
$xml->addAttribute("version", "2.0");
$channel = $xml->addChild("channel");
$title = $channel->addChild("title", "쇼핑몰피드");
$link = $channel->addChild("link", G5_URL);
$description = $channel->addChild("description", "몰?루");

for ($i = 0; $row = sql_fetch_array($result); $i++ ) {
    $stock = "in_stock";
    if($row['in_stock'] != null) {
        if($row['in_stock'] <= 0) $stock = "out_of_stock";
    }
    if(empty($row['it_img1'])) continue;
    if(!file_exists(G5_DATA_PATH.'/item/'.$row['it_img1'])) continue;

    $ext = explode('.', $row['it_img1'])[1];

    switch($ext) {
        case "jpg":
        case "jpeg":
        case "webp":
        case "png":
        case "gif":
        case "bmp":
        case "tif":
        case "tiff":
            $ext_check = true;
            break;
        default:
            $ext_check = false;
            break;
    }

    if($ext_check == false) continue;
    
    $item = $channel->addChild("item");
    // 필수 입력 항목
    $item->addChild("g:g:id", $row['it_id']);
    $item->addChild("title", $row['it_name']);
    $item->addChild("description", strip_tags($row['it_basic']));
    $item->addChild("link", G5_SHOP_URL.'/item.php?it_id='.urlencode($row['it_id']));
    $item->addChild("g:g:image_link", G5_DATA_URL.'/item/'.$row['it_img1']);
    $item->addChild("availability", $stock);
    
    if($row['it_cust_price'] != null && $row['it_cust_price'] > 0) {
        $item->addChild("g:g:price", sprintf('%.2fKRW', $row['it_cust_price']));
        $item->addChild("g:g:sale_price", sprintf('%.2fKRW', $row['it_price']));
    } else {
        $item->addChild("g:g:price", sprintf('%.2fKRW', $row['it_price']));
    }
    
    // 선택적 입력 항목
    $item->addChild("g:g:condition", "new");

    $cate_array = array($row['ca_id'], $row['ca_id2'], $row['ca_id3']);

    $adult = "no";
    foreach($cate_array as $key => $var) {
        if(in_array(1, $category_array[$var])) {
            $adult = "yes";
        }
    }

    $item->addChild("adult", $adult);
}

header('Content-type: text/xml'); 
echo $xml->asXML();

?>