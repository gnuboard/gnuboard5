<?php
include_once('./_common.php');

// 상품정보
$sql = " select it_id, it_name from {$g4['shop_item_table']} where it_id = '$it_id' ";
$it = sql_fetch($sql);

if(!$it['it_id']) {
    alert_close('상품 정보가 존재하지 않아 선택사항을 변경할 수 없습니다.');
}

$it_name = get_text($it['it_name']);

$s_uq_id = get_session('ss_uniqid');

$count = count($_POST['ct_id']);

// 옵션개수
$opt_count = 0;
$spl_count = 0;
for($k=0; $k<$count; $k++) {
    $is_delete = $_POST['is_delete'][$k];
    if(!$is_delete) {
        $is_option = $_POST['is_option'][$k];

        if($is_option != 2) {
            $opt_count++;
        } else {
            $spl_count++;
        }
    }
}

// 추가옵션이 있을 때 선택옵션이 없으면 에러
if($spl_count > 0 && $opt_count == 0) {
    alert('추가옵션이 있을 경우 선택옵션을 모두 삭제할 수 없습니다.');
}

$ct_parent = $_POST['ct_parent'];
$ct_parent_check = true;

for($i=0; $i<$count; $i++) {
    $ct_id = $_POST['ct_id'][$i];

    if($ct_id) {
        $is_delete = $_POST['is_delete'][$i];

        if($is_delete) { // 옵션삭제
            $sql = " delete from {$g4['shop_cart_table']} where uq_id = '$s_uq_id' and ct_id = '$ct_id' ";
            sql_query($sql);
        } else { // 옵션수정
            $ct_qty = $_POST['ct_qty'][$i];
            $sql = " update {$g4['shop_cart_table']} set ct_qty = '$ct_qty' where uq_id = '$s_uq_id' and ct_id = '$ct_id' ";
            sql_query($sql);
        }
    } else { // 옵션추가
        $it_id = $_POST['it_id'];
        $it_amount = $_POST['it_amount'][$i];
        $opt_id = $_POST['opt_id'][$i];
        $is_option = $_POST['is_option'][$i];
        $ct_option = $_POST['ct_option'][$i];
        $ct_amount = $_POST['ct_amount'][$i];
        $ct_qty = $_POST['ct_qty'][$i];
        $ct_point = 0;
        $ct_send_cost_pay = $_POST['ct_send_cost_pay'];
        if($is_option == 1 || $is_option == 0) {
            $amount = $it_amount;
        } else {
            $amount = 0;
        }

        $sql = " insert into {$g4['shop_cart_table']}
                    set uq_id               = '$s_uq_id',
                        ct_parent           = '$ct_parent',
                        mb_id               = '{$member['mb_id']}',
                        is_option           = '$is_option',
                        it_id               = '$it_id',
                        it_name             = '$it_name',
                        opt_id              = '$opt_id',
                        ct_option           = '$ct_option',
                        ct_status           = '쇼핑',
                        it_amount           = '$it_amount',
                        ct_amount           = '$ct_amount',
                        ct_qty              = '$ct_qty',
                        ct_point            = '$ct_point',
                        ct_stock_use        = '0',
                        ct_point_use        = '0',
                        ct_send_cost_pay    = '$ct_send_cost_pay',
                        ct_time             = '{$g4['time_ymdhis']}',
                        ct_ip               = '$REMOTE_ADDR',
                        ct_direct           = '$sw_direct' ";

        sql_query($sql);

        // ct_parent 처리
        if($ct_parent_check) {
            $temp_ct_id = mysql_insert_id();

            $sql1 = " select count(*) as cnt from {$g4['shop_cart_table']} where uq_id = '$s_uq_id' and it_id = '$it_id' and ct_direct = '$sw_direct' ";
            $row1 = sql_fetch($sql1);

            if($row1['cnt'] == 1) {
                sql_query(" update {$g4['shop_cart_table']} set ct_parent = '0' where ct_id = '$temp_ct_id' ");
                $ct_parent = $temp_ct_id;
            }

            $ct_parent_check = false;
        }
    }
}

echo '<script>';
echo 'window.opener.location.reload();';
echo 'self.close();';
echo '</script>';
?>