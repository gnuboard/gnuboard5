<?php
include_once('./_common.php');

// 새로 테이블을 만든 경우 기존 옵션정보 삭제
if($makemode == 'create') {
    $sql = " delete from `{$g4['yc4_option_table']}` where it_id = '$it_id' ";
    sql_query($sql);
}

// 옵션정보입력
$count = count($_POST['opt_id']);

if(!$count) {
    $sql = " update {$g4['yc4_item_table']}
                set it_option_use   = '0',
                    it_opt1_subject = '',
                    it_opt2_subject = '',
                    it_opt3_subject = '',
                    it_opt1         = '',
                    it_opt2         = '',
                    it_opt3         = ''
                where it_id = '$it_id' ";
    sql_query($sql);

    echo '<script>self.close();</script>';
    exit;
}

for($i = 0; $i < $count; $i++) {
    $sql_common = " opt_amount  = '{$_POST['opt_amount'][$i]}',
                    opt_qty     = '{$_POST['opt_qty'][$i]}',
                    opt_notice  = '{$_POST['opt_notice'][$i]}',
                    opt_use     = '{$_POST['opt_use'][$i]}' ";

    $row = sql_fetch(" select opt_id from `{$g4['yc4_option_table']}` where it_id = '$it_id' and opt_id = '{$_POST['opt_id'][$i]}' ");

    if($row['opt_id']) {
        $sql = " update `{$g4['yc4_option_table']}` set $sql_common where it_id = '$it_id' and opt_id = '{$_POST['opt_id'][$i]}' ";
    } else {
        $sql = " insert into `{$g4['yc4_option_table']}` set it_id = '$it_id', opt_id = '{$_POST['opt_id'][$i]}', $sql_common ";
    }

    sql_query($sql);
}

if($w == '') {
    set_session('ss_op_item_code', $it_id);
}

echo '<script>self.close();</script>';
?>