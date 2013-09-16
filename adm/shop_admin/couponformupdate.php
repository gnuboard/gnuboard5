<?php
$sub_menu = '400650';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$_POST = array_map('trim', $_POST);

if(!$_POST['cp_subject'])
    alert('쿠폰이름을 입력해 주십시오.');

if($_POST['cp_method'] == 0 && !$_POST['cp_target'])
    alert('적용상품을 입력해 주십시오.');

if($_POST['cp_method'] == 1 && !$_POST['cp_target'])
    alert('적용분류를 입력해 주십시오.');

if(!$_POST['mb_id'] && !$_POST['chk_all_mb'])
    alert('회원아이디를 입력해 주십시오.');

if(!$_POST['cp_start'] || !$_POST['cp_end'])
    alert('사용 시작일과 종료일을 입력해 주십시오.');

if($_POST['cp_start'] > $_POST['cp_end'])
    alert('사용 시작일은 종료일 이전으로 입력해 주십시오.');

if($_POST['cp_end'] < G5_TIME_YMD)
    alert('종료일은 오늘('.G5_TIME_YMD.')이후로 입력해 주십시오.');

if(!$_POST['cp_price']) {
    if($_POST['cp_type'])
        alert('할인비율을 입력해 주십시오.');
    else
        alert('할인금액을 입력해 주십시오.');
}

if($_POST['cp_type'] && ($_POST['cp_price'] < 1 || $_POST['cp_price'] > 99))
    alert('할인비율을은 1과 99사이 값으로 입력해 주십시오.');

if($_POST['cp_method'] == 0) {
    $sql = " select count(*) as cnt from {$g5['g5_shop_item_table']} where it_id = '$cp_target' ";
    $row = sql_fetch($sql);
    if(!$row['cnt'])
        alert('입력하신 상품코드는 존재하지 않는 상품코드입니다.');
} else if($_POST['cp_method'] == 1) {
    $sql = " select count(*) as cnt from {$g5['g5_shop_category_table']} where ca_id = '$cp_target' ";
    $row = sql_fetch($sql);
    if(!$row['cnt'])
        alert('입력하신 분류코드는 존재하지 않는 분류코드입니다.');
}

if($w == '') {
    if($_POST['chk_all_mb']) {
        $mb_id = '전체회원';
    } else {
        $sql = " select mb_id from {$g5['member_table']} where mb_id = '{$_POST['mb_id']}' and mb_leave_date = '' and mb_intercept_date = '' ";
        $row = sql_fetch($sql);
        if(!$row['mb_id'])
            alert('입력하신 회원아이디는 존재하지 않거나 탈퇴 또는 차단된 회원아이디입니다.');

        $mb_id = $_POST['mb_id'];
    }

    $j = 0;
    do {
        $cp_id = get_coupon_id();

        $sql3 = " select count(*) as cnt from {$g5['g5_shop_coupon_table']} where cp_id = '$cp_id' ";
        $row3 = sql_fetch($sql3);

        if(!$row3['cnt'])
            break;
        else {
            if($j > 20)
                die('Coupon ID Error');
        }
    } while(1);

    $sql = " INSERT INTO {$g5['g5_shop_coupon_table']}
                ( cp_id, cp_subject, cp_method, cp_target, mb_id, cp_start, cp_end, cp_type, cp_price, cp_trunc, cp_minimum, cp_maximum, cp_datetime )
            VALUES
                ( '$cp_id', '$cp_subject', '$cp_method', '$cp_target', '$mb_id', '$cp_start', '$cp_end', '$cp_type', '$cp_price', '$cp_trunc', '$cp_minimum', '$cp_maximum', '".G5_TIME_YMDHIS."' ) ";

    sql_query($sql);
} else if($w == 'u') {
    $sql = " select * from {$g5['g5_shop_coupon_table']} where cp_id = '$cp_id' ";
    $cp = sql_fetch($sql);

    if(!$cp['cp_id'])
        alert('쿠폰정보가 존해하지 않습니다.', './couponlist.php');

    if($_POST['chk_all_mb']) {
        $mb_id = '전체회원';
    }

    $sql = " update {$g5['g5_shop_coupon_table']}
                set cp_subject  = '$cp_subject',
                    cp_method   = '$cp_method',
                    cp_target   = '$cp_target',
                    mb_id       = '$mb_id',
                    cp_start    = '$cp_start',
                    cp_end      = '$cp_end',
                    cp_type     = '$cp_type',
                    cp_price    = '$cp_price',
                    cp_trunc    = '$cp_trunc',
                    cp_maximum  = '$cp_maximum',
                    cp_minimum  = '$cp_minimum'
                where cp_id = '$cp_id' ";
    sql_query($sql);
}

goto_url('./couponlist.php');
?>