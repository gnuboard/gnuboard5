<?php
include_once("./_common.php");

$sql =" SELECT *, (SELECT MIN(`io_stock_qty`) FROM `g5_shop_item_option` WHERE `it_id` = a.`it_id` GROUP BY `it_id`) AS in_stock
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
    if($row['in_stock'] != null) {
        if($row['in_stock'] <= 0) continue;
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
    $item->addChild("title", $row['it_name']);
    $item->addChild("link", G5_SHOP_URL.'/item.php?it_id='.urlencode($row['it_id']));
    $item->addChild("description", strip_tags($row['it_basic']));
    $item->addChild("g:g:image_link", G5_DATA_URL.'/item/'.$row['it_img1']);
    $item->addChild("g:g:condition", "new");
    $item->addChild("g:g:id", $row['it_id']);
}

header('Content-type: text/xml'); 
echo $xml->asXML();

?>