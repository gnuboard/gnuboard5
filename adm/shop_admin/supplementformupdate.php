<?php
include_once('./_common.php');

// 새로 테이블을 만든 경우 기존 옵션정보 삭제
if($makemode == 'create') {
    $sql = " delete from `{$g4['yc4_supplement_table']}` where it_id = '$it_id' ";
    sql_query($sql);
}

// 옵션정보입력
$count = count($_POST['sp_id']);

if(!$count) {
    $sql = " update {$g4['yc4_item_table']} set it_supplement_use = '0' where it_id = '$it_id' ";
    sql_query($sql);
    echo '<script>self.close();</script>';
    exit;
}

for($i = 0; $i < $count; $i++) {
    $sql_common = " sp_amount  = '{$_POST['sp_amount'][$i]}',
                    sp_qty     = '{$_POST['sp_qty'][$i]}',
                    sp_notice  = '{$_POST['sp_notice'][$i]}',
                    sp_use     = '{$_POST['sp_use'][$i]}' ";

    $row = sql_fetch(" select sp_id from `{$g4['yc4_supplement_table']}` where it_id = '$it_id' and sp_id = '{$_POST['sp_id'][$i]}' ");

    if($row['sp_id']) {
        $sql = " update `{$g4['yc4_supplement_table']}` set $sql_common where it_id = '$it_id' and sp_id = '{$_POST['sp_id'][$i]}' ";
    } else {
        $sql = " insert into `{$g4['yc4_supplement_table']}` set it_id = '$it_id', sp_id = '{$_POST['sp_id'][$i]}', $sql_common ";
    }

    sql_query($sql);
}

if($w == '') {
    set_session('ss_sp_item_code', $it_id);
}

echo '<script>self.close();</script>';
?>