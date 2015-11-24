<?php
$sub_menu = '400800';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

check_admin_token();

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
    $sql = " select count(*) as cnt from {$g5['g5_shop_item_table']} where it_id = '$cp_target' and it_nocoupon = '0' ";
    $row = sql_fetch($sql);
    if(!$row['cnt'])
        alert('입력하신 상품코드는 존재하지 않는 코드이거나 쿠폰적용안함으로 설정된 상품입니다.');
} else if($_POST['cp_method'] == 1) {
    $sql = " select count(*) as cnt from {$g5['g5_shop_category_table']} where ca_id = '$cp_target' and ca_nocoupon = '0' ";
    $row = sql_fetch($sql);
    if(!$row['cnt'])
        alert('입력하신 분류코드는 존재하지 않는 분류코드이거나 쿠폰적용안함으로 설정된 분류입니다.');
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

// 쿠폰생성알림 발송
if($w == '' && ($_POST['cp_sms_send'] || $_POST['cp_email_send'])) {
    include_once(G5_LIB_PATH.'/mailer.lib.php');

    $sms_count = 0;
    $arr_send_list = array();
    $sms_messages = array();

    if($_POST['chk_all_mb']) {
        $sql = " select mb_id, mb_name, mb_hp, mb_email, mb_mailling, mb_sms
                    from {$g5['member_table']}
                    where mb_leave_date = ''
                      and mb_intercept_date = ''
                      and ( mb_mailling = '1' or mb_sms = '1' )
                      and mb_id <> '{$config['cf_admin']}' ";
    } else {
        $sql = " select mb_id, mb_name, mb_hp, mb_email, mb_mailling, mb_sms
                    from {$g5['member_table']}
                    where mb_id = '$mb_id' ";
    }

    $result = sql_query($sql);

    for($i=0; $row = sql_fetch_array($result); $i++) {
        $arr_send_list[] = $row;
    }

    $count = count($arr_send_list);

    for($i=0; $i<$count; $i++) {
        if(!$arr_send_list[$i]['mb_id'])
            continue;

        // SMS
        if($config['cf_sms_use'] == 'icode' && $_POST['cp_sms_send'] && $arr_send_list[$i]['mb_hp'] && $arr_send_list[$i]['mb_sms']) {
            $sms_contents = $cp_subject.' 쿠폰이 '.get_text($arr_send_list[$i]['mb_name']).'님께 발행됐습니다. 쿠폰만료 : '.$cp_end.' '.str_replace('http://', '', G5_URL);

            if($sms_contents) {
                $receive_number = preg_replace("/[^0-9]/", "", $arr_send_list[$i]['mb_hp']);   // 수신자번호
                $send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호

                if($receive_number)
                    $sms_messages[] = array('recv' => $receive_number, 'send' => $send_number, 'cont' => $sms_contents);
            }
        }

        // E-MAIL
        if($config['cf_email_use'] && $_POST['cp_email_send'] && $arr_send_list[$i]['mb_email'] && $arr_send_list[$i]['mb_mailling']) {
            $mb_name = get_text($arr_send_list[$i]['mb_name']);
            switch($cp_method) {
                case 2:
                    $coupon_method = '결제금액할인';
                    break;
                case 3:
                    $coupon_method = '배송비할인';
                    break;
                default:
                    $coupon_method = '개별상품할인';
                    break;
            }
            $contents = '쿠폰명 : '.$cp_subject.'<br>';
            $contents .= '적용대상 : '.$coupon_method.'<br>';
            $contents .= '쿠폰만료 : '.$cp_end;

            $title = $config['cf_title'].' - 쿠폰발행알림 메일';
            $email = $arr_send_list[$i]['mb_email'];

            ob_start();
            include G5_SHOP_PATH.'/mail/couponmail.mail.php';
            $content = ob_get_contents();
            ob_end_clean();

            mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $email, $title, $content, 1);
        }
    }

    // SMS발송
    $sms_count = count($sms_messages);
    if($sms_count > 0) {
        if($config['cf_sms_type'] == 'LMS') {
            include_once(G5_LIB_PATH.'/icode.lms.lib.php');

            $port_setting = get_icode_port_type($config['cf_icode_id'], $config['cf_icode_pw']);

            // SMS 모듈 클래스 생성
            if($port_setting !== false) {
                $SMS = new LMS;
                $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $port_setting);

                for($s=0; $s<$sms_count; $s++) {
                    $strDest     = array();
                    $strDest[]   = $sms_messages[$s]['recv'];
                    $strCallBack = $sms_messages[$s]['send'];
                    $strCaller   = iconv_euckr(trim($default['de_admin_company_name']));
                    $strSubject  = '';
                    $strURL      = '';
                    $strData     = iconv_euckr($sms_messages[$s]['cont']);
                    $strDate     = '';
                    $nCount      = count($strDest);

                    $res = $SMS->Add($strDest, $strCallBack, $strCaller, $strSubject, $strURL, $strData, $strDate, $nCount);

                    $SMS->Send();
                    $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
                }
            }
        } else {
            include_once(G5_LIB_PATH.'/icode.sms.lib.php');

            $SMS = new SMS; // SMS 연결
            $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);

            for($s=0; $s<$sms_count; $s++) {
                $recv_number = $sms_messages[$s]['recv'];
                $send_number = $sms_messages[$s]['send'];
                $sms_content = iconv_euckr($sms_messages[$s]['cont']);

                $SMS->Add($recv_number, $send_number, $config['cf_icode_id'], $sms_content, "");
            }

            $SMS->Send();
            $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
        }
    }
}

goto_url('./couponlist.php');
?>