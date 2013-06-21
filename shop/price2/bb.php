<?php
/*
**  가격비교사이트 비비(베스트바이어) 엔진페이지
*/
function it_image($img)
{
    global $g4;

    $tmp = G4_DATA_PATH.'/item/'.$img;
    if (file_exists($tmp) && $img) {
        $str = G4_DATA_URL.'/item/'.$img;
    } else {
        $str = G4_SHOP_URL.'/img/no_image.gif';
    }
    return $str;
}

include_once('./_common.php');
?>
<html>
<title>비비 엔진페이지</title>
<head>
<meta http-equiv="Cache-Control" content="no-cache"/>
<meta http-equiv="Expires" content="0"/>
<meta http-equiv="Pragma" content="no-cache"/>
</head>
<body>
<?php
// <p>상품번호^대분류^중분류^소분류^제조사^모델명^상품Url^이미지Url^가격
$str = "";
$cnt = 0;
$sql = " select * from {$g4['shop_item_table']}
          where it_use = '1'
          order by ca_id ";
$result = sql_query($sql);
for ($i=0; $row=mysql_fetch_array($result); $i++)
{
    $image = get_it_imageurl($row['it_id']);

    $row2 = sql_fetch(" select ca_name from {$g4['shop_category_table']} where ca_id = '".substr($row['ca_id'],0,2)."' ");

    if (strlen($row['ca_id']) >= 4)
        $row3 = sql_fetch(" select ca_name from {$g4['shop_category_table']} where ca_id = '".substr($row['ca_id'],0,4)."' ");

    if (strlen($row['ca_id']) >= 6)
        $row4 = sql_fetch(" select ca_name from {$g4['shop_category_table']} where ca_id = '".substr($row['ca_id'],0,6)."' ");

    // 상품별옵션
    $sql = " select * from {$g4['shop_item_option_table']} where it_id = '{$row['it_id']}' and io_type = '0' and io_use = '1' order by io_no asc ";
    $result2 = sql_query($sql);
    $opt_count = @mysql_num_rows($result2);

    if(!$opt_count) {
        $it_name = $row['it_name'];
        $buy_url = G4_SHOP_URL.'/itembuy.php?it_id='.$row['it_id'];
        if($default['de_send_cost_case'] == '개별' && $row['it_sc_method'] != 1)
            $delivery = get_item_sendcost($row['it_id'], $row['it_price'], 1);
        $it_price = $row['it_price'];

        $stock = get_it_stock_qty($row['it_id']);
        if ($stock <= 0)
            $it_price = 0;

        $str .= "<p>{$row['it_id']}^{$row2['ca_name']}^{$row3['ca_name']}^{$row4['ca_name']}^{$row['it_maker']}^{$row['it_name']}^{$buy_url}^$image^{$it_price}";
        $str .= "\n";
        $cnt++;

    } else {
        $subj = explode(',', $row['it_option_subject']);
        for($k=0; $row2=sql_fetch_array($result2); $k++) {
            $it_name = $row['it_name'].' ';
            $opt = explode(chr(30), $row2['io_id']);
            $sep = '';
            for($j=0; $j<count($subj); $j++) {
                $it_name .= $sep.$subj[$j].':'.$opt[$j];
                $sep = ' ';
            }
            $buy_url = G4_SHOP_URL.'/itembuy.php?it_id='.$row['it_id'].'&amp;opt='.$row2['io_id'];
            $it_price = $row['it_price'] + $row2['io_price'];
            if($default['de_send_cost_case'] == '개별' && $row['it_sc_method'] != 1)
                $delivery = get_item_sendcost($row['it_id'], ($row['it_price'] + $row2['io_price']), 1);

            $stock = get_option_stock_qty($row['it_id'], $row2['io_id'], 0);
            if ($stock <= 0)
                $it_price = 0;

            $str .= "<p>{$row['it_id']}^{$row2['ca_name']}^{$row3['ca_name']}^{$row4['ca_name']}^{$row['it_maker']}^{$row['it_name']}^{$buy_url}^$image^{$it_price}";
            $str .= "\n";
            $cnt++;
        }
    }
}

echo "<p>" . $config['cf_title'] . " 입니다. 총 (".$cnt.") 건 입니다.\n";
echo $str;
?>
</body>
</html>