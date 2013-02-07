<?php
include_once('./_common.php');

// 선택/추가 옵션 테이블에서 상품정보가 없는 정보 삭제

$sql = " select distinct it_id
            from {$g4['shop_option_table']}
            order by opt_id ";
$result = sql_query($sql);

for($i=0; $row=sql_fetch_array($result); $i++) {
    // 상품정보 체크
    $sql1 = " select it_id from {$g4['shop_item_table']} where it_id = '{$row['it_id']}' ";
    $row1 = sql_fetch($sql1);

    if(!$row1['it_id']) {
        @sql_query(" delete from {$g4['shop_option_table']} where it_id = '{$row['it_id']}' ");
    }
}


$sql = " select distinct it_id
            from {$g4['shop_supplement_table']}
            order by sp_id ";
$result = sql_query($sql);

for($i=0; $row=sql_fetch_array($result); $i++) {
    // 상품정보 체크
    $sql1 = " select it_id from {$g4['shop_item_table']} where it_id = '{$row['it_id']}' ";
    $row1 = sql_fetch($sql1);

    if(!$row1['it_id']) {
        @sql_query(" delete from {$g4['shop_supplement_table']} where it_id = '{$row['it_id']}' ");
    }
}
?>