<?php
$sub_menu = '400660';
include_once('./_common.php');

check_demo();

if ($w == 'd')
    auth_check($auth[$sub_menu], "d");
else
    auth_check($auth[$sub_menu], "w");

if ($w == "u")
{
    $sql = "update {$g5['g5_shop_item_qa_table']}
               set iq_subject = '$iq_subject',
                   iq_question = '$iq_question',
                   iq_answer = '$iq_answer'
             where iq_id = '$iq_id' ";
    sql_query($sql);

    if(trim($iq_answer)) {
        $sql = " select a.iq_email, a.iq_hp, b.it_name
                    from {$g5['g5_shop_item_qa_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
                    where a.iq_id = '$iq_id' ";
        $row = sql_fetch($sql);

        // SMS 알림
        if($config['cf_sms_use'] == 'icode' && $row['iq_hp']) {
            include_once(G5_LIB_PATH.'/icode.sms.lib.php');

            $sms_content = get_text($row['it_name']).' 상품문의에 답변이 등록되었습니다.';
            $send_number = preg_replace('/[^0-9]/', '', $default['de_admin_company_tel']);
            $recv_number = preg_replace('/[^0-9]/', '', $row['iq_hp']);

            if($recv_number) {
                $SMS = new SMS; // SMS 연결
                $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);
                $SMS->Add($recv_number, $send_number, $config['cf_icode_id'], iconv("utf-8", "euc-kr", stripslashes($sms_content)), "");
                $SMS->Send();
            }
        }

        // 답변 이메일전송
        if(trim($row['iq_email'])) {
            include_once(G5_LIB_PATH.'/mailer.lib.php');

            $subject = $config['cf_title'].' '.$row['it_name'].' 상품문의 답변 알림 메일';
            $content = conv_content($iq_answer, 1);

            mailer($config['cf_title'], $config['cf_admin_email'], $row['iq_email'], $subject, $content, 1);
        }
    }

    goto_url("./itemqaform.php?w=$w&amp;iq_id=$iq_id&amp;sca=$sca&amp;$qstr");
}
else {
    alert();
}
?>
