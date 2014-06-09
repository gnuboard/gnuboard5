<?php
$sub_menu = "900800";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

$g5['title'] = "휴대폰번호 업데이트";

$g5['sms5_demo'] = 0;

$is_hp_exist = false;

$bk_hp = get_hp($bk_hp);

if ($w=='u') // 업데이트
{
    if (!$bg_no) $bg_no = 0;

    if (!$bk_receipt) $bk_receipt = 0; else $bk_receipt = 1;

    if (!strlen(trim($bk_name)))
        alert('이름을 입력해주세요');

    if ($bk_hp == '')
        alert('휴대폰번호만 입력 가능합니다.');
/*
    $res = sql_fetch("select * from {$g5['sms5_book_table']} where bk_no<>'$bk_no' and bk_hp='$bk_hp'");
    if ($res)
        alert('같은 번호가 존재합니다.');
*/
    $res = sql_fetch("select * from {$g5['sms5_book_table']} where bk_no='$bk_no'");
    if (!$res)
        alert('존재하지 않는 데이터 입니다.');

    if ($bg_no != $res['bg_no']) {
        if ($res['mb_id']) $mem = "bg_member"; else $mem = "bg_nomember";
        if ($res['bk_receipt'] == 1) $sms = "bg_receipt"; else $sms = "bg_reject";
        sql_query("update {$g5['sms5_book_group_table']} set bg_count = bg_count - 1, $mem = $mem - 1, $sms = $sms - 1 where bg_no='{$res['bg_no']}'");
        sql_query("update {$g5['sms5_book_group_table']} set bg_count = bg_count + 1, $mem = $mem + 1, $sms = $sms + 1 where bg_no='$bg_no'");
    }

    if ($bk_receipt != $res['bk_receipt']) {
        if ($bk_receipt == 1)
            sql_query("update {$g5['sms5_book_group_table']} set bg_receipt = bg_receipt + 1, bg_reject = bg_reject - 1 where bg_no='$bg_no'");
        else
            sql_query("update {$g5['sms5_book_group_table']} set bg_receipt = bg_receipt - 1, bg_reject = bg_reject + 1 where bg_no='$bg_no'");
    }

    sql_query("update {$g5['sms5_book_table']} set bg_no='$bg_no', bk_name='$bk_name', bk_hp='$bk_hp', bk_receipt='$bk_receipt', bk_datetime='".G5_TIME_YMDHIS."', bk_memo='".addslashes($bk_memo)."' where bk_no='$bk_no'");
    if ($res['mb_id']){ //만약에 mb_id가 있다면...
        // 휴대폰번호 중복체크
        $sql = " select mb_id from {$g5['member_table']} where mb_id <> '{$res['mb_id']}' and mb_hp = '{$bk_hp}' ";
        $mb_hp_exist = sql_fetch($sql);
        if ($mb_hp_exist['mb_id']) { //중복된 회원 휴대폰번호가 있다면
            $is_hp_exist = true;
        } else {
             sql_query("update {$g5['member_table']} set mb_name='".addslashes($bk_name)."', mb_hp='$bk_hp', mb_sms='$bk_receipt' where mb_id='{$res['mb_id']}'", false);
        }
    }
    $get_bg_no = $bg_no;

    $go_url = './num_book_write.php?bk_no='.$bk_no.'&amp;w='.$w.'&amp;page='.$page;
    if( $is_hp_exist ){ //중복된 회원 휴대폰번호가 있다면
        //alert( "중복된 회원 휴대폰번호가 있어서 회원정보에는 반영되지 않았습니다.", $go_url );
        goto_url($go_url);
    } else {
        goto_url($go_url);
    }
    exit;
}
else if ($w=='d') // 삭제
{
    if (!is_numeric($bk_no))
        alert('고유번호가 없습니다.');

    $res = sql_fetch("select * from {$g5['sms5_book_table']} where bk_no='$bk_no'");
    if (!$res)
        alert('존재하지 않는 데이터 입니다.');

    if ($res['bk_receipt'] == 1) $bg_sms = 'bg_receipt'; else $bg_sms = 'bg_reject';
    if ($res['mb_id']) $bg_mb = 'bg_member'; else $bg_mb = 'bg_nomember';

    sql_query("delete from {$g5['sms5_book_table']} where bk_no='$bk_no'");
    sql_query("update {$g5['sms5_book_group_table']} set bg_count = bg_count - 1, $bg_mb = $bg_mb - 1, $bg_sms = $bg_sms - 1 where bg_no = '{$res['bg_no']}'");

/*
    if (!is_numeric($bk_no))
        alert('고유번호가 없습니다.');

    $res = sql_fetch("select * from $g5[sms5_book_table] where bk_no='$bk_no'");
    if (!$res)
        alert('존재하지 않는 데이터 입니다.');

    if (!$res[mb_id])
    {
        if ($res[receipt] == 1)
            $sql_sms = "bg_receipt = bg_receipt - 1";
        else
            $sql_sms = "bg_reject = bg_reject - 1";

        sql_query("delete from $g5[sms5_book_table] where bk_no='$bk_no'");
        sql_query("update $g5[sms5_book_group_table] set bg_count = bg_count - 1, bg_nomember = bg_nomember - 1, $sql_sms where bg_no = '$res[bg_no]'");
    }
    else
        alert("회원은 삭제할 수 없습니다.\\n\\n회원관리 메뉴에서 삭제한 후\\n\\n회원정보업데이트 메뉴를 실행 해주세요.");
*/
}
else // 등록
{
    if (!$bg_no) $bg_no = 1;

    if (!$bk_receipt) $bk_receipt = 0; else $bk_receipt = 1;

    if (!strlen(trim($bk_name)))
        alert('이름을 입력해주세요');

    if ($bk_hp == '')
        alert('휴대폰번호만 입력 가능합니다.');

    $res = sql_fetch("select * from {$g5['sms5_book_table']} where bk_hp='$bk_hp'");
    if ($res)
        alert('같은 번호가 존재합니다.');

    if ($bk_receipt == 1)
        $sql_sms = "bg_receipt = bg_receipt + 1";
    else
        $sql_sms = "bg_reject = bg_reject + 1";

    sql_query("insert into {$g5['sms5_book_table']} set bg_no='$bg_no', bk_name='".addslashes($bk_name)."', bk_hp='$bk_hp', bk_receipt='$bk_receipt', bk_datetime='".G5_TIME_YMDHIS."', bk_memo='".addslashes($bk_memo)."'");
    sql_query("update {$g5['sms5_book_group_table']} set bg_count = bg_count + 1, bg_nomember = bg_nomember + 1, $sql_sms where bg_no = '$bg_no'");

    $get_bg_no = $bg_no;
}

$go_url = './num_book.php?page='.$page.'&amp;bg_no='.$get_bg_no.'&amp;ap='.$ap;
goto_url($go_url);
?>