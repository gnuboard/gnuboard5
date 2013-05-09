<?php
$str = '';
$exists = false;

$ca_id_len = strlen($ca_id);
$len2 = $ca_id_len + 2;
$len4 = $ca_id_len + 4;

$sql = " select ca_id, ca_name from {$g4['shop_category_table']}
          where ca_id like '$ca_id%'
            and length(ca_id) = $len2
            and ca_use = '1'
          order by ca_id ";
$result = sql_query($sql);
while ($row=sql_fetch_array($result)) {

    //$row2 = sql_fetch(" select count(*) as cnt from $g4[shop_category_table] where ca_id like '$row[ca_id]%' ");
    $row2 = sql_fetch(" select count(*) as cnt from {$g4['shop_item_table']} where (ca_id like '{$row['ca_id']}%' or ca_id2 like '{$row['ca_id']}%' or ca_id3 like '{$row['ca_id']}%') and it_use = '1'  ");

    $str .= '<li><a href="./list.php?ca_id='.$row['ca_id'].'">'.$row['ca_name'].' ('.$row2['cnt'].')</a></li>';
    $exists = true;
}

if ($exists) {
?>

<aside id="sct_ct_1" class="sct_ct">
    <h2>현재 상품 분류와 관련된 분류</h2>
    <ul>
        <?php echo $str; ?>
    </ul>
</aside>

<?php } ?>