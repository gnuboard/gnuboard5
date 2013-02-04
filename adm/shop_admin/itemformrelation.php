<?
$sub_menu = "400300";
include_once("./_common.php");
include_once(G4_LIB_PATH.'/thumbnail.lib.php');

$sql = " select ca_id, it_id, it_name, it_amount, it_img1, it_img2, it_img3, it_img4, it_img5, it_img6, it_img7, it_img8, it_img9, it_img10
           from {$g4['yc4_item_table']}
          where ca_id like '$ca_id%'
            and it_id <> '$it_id'
          order by ca_id, it_name ";
$result = sql_query($sql);
$num = @mysql_num_rows($result);

$options = "";
for($i=0;$row=sql_fetch_array($result);$i++) {
    // 관련상품으로 등록된 상품은 제외
    $sql2 = " select count(*) as cnt from {$g4['yc4_item_relation_table']} where it_id = '$it_id' and it_id2 = '{$row['it_id']}' ";
    $row2 = sql_fetch($sql2);
    if ($row2['cnt'])
        continue;

    $sql2 = " select ca_name from {$g4['yc4_category_table']} where ca_id = '{$row['ca_id']}' ";
    $row2 = sql_fetch($sql2);
    $ca_name = addslashes($row2['ca_name']);

    $it_name = addslashes($row['it_name']);

    // 상품이미지
    for($i=1; $i<=10; $i++) {
        $idx = 'it_img'.$i;
        $filepath = G4_DATA_PATH.'/item/'.$row['it_id'];
        $filename = $row[$idx];
        if(file_exists($filepath.'/'.$filename) && $filename != "")
            break;
    }

    $it_img = it_img_thumb($filename, $filepath, 100, 80);

    $options .= "<option value=\"".$row['it_id']."/".$it_img."/".$row['it_amount']."\">$ca_name : $it_name</option>\n";
}

echo $options;
?>