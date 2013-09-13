<?php
$sub_menu = '400440';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$_POST = array_map('trim', $_POST);

if(!$_POST['pp_name'])
    alert('이름을 입력해 주십시오.');
if(!$_POST['pp_price'])
    alert('주문금액을 입력해 주십시오.');
if(preg_match('/[^0-9]/', $_POST['pp_price']))
    alert('주문금액은 숫자만 입력해 주십시오.');

if($_POST['od_id']) {
    $sql = " select od_id from {$g5['g5_shop_order_table']} where od_id = '{$_POST['od_id']}' ";
    $od = sql_fetch($sql);
    if(!$od['od_id'])
        alert('입력하신 주문번호는 존재하지 않습니다.');
}

$sql = " select * from {$g5['g5_shop_personalpay_table']} where pp_id = '$pp_id' ";
$row = sql_fetch($sql);

if(!$row['pp_id'])
    alert_close('복사하시려는 개인결제 정보가 존재하지 않습니다.');

$new_pp_id = get_uniqid();

$sql = " insert into {$g5['g5_shop_personalpay_table']}
            set pp_id           = '$new_pp_id',
                od_id           = '{$_POST['od_id']}',
                pp_name         = '{$_POST['pp_name']}',
                pp_content      = '{$row['pp_content']}',
                pp_use          = '{$row['pp_use']}',
                pp_price        = '{$_POST['pp_price']}',
                pp_ip           = '{$_SERVER['REMOTE_ADDR']}',
                pp_time         = '".G5_TIME_YMDHIS."' ";
sql_query($sql);

$g5['title'] = '개인결제 복사';
include_once(G5_PATH.'/head.sub.php');
?>

<script>
alert("개인결제 정보가 복사되었습니다.");
window.opener.location.reload();
self.close();
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>