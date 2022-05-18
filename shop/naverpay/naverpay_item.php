<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/naverpay.lib.php');

$query = $_SERVER['QUERY_STRING'];

$vars = array();

foreach(explode('&', $query) as $pair) {
    @list($key, $value) = explode('=', $pair);
    $key = urldecode($key);
    $value = preg_replace("/[^A-Za-z0-9\-_]/", "", urldecode($value));
    $vars[$key][] = $value;
}

if (isset($vars['ITEM_ID'])) 
    $itemIds = $vars['ITEM_ID'];
else
    $itemIds = array();

if (is_null($itemIds) || count($itemIds) < 1) {
    exit('ITEM_ID 는 필수입니다.');
}

header('Content-Type: application/xml;charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<response>
<?php
foreach($itemIds as $it_id) {
    $it = get_shop_item($it_id, true);
    if(!$it['it_id'])
        continue;

    $id          = $it['it_id'];
    $name        = $it['it_name'];
    $description = $it['it_basic'];
    $price       = get_price($it);
    $image       = get_naverpay_item_image_url($it_id);
    $quantity    = get_naverpay_item_stock($it_id);
    $ca_name     = '';
    $ca_name2    = '';
    $ca_name3    = '';
    $returnInfo  = get_naverpay_return_info($it['it_seller']);
    $option      = get_naverpay_item_option($it_id, $it['it_option_subject']);

    if($it['ca_id']) {
        $cat = sql_fetch(" select ca_name from {$g5['g5_shop_category_table']} where ca_id = '{$it['ca_id']}' ");
        $ca_name = $cat['ca_name'];
    }
    if($it['ca_id2']) {
        $cat = sql_fetch(" select ca_name from {$g5['g5_shop_category_table']} where ca_id = '{$it['ca_id2']}' ");
        $ca_name2 = $cat['ca_name'];
    }
    if($it['ca_id3']) {
        $cat = sql_fetch(" select ca_name from {$g5['g5_shop_category_table']} where ca_id = '{$it['ca_id3']}' ");
        $ca_name3 = $cat['ca_name'];
    }
?>
<item id="<?php echo $id; ?>">
<?php if($it['ec_mall_pid']) { ?>
<mall_pid><![CDATA[<?php echo $it['ec_mall_pid']; ?>]]></mall_pid>
<?php } ?>
<name><![CDATA[<?php echo $name; ?>]]></name>
<url><?php echo shop_item_url($it_id); ?></url>
<description><![CDATA[<?php echo $description; ?>]]></description>
<image><?php echo $image; ?></image>
<thumb><?php echo $image; ?></thumb>
<price><?php echo $price; ?></price>
<quantity><?php echo $quantity; ?></quantity>
<category>
<first id="MJ01"><![CDATA[<?php echo $ca_name; ?>]]></first>
<second id="ML01"><![CDATA[<?php echo $ca_name2; ?>]]></second>
<third id="MN01"><![CDATA[<?php echo $ca_name3; ?>]]></third>
</category>
<?php echo $option; ?>
<?php echo $returnInfo; ?>
</item>
<?php
}
echo('</response>');