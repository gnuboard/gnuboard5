<?php
include_once('./_common.php');

// 상품정보
$sql = " select it_id, it_name, it_soldout, it_stock_sms
            from {$g5['g5_shop_item_table']}
            where it_id = '$it_id' ";
$it = sql_fetch($sql);

if(!$it['it_id'])
    alert_close('상품정보가 존재하지 않습니다.');

if(!$it['it_soldout'] || !$it['it_stock_sms'])
    alert_close('재입고SMS 알림을 신청할 수 없는 상품입니다.');

$ss_hp = hyphen_hp_number($ss_hp);
if(!$ss_hp)
    alert('휴대폰번호를 입력해 주십시오.');

if(!$agree)
    alert('개인정보처리방침안내에 동의해 주십시오.');

// 중복등록 체크
$sql = " select count(*) as cnt
            from {$g5['g5_shop_item_stocksms_table']}
            where it_id = '$it_id'
              and ss_hp = '$ss_hp'
              and ss_send = '0' ";
$row = sql_fetch($sql);

if($row['cnt'])
    alert_close('해당 상품에 대하여 이전에 알림 요청을 등록한 내역이 있습니다.');

// 정보입력
$sql = " insert into {$g5['g5_shop_item_stocksms_table']}
            set it_id       = '$it_id',
                ss_hp       = '$ss_hp',
                ss_ip       = '{$_SERVER['REMOTE_ADDR']}',
                ss_datetime = '".G5_TIME_YMDHIS."' ";
sql_query($sql);

alert_close('재입고SMS 알림 요청 등록이 완료됐습니다.');
?>