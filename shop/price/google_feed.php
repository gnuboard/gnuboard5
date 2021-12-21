<?php
include_once("./_common.php");

function array_xml($arr) {
	if(!is_array($arr)) return $arr;
	$result = '';
	foreach($arr as $key => $val) {
        if(is_numeric($key)) {
            $result .= array_xml($val);
        } else {
            $result .= "<{$key}>".array_xml($val)."</{$key}>";
        }
	}
	return $result;
}

ob_end_clean();

ob_start();

$sql =" select * from {$g5['g5_shop_item_table']} where it_use = '1' and it_soldout = '0' and it_tel_inq = '0' and it_price > '0' order by ca_id";
$result = sql_query($sql);

$xml = array( "channel" => array(
    "title" => "타이틀",
    "link" => "링크",
    "description" => "몰?루"
));

$items = array();
for ($i = 0; $row = sql_fetch_array($result); $i++ ) {
    if(empty($row['it_img1'])) continue;
    if(!file_exists(G5_DATA_PATH.'/item/'.$row['it_img1'])) continue;

    $xml['channel']['item'][$i]['title'] = $row['it_name'];
    $xml['channel']['item'][$i]['link'] = G5_SHOP_URL.'/item.php?it_id='.$row['it_id'];
    $xml['channel']['item'][$i]['description'] = $row['it_basic'];
    $xml['channel']['item'][$i]['g:image_link'] = G5_DATA_URL.'/item/'.$row['it_img1'];
    $xml['channel']['item'][$i]['g:condition'] = "new";
    $xml['channel']['item'][$i]['g:id'] = $row['it_id'];
}

header('Content-type: text/xml'); 
echo "<?xml version='1.0' encoding='UTF-8'?>";
echo "<rss version=\"2.0\" xmlns:g=\"http://base.google.com/ns/1.0\">";

foreach($xml as $key => $var) {
    echo array_xml($var);
}

echo "</rss>";

?>