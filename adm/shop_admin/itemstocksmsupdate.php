<?php
$sub_menu = '500400';
include_once('./_common.php');

check_demo();

check_admin_token();

$count_post_chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;

if (! $count_post_chk) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

if ($_POST['act_button'] == "선택SMS전송") {

    auth_check_menu($auth, $sub_menu, 'w');

    $sms_messages = array();

    for ($i=0; $i<$count_post_chk; $i++) {

        // 실제 번호를 넘김
        $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;
        $ss_id = isset($_POST['ss_id'][$k]) ? (int) $_POST['ss_id'][$k] : 0;

        $sql = " select a.ss_id, a.ss_hp, a.ss_send, b.it_id, b.it_name
                    from {$g5['g5_shop_item_stocksms_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
                    where a.ss_id = '$ss_id' ";
        $row = sql_fetch($sql);

        if(!$row['ss_id'] || !$row['it_id'] || $row['ss_send'])
            continue;

        // SMS
        if($config['cf_sms_use'] == 'icode') {
            $sms_contents = get_text($row['it_name']).' 상품이 재입고 되었습니다. '.$default['de_admin_company_name'];
            $receive_number = preg_replace("/[^0-9]/", "", $row['ss_hp']);	// 수신자번호
            $send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호

            if($receive_number)
                $sms_messages[] = array('recv' => $receive_number, 'send' => $send_number, 'cont' => $sms_contents);
        }

        // SMS 전송으로 변경함
        $sql = " update {$g5['g5_shop_item_stocksms_table']}
                    set ss_send = '1',
                        ss_send_time = '".G5_TIME_YMDHIS."',
                        ss_channel = '1'
                    where ss_id = '{$ss_id}' ";
        sql_query($sql);
    }

    // SMS
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
} else if ($_POST['act_button'] == "선택알림톡전송") {
    // 알림톡 발송 BEGIN: 재입고알림(CU-ST01) -------------------------------------
    auth_check_menu($auth, $sub_menu, 'w');
    include_once(G5_KAKAO5_PATH.'/kakao5.lib.php');
    
    if (!$config['cf_kakaotalk_use']) {
        alert('카카오톡 사용 설정이 되어 있지 않아 발송할 수 없습니다.\n[환경설정>기본환경설정>기본알림환경]에서 사용 설정을 해주세요.');
    } else {
        // 프리셋 정보 가져오기
        $alimtalk = get_alimtalk_preset_info('CU-ST01');
    
        if (empty($alimtalk['success'])) {
            alert('재입고 알림톡 설정이 되어 있지 않아 발송할 수 없습니다.\n[환경설정>알림톡프리셋 관리]에서 설정해주세요.');
        } else {
            for ($i=0; $i<$count_post_chk; $i++) {

                // 실제 번호를 넘김
                $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;
                $ss_id = isset($_POST['ss_id'][$k]) ? (int) $_POST['ss_id'][$k] : 0;

                $sql = " select a.ss_id, a.ss_hp, a.ss_send, b.it_id, b.it_name
                            from {$g5['g5_shop_item_stocksms_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
                            where a.ss_id = '$ss_id' ";
                $row = sql_fetch($sql);

                if(!$row['ss_id'] || !$row['it_id'] || $row['ss_send'])
                    continue;

                $conditions = ['it_id' => $row['it_id'], 'it_name' => get_text($row['it_name'])]; // 변수 치환 정보

                $cu_atk = send_alimtalk_preset('CU-ST01', ['rcv' => $row['ss_hp']], $conditions); // 회원

                // 성공한 건만 완료 처리
                if (!empty($cu_atk) && !empty($cu_atk['success']))
                {
                    sql_query(" update {$g5['g5_shop_item_stocksms_table']}
                                set ss_send = '1',
                                    ss_send_time = '".G5_TIME_YMDHIS."',
                                    ss_channel = '2'
                                where ss_id = '{$ss_id}' ");
                }
            }
        }
    }
    // 알림톡 발송 END -------------------------------------------------------------

} else if ($_POST['act_button'] == "선택삭제") {

    if ($is_admin != 'super')
        alert('자료의 삭제는 최고관리자만 가능합니다.');

    auth_check_menu($auth, $sub_menu, 'd');

    for ($i=0; $i<$count_post_chk; $i++) {
        // 실제 번호를 넘김
        $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;
        $ss_id = isset($_POST['ss_id'][$k]) ? (int) $_POST['ss_id'][$k] : 0;

        $sql = " delete from {$g5['g5_shop_item_stocksms_table']} where ss_id = '{$ss_id}' ";
        sql_query($sql);
    }
}


$qstr1 = 'sel_field='.$sel_field.'&amp;search='.$search;
$qstr = $qstr1.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;page='.$page;

goto_url('./itemstocksms.php?'.$qstr);