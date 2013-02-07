<?php
$sub_menu = "400800";
include_once("./_common.php");

check_demo();

auth_check($auth[$sub_menu], "w");

// 쿠폰번호 생성함수
function coupon_generator()
{
    $len = 16;
    $chars = "ABCDEFGHJKLMNPQRSTUVWXYZ123456789";

    srand((double)microtime()*1000000);

    $i = 0;
    $str = '';

    while ($i < $len) {
        $num = rand() % strlen($chars);
        $tmp = substr($chars, $num, 1);
        $str .= $tmp;
        $i++;
    }

    $str = preg_replace("/([0-9A-Z]{4})([0-9A-Z]{4})([0-9A-Z]{4})([0-9A-Z]{4})/", "\\1-\\2-\\3-\\4", $str);

    return $str;
}

if($w != 'd') {
    $cp_subject = get_text(trim($_POST['cp_subject']));
    $it_id = trim($_POST['it_id']);
    $ca_id = trim($_POST['ca_id']);
    $mb_id = trim($_POST['mb_id']);

    $it_id = preg_replace("/^,+/", "", $it_id);
    $it_id = preg_replace("/,+$/", "", $it_id);
    $ca_id = preg_replace("/^,+/", "", $ca_id);
    $ca_id = preg_replace("/,+$/", "", $ca_id);
    $mb_id = preg_replace("/^,+/", "", $mb_id);
    $mb_id = preg_replace("/,+$/", "", $mb_id);
    $cp_amount = (int)preg_replace("/[^0-9]/", "", $_POST['cp_amount']);
    $cp_maximum = (int)preg_replace("/[^0-9]/", "", $_POST['cp_maximum']);
    $cp_minimum = (int)preg_replace("/[^0-9]/", "", $_POST['cp_minimum']);

    if(!$cp_subject) {
        alert('쿠폰명을 입력해 주세요.');
    }

    if($cp_type != 0){ // 상품할인이 아니면 사용대상은 주문서
        $cp_target = 3;
    }

    if($cp_type == 2) { // 배송비할인은 정액할인만
        $cp_method = 0;
    }

    if($cp_type == 0) { // 상품할인
        if(strlen($cp_target) != 1) {
            alert('사용대상을 선택해 주세요.');
        }

        if(strlen($cp_method) != 1) {
            alert('할인방식을 선택해 주세요.');
        }
    } else if($cp_type == 1) { // 결제금액할인
        if(strlen($cp_method) != 1) {
            alert('할인방식을 선택해 주세요.');
        }
    }

    if(!$cp_amount) {
        alert('할인금액을 원 또는 % 단위로 입력해 주세요.');
    } else {
        if($cp_method == 1) { // 비율할인형
            if($cp_amount < 1 || $cp_amount > 99) {
                alert('할인비율을 1과 99 사이의 값으로 입력해 주세요.');
            }
        } else {
            if($cp_amount < 1) {
                alert('할인금액을 1원이상 입력해 주세요.');
            }
        }
    }

    if(!$cp_minimum) {
        $cp_minimum = 0;
    }

    if(!$cp_minimum) {
        $cp_minimum = 0;
    }

    if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $cp_start)) {
        alert('사용기한은 '.date("Y-m-d", time()).'형식으로 입력해 주세요.');
    }

    if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $cp_end)) {
        alert('사용기한은 '.date("Y-m-d", time()).'형식으로 입력해 주세요.');
    }

    if($cp_start > $cp_end) {
        alert('사용시작일은 종료일 이후 일 수 없습니다.');
    }

    if($cp_end < $g4['time_ymd']) {
        alert('사용종료일은 오늘('.$g4['time_ymd'].') 이전일 수 없습니다.');
    }

    if($cp_type == 0) { // 상품할인 일때
        if($cp_target == 0) {
            if(!$it_id) {
                alert('적용상품을 선택해 주세요.');
            }
            $ca_id = '';
        } else if($cp_target == 1) {
            if(!$ca_id) {
                alert('적용카테고리를 입력해 주세요.');
            }
            $it_id = '';
        } else if($cp_target == 2) { // 전체상품이므로 $it_id 필요없음
            $it_id = '';
        }
    }

    if(!$mb_id) {
        alert('적용회원을 선택해 주세요');
    }

    // ca_id에 전체카테고리와 함께 다른 정보가 있을 경우 전체카테고리로 처리
    if(strstr($ca_id, '전체카테고리')) {
        $ca_id = '전체카테고리';
    }

    // mb_id에 전체회원과 함께 다른 정보가 있을 경우 전체회원으로 처리
    if(strstr($mb_id, '전체회원')) {
        $mb_id = '전체회원';
    }
}

if($w == '') {
    if($cp_type) { // 결제금액할인 or 배송비할인
        $arr_mb_id = explode(',', $mb_id);
        $mb_id_count = count($arr_mb_id);

        for($i=0; $i<$mb_id_count; $i++) {
            // 회원체크
            if($mb_id != '전체회원') {
                $sql = " select mb_id from {$g4['member_table']}
                            where mb_leave_date = '' and mb_intercept_date = '' and mb_id = '{$arr_mb_id[$i]}' ";
                $mb = sql_fetch($sql);
                if(!$mb['mb_id']) {
                    continue;
                }
            }

            $j = 0;
            do {
                $cp_id = coupon_generator();

                $sql = " insert into {$g4['shop_coupon_table']}
                            set cp_id       = '$cp_id',
                                cp_subject  = '$cp_subject',
                                cp_type     = '$cp_type',
                                cp_target   = '$cp_target',
                                cp_method   = '$cp_method',
                                it_id       = '$it_id',
                                ca_id       = '$ca_id',
                                mb_id       = '{$arr_mb_id[$i]}',
                                cp_start    = '$cp_start',
                                cp_end      = '$cp_end',
                                cp_amount   = '$cp_amount',
                                cp_trunc    = '$cp_trunc',
                                cp_minimum  = '$cp_minimum',
                                cp_maximum  = '$cp_maximum',
                                cp_use      = '$cp_use',
                                cp_datetime = '{$g4['time_ymdhis']}' ";
                $result = sql_query($sql, false);

                if($result) {
                    break; // 에러가 없다면 빠진다.
                } else {
                    if($j++ > 10) {
                        die('coupon id error.');
                    }
                }
            } while (1);
        }
    } else { // 상품할인
        if($cp_target == 2) { // 전체상품
            $arr_mb_id = explode(',', $mb_id);
            $mb_id_count = count($arr_mb_id);

            for($i=0; $i<$mb_id_count; $i++) {
                // 회원체크
                if($mb_id != '전체회원') {
                    $sql = " select mb_id from {$g4['member_table']}
                                where mb_leave_date = '' and mb_intercept_date = '' and mb_id = '{$arr_mb_id[$i]}' ";
                    $mb = sql_fetch($sql);
                    if(!$mb['mb_id']) {
                        continue;
                    }
                }

                $j = 0;
                do {
                    $cp_id = coupon_generator();

                    $sql = " insert into {$g4['shop_coupon_table']}
                                set cp_id       = '$cp_id',
                                    cp_subject  = '$cp_subject',
                                    cp_type     = '$cp_type',
                                    cp_target   = '$cp_target',
                                    cp_method   = '$cp_method',
                                    it_id       = '$it_id',
                                    ca_id       = '$ca_id',
                                    mb_id       = '{$arr_mb_id[$i]}',
                                    cp_start    = '$cp_start',
                                    cp_end      = '$cp_end',
                                    cp_amount   = '$cp_amount',
                                    cp_trunc    = '$cp_trunc',
                                    cp_minimum  = '$cp_minimum',
                                    cp_maximum  = '$cp_maximum',
                                    cp_use      = '$cp_use',
                                    cp_datetime = '{$g4['time_ymdhis']}' ";
                    $result = sql_query($sql, false);

                    if($result) {
                        break; // 에러가 없다면 빠진다.
                    } else {
                        if($j++ > 10) {
                            die('coupon id error.');
                        }
                    }
                } while (1);
            }
        } else if($cp_target == 1) { // 카테고리
            $arr_ca_id = explode(',', $ca_id);
            $arr_mb_id = explode(',', $mb_id);
            $ca_id_count = count($arr_ca_id);
            $mb_id_count = count($arr_mb_id);

            for($i=0; $i<$ca_id_count; $i++) {
                // 카테고리체크
                if($ca_id != '전체카테고리') {
                    $sql = " select ca_id from {$g4['shop_category_table']}
                                where ca_id = '{$arr_ca_id[$i]}' and ca_use = '1' and ca_nocoupon = '0' ";
                    $ca = sql_fetch($sql);
                    if(!$ca['ca_id']) {
                        continue;
                    }
                }

                for($k=0; $k<$mb_id_count; $k++) {
                    // 회원체크
                    if($mb_id != '전체회원') {
                        $sql = " select mb_id from {$g4['member_table']}
                                    where mb_leave_date = '' and mb_intercept_date = '' and mb_id = '{$arr_mb_id[$k]}' ";
                        $mb = sql_fetch($sql);
                        if(!$mb['mb_id']) {
                            continue;
                        }
                    }

                    $j = 0;
                    do {
                        $cp_id = coupon_generator();

                        $sql = " insert into {$g4['shop_coupon_table']}
                                    set cp_id       = '$cp_id',
                                        cp_subject  = '$cp_subject',
                                        cp_type     = '$cp_type',
                                        cp_target   = '$cp_target',
                                        cp_method   = '$cp_method',
                                        it_id       = '$it_id',
                                        ca_id       = '{$arr_ca_id[$i]}',
                                        mb_id       = '{$arr_mb_id[$k]}',
                                        cp_start    = '$cp_start',
                                        cp_end      = '$cp_end',
                                        cp_amount   = '$cp_amount',
                                        cp_trunc    = '$cp_trunc',
                                        cp_minimum  = '$cp_minimum',
                                        cp_maximum  = '$cp_maximum',
                                        cp_use      = '$cp_use',
                                        cp_datetime = '{$g4['time_ymdhis']}' ";
                        $result = sql_query($sql, false);

                        if($result) {
                            break; // 에러가 없다면 빠진다.
                        } else {
                            if($j++ > 10) {
                                die('coupon id error.');
                            }
                        }
                    } while (1);
                }
            }
        } else { // 상품
            $arr_it_id = explode(',', $it_id);
            $arr_mb_id = explode(',', $mb_id);
            $it_id_count = count($arr_it_id);
            $mb_id_count = count($arr_mb_id);

            for($i=0; $i<$it_id_count; $i++) {
                // 상품체크
                if($it_id != '') {
                    $sql = " select it_id from {$g4['shop_item_table']}
                                where it_id = '{$arr_it_id[$i]}' and it_use = '1' and it_nocoupon = '0' ";
                    $it = sql_fetch($sql);
                    if(!$it['it_id']) {
                        continue;
                    }
                }

                for($k=0; $k<$mb_id_count; $k++) {
                    // 회원체크
                    if($mb_id != '전체회원') {
                        $sql = " select mb_id from {$g4['member_table']}
                                    where mb_leave_date = '' and mb_intercept_date = '' and mb_id = '{$arr_mb_id[$k]}' ";
                        $mb = sql_fetch($sql);
                        if(!$mb['mb_id']) {
                            continue;
                        }
                    }

                    $j = 0;
                    do {
                        $cp_id = coupon_generator();

                        $sql = " insert into {$g4['shop_coupon_table']}
                                    set cp_id       = '$cp_id',
                                        cp_subject  = '$cp_subject',
                                        cp_type     = '$cp_type',
                                        cp_target   = '$cp_target',
                                        cp_method   = '$cp_method',
                                        it_id       = '{$arr_it_id[$i]}',
                                        ca_id       = '$ca_id',
                                        mb_id       = '{$arr_mb_id[$k]}',
                                        cp_start    = '$cp_start',
                                        cp_end      = '$cp_end',
                                        cp_amount   = '$cp_amount',
                                        cp_trunc    = '$cp_trunc',
                                        cp_minimum  = '$cp_minimum',
                                        cp_maximum  = '$cp_maximum',
                                        cp_use      = '$cp_use',
                                        cp_datetime = '{$g4['time_ymdhis']}' ";
                        $result = sql_query($sql, false);

                        if($result) {
                            break; // 에러가 없다면 빠진다.
                        } else {
                            if($j++ > 10) {
                                die('coupon id error.');
                            }
                        }
                    } while (1);
                }
            }
        }
    }
} else if($w == 'u') {
    $sql = " select cp_id from {$g4['shop_coupon_table']} where cp_no = '$cp_no' ";
    $row = sql_fetch($sql);

    if(!$row['cp_id']) {
        alert('쿠폰 정보가 존재하지 않습니다.');
    }

    $arr_it_id = explode(',', $it_id);
    $arr_ca_id = explode(',', $ca_id);
    $arr_mb_id = explode(',', $mb_id);

    if(count($arr_it_id) > 1) {
        alert('수정시에는 1개의 상품만 입력할 수 있습니다.');
    }

    if(count($arr_ca_id) > 1) {
        alert('수정시에는 1개의 카테고리만 입력할 수 있습니다.');
    }

    if(count($arr_mb_id) > 1) {
        alert('수정시에는 1명의 회원만 입력할 수 있습니다.');
    }

    // 상품체크
    if($cp_type == 0 && $cp_target == 0) {
        if($it_id != '전체상품') {
            $sql = " select it_id from {$g4['shop_item_table']} where it_id = '$it_id' and it_nocoupon = '0' ";
            $row = sql_fetch($sql);
            if(!$row['it_id']) {
                alert('존재하지 않거나 쿠폰제외 상품입니다.');
            }
        }
    }

    // 카테고리체크
    if($cp_type == 0 && $cp_target == 1) {
        if($ca_id != '전체카테고리') {
            $sql = " select ca_id from {$g4['shop_category_table']} where ca_id = '$ca_id' and ca_nocoupon = '0' ";
            $row = sql_fetch($sql);
            if(!$row['ca_id']) {
                alert('존재하지 않거나 쿠폰제외 카테고리입니다.');
            }
        }
    }

    // 회원체크
    if($mb_id != '전체회원') {
        $sql = " select mb_id from {$g4['member_table']}
                    where mb_leave_date = '' and mb_intercept_date = '' and mb_id = '$mb_id' ";
        $row = sql_fetch($sql);
        if(!$row['mb_id']) {
            alert('회원정보가 없거나 탈퇴 또는 차단된 회원입니다.');
        }
    }

    $sql = " update {$g4['shop_coupon_table']}
                set cp_subject  = '$cp_subject',
                    cp_type     = '$cp_type',
                    cp_target   = '$cp_target',
                    cp_method   = '$cp_method',
                    it_id       = '$it_id',
                    ca_id       = '$ca_id',
                    mb_id       = '$mb_id',
                    cp_start    = '$cp_start',
                    cp_end      = '$cp_end',
                    cp_amount   = '$cp_amount',
                    cp_trunc    = '$cp_trunc',
                    cp_minimum  = '$cp_minimum',
                    cp_maximum  = '$cp_maximum',
                    cp_use      = '$cp_use',
                    cp_datetime = '{$g4['time_ymdhis']}'
                where cp_no = '$cp_no' ";
    sql_query($sql);
} else if($w == 'd') {
    $sql = " select cp_id from {$g4['shop_coupon_table']} where cp_no = '$cp_no' ";
    $row = sql_fetch($sql);

    if(!$row['cp_id']) {
        alert('쿠폰 정보가 존재하지 않습니다.');
    }

    $sql = " delete from {$g4['shop_coupon_table']} where cp_no = '$cp_no' ";
    sql_query($sql);
}

if($w == 'u') {
    goto_url("./couponform.php?w=u&cp_no=$cp_no&$qstr");
} else {
    goto_url("./couponlist.php?$qstr");
}
?>