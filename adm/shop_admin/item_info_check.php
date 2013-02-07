<?php
include_once('./_common.php');

// 상품요약정보중 it_id 가 존재하지 않는 것을 삭제
$sql = " select distinct it_id
            from {$g4['shop_item_info_table']}
            order by ii_id ";
$result = sql_query($sql);

for($i=0; $row=sql_fetch_array($result); $i++) {
    $sql1 = "select it_id from {$g4['shop_item_table']} where it_id = '{$row['it_id']}' ";
    $row1 = sql_fetch($sql1);

    if(!$row1['it_id']) {
        @sql_query(" delete from {$g4['shop_item_info_table']} where it_id = '{$row['it_id']}' ");
    }
}