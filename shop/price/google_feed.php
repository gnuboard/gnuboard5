<?php
include_once("./_common.php");

$sql =" select *, if((SELECT io_stock_qty FROM `{$g5['g5_shop_item_option_table']}` WHERE it_id = a.it_id GROUP BY it_id HAVING io_stock_qty < 1) != null, 0, 1) as in_stock
        from `{$g5['g5_shop_item_table']}` as a
        where it_use = '1' and it_soldout = '0' and it_tel_inq = '0' and it_price > '0' order by ca_id";
$result = sql_query($sql);

// $xml = new SimpleXMLElement("<rss version=\"2.0\" xmlns:g=\"http://base.google.com/ns/1.0\"></rss>");
$xml = new SimpleXMLElement("<rss/>");
$xml->addAttribute("xmlns:xmlns:g", "http://base.google.com/ns/1.0");
$xml->addAttribute("version", "2.0");
$channel = $xml->addChild("channel");
$title = $channel->addChild("title", "타이틀");
$link = $channel->addChild("link", "링크");
$description = $channel->addChild("description", "몰?루");

for ($i = 0; $row = sql_fetch_array($result); $i++ ) {
    $item = $channel->addChild("item");
    if(empty($row['it_img1'])) continue;
    if(!file_exists(G5_DATA_PATH.'/item/'.$row['it_img1'])) continue;

    $item->addChild("title", $row['it_name']);
    $item->addChild("link", G5_SHOP_URL.'/item.php?it_id='.urlencode($row['it_id']));
    $item->addChild("description", strip_tags($row['it_basic']));

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
    $item->addChild("xmlns:g:image_link", G5_DATA_URL.'/item/'.$row['it_img1']);

    $item->addChild("xmlns:g:condition", "new");
    $item->addChild("xmlns:g:id", $row['it_id']);
}

header('Content-type: text/xml'); 
echo $xml->asXML();

?>