<?php
include_once("./_common.php");

ob_end_clean();

ob_start();

$sql =" select * from {$g5['g5_shop_item_table']} where it_use = '1' and it_soldout = '0' and it_tel_inq = '0' and it_price > '0' order by ca_id";
$result = sql_query($sql);

$xml = array();
$xml['rss'] = "version=\"2.0\" xmlns:g=\"http:base.google.com/ns/1.0\"";
$xml['channel'] = array(
    "title" => "타이틀",
    "link" => "링크",
    "description" => "몰?루"
);

$items = array();
while($row = sql_fetch_array($result)) {
    $items['title'] = $row['it_name'];
    $items['link'] = G5_SHOP_URL.'/item.php?it_id='.$row['it_id'];
    $items['description'] = $row['it_basic'];
    // $items['g:image_link'] = G5_DATA_PATH.'/item/'.$row['']
}

header('Content-type: text/xml'); 
echo "<?xml version='1.0' encoding='UTF-8'?>\n";

?>