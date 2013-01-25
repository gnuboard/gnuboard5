<?php
include_once('./_common.php');

// 선택옵션별 재고체크
$sql = " select a.it_id, a.opt_id, a.opt_qty, b.it_name
            from {$g4['yc4_option_table']} a left join {$g4['yc4_item_table']} b on ( a.it_id = b.it_id )
            where a.opt_qty < a.opt_notice
              and a.opt_use = '1'
            order by a.opt_qty asc ";
$result = sql_query($sql);

for($i=0; $row=sql_fetch_array($result); $i++) {
    echo $row['it_name'] . ' (' . str_replace(chr(30), ' / ', $row['opt_id']) . ') 재고: ' . number_format($row['opt_qty']);
}

// 추가옵션별 재고체크
$sql = " select a.it_id, a.sp_id, a.sp_qty, b.it_name
            from {$g4['yc4_supplement_table']} a left join {$g4['yc4_item_table']} b on ( a.it_id = b.it_id )
            where a.sp_qty < a.sp_notice
              and a.sp_use = '1'
            order by a.sp_qty asc ";
$result = sql_query($sql);

for($i=0; $row=sql_fetch_array($result); $i++) {
    echo $row['it_name'] . ' (' . str_replace(chr(30), ' / ', $row['sp_id']) . ') 재고: ' . number_format($row['sp_qty']);
}
?>