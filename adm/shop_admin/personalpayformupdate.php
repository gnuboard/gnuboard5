<?php
$sub_menu = '400440';
include_once('./_common.php');

check_admin_token();

if($w == 'd') {
    auth_check($auth[$sub_menu], 'd');

    $sql = " select pp_id from {$g5['g5_shop_personalpay_table']} where pp_id = '{$_GET['pp_id']}' ";
    $row = sql_fetch($sql);
    if(!$row['pp_id'])
        alert('삭제하시려는 자료가 존재하지 않습니다.');

    sql_query(" delete from {$g5['g5_shop_personalpay_table']} where pp_id = '{$_GET['pp_id']}' ");

    goto_url('./personalpaylist.php?'.$qstr);
} else {
    auth_check($auth[$sub_menu], 'w');

    $_POST = array_map('trim', $_POST);

    if(!$_POST['pp_name'])
        alert('이름을 입력해 주십시오.');
    if(!$_POST['pp_price'])
        alert('주문금액을 입력해 주십시오.');
    if(preg_match('/[^0-9]/', $_POST['pp_price']))
        alert('주문금액은 숫자만 입력해 주십시오.');

    $od_id = preg_replace('/[^0-9]/', '', $_POST['od_id']);

    if($_POST['od_id']) {
        $sql = " select od_id from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
        $row = sql_fetch($sql);
        if(!$row['od_id'])
            alert('입력하신 주문번호는 존재하지 않는 주문 자료입니다.');
    }

    $sql_common = " pp_name             = '{$_POST['pp_name']}',
                    pp_price            = '{$_POST['pp_price']}',
                    od_id               = '$od_id',
                    pp_content          = '{$_POST['pp_content']}',
                    pp_receipt_price    = '{$_POST['pp_receipt_price']}',
                    pp_settle_case      = '{$_POST['pp_settle_case']}',
                    pp_receipt_time     = '{$_POST['pp_receipt_time']}',
                    pp_shop_memo        = '{$_POST['pp_shop_memo']}',
                    pp_use              = '{$_POST['pp_use']}' ";
}

if($w == '') {
    $pp_id = get_uniqid();
    $sql = " insert into {$g5['g5_shop_personalpay_table']}
                set pp_id = '$pp_id',
                    $sql_common ,
                    pp_ip   = '{$_SERVER['REMOTE_ADDR']}',
                    pp_time = '".G5_TIME_YMDHIS."' ";
    sql_query($sql);
} else if($w == 'u') {
    $sql = " select pp_id from {$g5['g5_shop_personalpay_table']} where pp_id = '{$_POST['pp_id']}' ";
    $row = sql_fetch($sql);
    if(!$row['pp_id'])
        alert('수정하시려는 자료가 존재하지 않습니다.');

    $sql = " update {$g5['g5_shop_personalpay_table']}
                set $sql_common
                where pp_id = '{$_POST['pp_id']}' ";
    sql_query($sql);
}

if($popup == 'yes')
    alert_close('개인결제가 추가됐습니다.');
else
    goto_url('./personalpayform.php?w=u&amp;pp_id='.$pp_id.'&amp;'.$qstr);
?>