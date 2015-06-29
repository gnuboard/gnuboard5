<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

if ($w == '')
{
    $po_id   = $_POST['po_id'];
    $pc_name = $_POST['pc_name'];
    $pc_idea = $_POST['pc_idea'];

    $po = sql_fetch(" select * from {$g5['poll_table']} where po_id = '{$po_id}' ");
    if (!$po['po_id'])
        alert('po_id 값이 제대로 넘어오지 않았습니다.');

    $tmp_row = sql_fetch(" select max(pc_id) as max_pc_id from {$g5['poll_etc_table']} ");
    $pc_id = $tmp_row['max_pc_id'] + 1;

    $sql = " insert into {$g5['poll_etc_table']}
                ( pc_id, po_id, mb_id, pc_name, pc_idea, pc_datetime )
                values ( '{$pc_id}', '{$po_id}', '{$member['mb_id']}', '{$pc_name}', '{$pc_idea}', '".G5_TIME_YMDHIS."' ) ";
    sql_query($sql);

    $pc_idea = stripslashes($pc_idea);

    $name = get_text(cut_str($pc_name, $config['cf_cut_name']));
    $mb_id = '';
    if ($member['mb_id'])
        $mb_id = '('.$member['mb_id'].')';

    // 환경설정의 투표 기타의견 작성시 최고관리자에게 메일발송 사용에 체크되어 있을 경우
    if ($config['cf_email_po_super_admin'])
    {
        $subject = $po['po_subject'];
        $content = $pc_idea;

        ob_start();
        include_once ('./poll_etc_update_mail.php');
        $content = ob_get_contents();
        ob_end_clean();

        // 관리자에게 보내는 메일
        $admin = get_admin('super');
        $from_email = $member['mb_email'] ? $member['mb_email'] : $admin['mb_email'];
        mailer($name, $from_email, $admin['mb_email'], '['.$config['cf_title'].'] 설문조사 기타의견 메일', $content, 1);
    }
}
else if ($w == 'd')
{
    if ($member['mb_id'] || $is_admin == 'super')
    {
        $sql = " delete from {$g5['poll_etc_table']} where pc_id = '{$pc_id}' ";
        if (!$is_admin)
            $sql .= " and mb_id = '{$member['mb_id']}' ";
        sql_query($sql);
    }
}

goto_url('./poll_result.php?po_id='.$po_id.'&amp;skin_dir='.$skin_dir);
?>
