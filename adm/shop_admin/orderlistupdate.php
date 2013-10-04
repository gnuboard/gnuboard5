<?php
$sub_menu = '400400';
include_once('./_common.php');

// 주문상태변경 처리
function change_order_status($od_status1, $od_status2, $od) 
{
    global $g5;

    // 원래 주문상태와 바뀔 주문상태가 같다면 처리하지 않음
    if ($od_status1 == $od_status2) return '';

    $od_id = $od['od_id'];

    if ($od_status1 == '주문') {
        if ($od_status2 == '입금') {
            if ($od['od_settle_case'] != '무통장') return '';
            $sql = " update {$g5['g5_shop_order_table']} 
                        set od_status = '입금',  
                            od_receipt_price = od_misu,
                            od_misu = 0
                      where od_id = '$od_id' ";
            sql_query($sql, true);

            /*
            $sql = " update {$g5['g5_shop_cart_table']} set ct_status = '결제완료' where od_id = '$od_id' and ct_status not in ('취소', '반품', '품절') ";
            sql_query($sql);
            */
        }
    }
}

print_r2($_POST); 

for ($i=0; $i<count($_POST['chk']); $i++)
{
    // 실제 번호를 넘김
    $k = $_POST['chk'][$i];

    $od_id = $_POST['od_id'][$k];
    $od = sql_fetch(" select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");

    change_order_status($od['od_status'], $_POST['od_status'], $od);

    
    echo $od_id . "<br>";
}
