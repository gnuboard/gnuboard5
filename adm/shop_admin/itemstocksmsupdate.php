<?php
$sub_menu = '500400';
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

if ($_POST['act_button'] == "선택SMS전송") {

    include_once(G5_LIB_PATH.'/icode.sms.lib.php');

    if($config['cf_sms_use'] == 'icode')
    {
        $SMS = new SMS;
        $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);
    }

    auth_check($auth[$sub_menu], 'w');

    $cnt = 0;

    for ($i=0; $i<count($_POST['chk']); $i++) {

        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        $sql = " select a.ss_id, a.ss_hp, a.ss_send, b.it_id, b.it_name
                    from {$g5['g5_shop_item_stocksms_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
                    where a.ss_id = '{$_POST['ss_id'][$k]}' ";
        $row = sql_fetch($sql);

        if(!$row['ss_id'] || !$row['it_id'] || $row['ss_send'])
            continue;

        // SMS
        if($config['cf_sms_use'] == 'icode') {
            $sms_contents = iconv_euckr(get_text($row['it_name']).' 상품이 재입고 되었습니다. '.$default['de_admin_company_name']);
            $receive_number = preg_replace("/[^0-9]/", "", $row['ss_hp']);	// 수신자번호
            $send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호

            if($receive_number && $send_number) {
                $SMS->Add($receive_number, $send_number, $config['cf_icode_id'], $sms_contents, "");
                $cnt++;
            }
        }

        // SMS 전송으로 변경함
        $sql = " update {$g5['g5_shop_item_stocksms_table']}
                    set ss_send = '1',
                        ss_send_time = '".G5_TIME_YMDHIS."'
                    where ss_id = '{$_POST['ss_id'][$k]}' ";
        sql_query($sql);
    }

    // SMS
    if($config['cf_sms_use'] == 'icode' && $cnt)
    {
        $SMS->Send();
    }
} else if ($_POST['act_button'] == "선택삭제") {

    if ($is_admin != 'super')
        alert('자료의 삭제는 최고관리자만 가능합니다.');

    auth_check($auth[$sub_menu], 'd');

    check_token();

    for ($i=0; $i<count($_POST['chk']); $i++) {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        $sql = " delete from {$g5['g5_shop_item_stocksms_table']} where ss_id = '{$_POST['ss_id'][$k]}' ";
        sql_query($sql);
    }
}


$qstr1 = 'sel_field='.$sel_field.'&amp;search='.$search;
$qstr = $qstr1.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;page='.$page;

goto_url('./itemstocksms.php?'.$qstr);
?>
